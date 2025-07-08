<?php 
    session_start();
    include_once '../config.php';
    if(isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Prepare and execute the SQL statement
        $stmt = $conn->prepare("SELECT * FROM tbl_account WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['acc_id'];
                $_SESSION['user_email'] = $user['email'];
                //return json
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'user_id' => $user['acc_id'],
                    'user_email' => $user['email']
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid password. Please try again.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email not found. Please register first.'
            ]);
        }

        $stmt->close();

    } else {
        echo "Please provide both email and password";
    }
?>