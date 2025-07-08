<?php

    require_once '../config.php';

    if(isset($_GET["id"]))
    {
        $resultId = intval($_GET["id"]);

        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM diagnosis_results WHERE id = ?");
        $stmt->bind_param("i", $resultId);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                echo json_encode(["result" => $data, "success" => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Result not found']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $stmt->error]);
        }
        
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
    }
?>