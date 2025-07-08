<?php
session_start();

// Include database configuration
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }
    
    // Validate required fields
    if (empty($data['classification']) || empty($data['image_data'])) {
        throw new Exception('Missing required fields');
    }
    
    // Process image data
    $imageData = $data['image_data'];
    $imageFileName = null;
    
    // Extract base64 image data and save to file
    if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
        $imageExtension = $matches[1];
        $imageBase64 = substr($imageData, strpos($imageData, ',') + 1);
        $imageDecoded = base64_decode($imageBase64);
        
        // Create uploads directory if it doesn't exist
        $uploadsDir = '../uploads/diagnosis_images/';
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        // Generate unique filename
        $imageFileName = 'diagnosis_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $imageExtension;
        $imageFilePath = $uploadsDir . $imageFileName;
        
        // Save image file
        if (file_put_contents($imageFilePath, $imageDecoded) === false) {
            throw new Exception('Failed to save image file');
        }
    } else {
        throw new Exception('Invalid image data format');
    }
    
    // Prepare SQL statement
    $sql = "INSERT INTO diagnosis_results (
        user_id,
        patient_name, 
        notes, 
        image_filename, 
        classification, 
        confidence_score, 
        explanation, 
        health_risk, 
        analysis_date, 
        created_at
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
    )";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    // Bind parameters
    $patient_name = $data['patient_name'] ?? 'Anonymous';
    $notes = $data['notes'] ?? '';
    $classification = $data['classification'];
    $confidence_score = $data['confidence_score'] ?? '0%';
    $explanation = $data['explanation'] ?? '';
    $health_risk = $data['health_risk'] ?? '';
    
    // Fix datetime format conversion
    if (isset($data['analysis_date']) && !empty($data['analysis_date'])) {
        // Convert ISO 8601 format to MySQL datetime format
        $analysis_date = date('Y-m-d H:i:s', strtotime($data['analysis_date']));
    } else {
        // Use current datetime in MySQL format
        $analysis_date = date('Y-m-d H:i:s');
    }
    
    // Fixed: 9 parameters for 9 placeholders (including analysis_date)
    $stmt->bind_param("issssssss", 
        $user_id,
        $patient_name, 
        $notes, 
        $imageFileName, 
        $classification, 
        $confidence_score, 
        $explanation, 
        $health_risk,
        $analysis_date
    );
    
    // Execute statement
    if ($stmt->execute()) {
        $insertId = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Diagnosis results saved successfully',
            'id' => $insertId
        ]);
    } else {
        throw new Exception('Failed to insert data: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log('Database Error: ' . $e->getMessage());
    
    // Clean up image file if it was created but database insert failed
    if (isset($imageFilePath) && file_exists($imageFilePath)) {
        unlink($imageFilePath);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save results: ' . $e->getMessage()
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>