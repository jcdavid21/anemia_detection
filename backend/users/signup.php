<?php
session_start();
include_once '../config.php';
if (isset($_POST['full_name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_pass'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_pass'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Passwords do not match.'
        ]);
        exit();
    }

    $check_email = $conn->prepare("SELECT * FROM tbl_account WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();
    if ($result->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email already exists. Please use a different email.'
        ]);
        exit();
    }
    $check_email->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO tbl_account (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashed_password);

    if ($stmt->execute()) {
        // Get the last inserted ID
        $generated_id = $stmt->insert_id;

        $stmt_2 = $conn->prepare("INSERT INTO tbl_account_details (acc_id, full_name) VALUES (?, ?)");
        $stmt_2->bind_param("is", $generated_id, $full_name);
        if ($stmt_2->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Registration successful. You can now log in.',
                'user_id' => $generated_id,
                'user_email' => $email
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create user profile. Please try again.'
            ]);
        }
        $stmt_2->close();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Registration failed. Please try again.'
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill in all fields.'
    ]);
}
$conn->close();

?>