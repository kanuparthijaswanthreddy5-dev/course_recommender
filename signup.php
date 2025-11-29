<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "course_recommender_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $study = $conn->real_escape_string($_POST['study']);
    $dob = $conn->real_escape_string($_POST['dob']);

    $sql = "INSERT INTO login (first_name, last_name, email, password, study, dob) 
            VALUES ('$first_name', '$last_name', '$email', '$password', '$study', '$dob')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: url('images/signup.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.2); /* More transparent */
            backdrop-filter: blur(2px); /* This adds the blur effect */
            -webkit-backdrop-filter: blur(10px); /* Safari support */
            padding: 40px 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.3); /* Optional: soft border */
        }


        .signup-container h2 { 
            text-align: center;
            margin-bottom: 20px;
        }

        input, select {
            width: 100%;
            padding: 10px 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 15px;
        }

        button:hover {
            background: #218838;
        }

        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="signup-container">
    <h2>Sign Up</h2>
    <form method="POST" action="">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="study" required>
            <option value="">Select Study</option>
            <option value="SSC">SSC</option>
            <option value="HSC">HSC</option>
            <option value="Diploma">Diploma</option>
            <option value="Bachelor's">Undergraduate</option>
            <option value="Master's">Postgrduate</option>
            <option value="Engineering">Engineering</option>
        </select>
        <input type="date" name="dob" required>
        <button type="submit">Register</button>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    </form>
</div>
</body>
</html>
