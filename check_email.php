<?php
header('Content-Type: application/json');

require_once 'includes/config.php';

$email = trim($_POST['email'] ?? $_GET['email'] ?? '');

if ($email === '') {
    echo json_encode([
        'status' => 'empty',
        'message' => 'Email is required'
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'invalid',
        'message' => 'Please enter a valid email address'
    ]);
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            'status' => 'exists',
            'message' => 'This email is already registered.',
            'email' => $email
        ]);
    } else {
        echo json_encode([
            'status' => 'available',
            'message' => 'Email is available.',
            'email' => $email
        ]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database query failed'
    ]);
}
