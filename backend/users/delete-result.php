<?php
    require_once '../config.php';

    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if(!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }

    $resultId = intval($data['id']);

    $query = "DELETE FROM diagnosis_results WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $resultId);
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete result: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
?>