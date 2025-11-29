<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the message from the session if it exists, otherwise provide a default
$message = $_SESSION['message'] ?? 'Your enrollment status has been processed.';
unset($_SESSION['message']); // Clear the message after displaying it

// Determine if it's a success or error message for styling
$is_success = (strpos($message, 'successful') !== false || strpos($message, 'status has been processed') !== false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Status</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #2c3e50;
            color: #ecf0f1;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            background: rgba(33, 47, 60, 0.9);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5);
            max-width: 500px;
            width: 90%;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .success-heading {
            color: #2ecc71; /* Green for success */
        }
        .error-heading {
            color: #e74c3c; /* Red for error */
        }
        p {
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        a.button {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(to right,rgb(65, 161, 225),rgb(108, 185, 41));
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        a.button:hover {
            background: linear-gradient(to right, #2980b9, #2573a6);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="<?= $is_success ? 'success-heading' : 'error-heading' ?>">
        <?= $is_success ? 'Enrollment Status' : 'Enrollment Error' ?>
    </h1>
    <p><?= htmlspecialchars($message) ?></p>
    <a href="my_courses.php" class="button">Go to My Courses</a>
    <a href="courses.php" class="button" style="margin-left: 15px; background: linear-gradient(to right, #6c757d, #5a6268);">Browse More Courses</a>
</div>
</body>
</html>