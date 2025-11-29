<?php
session_start();

// Enable error reporting for development (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Determine the heading based on the referrer
$login_heading = "Welcome Back!"; // Default heading

if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    if (strpos($referer, 'index.php') !== false) {
        $login_heading = "Start Your Journey!";
    } elseif (strpos($referer, 'register.php') !== false) {
        // This condition will be true when redirected from register.php
        $login_heading = "Welcome Back!";
    }
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Database connection details
    $servername = "localhost";
    $username = "root";
    $password = ""; // <--- IMPORTANT: SET YOUR DATABASE ROOT PASSWORD HERE
    $dbname = "course_recommender_db";

    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        // If connection fails, display a clear error message
        die("Database Connection failed: " . $conn->connect_error);
    }

    // Retrieve and sanitize form data
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $entered_password = $_POST['password'] ?? ''; // Get raw password for verification

    $error = "";

    if (empty($email) || empty($entered_password)) {
        $error = "Both email and password are required.";
    } else {
        // Prepare a SELECT statement to fetch user by email
        // Fetch the hashed password from the database
        $stmt = $conn->prepare("SELECT user_id, email, password FROM users WHERE email = ?");
        if ($stmt === false) {
            die("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify the hashed password
            if (password_verify($entered_password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                header("Location: courses.php"); // Redirect to courses page
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* General styling */
        :root {
            --primary-blue: #3498db;
            --dark-blue: #2980b9;
            --light-blue-accent: #87CEEB;
            --container-bg-alpha: rgba(255, 255, 255, 0.15); /* Slightly more transparent */
            --border-alpha: rgba(255, 255, 255, 0.3);
            --input-bg-alpha: rgba(255, 255, 255, 0.1); /* Even more transparent */
            --input-focus-bg-alpha: rgba(255, 255, 255, 0.2);
            --text-color: #fff;
            --shadow-color: rgba(0,0,0,0.4);
            --error-bg-alpha: rgba(255, 0, 0, 0.2);
            --error-border-alpha: rgba(255, 0, 0, 0.4);
        }

        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: url('images/lock.jpg') no-repeat center center fixed; /* Ensure this path is correct and image exists */
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            overflow: hidden; /* Prevent scrollbar from subtle animations */
            font-weight: 600; /* Applied font-weight to body for general text */
        }

        /* Login Container */
        .login-container {
            background: var(--container-bg-alpha);
            backdrop-filter: blur(15px); /* Increased blur for stronger effect */
            -webkit-backdrop-filter: blur(15px);
            padding: 45px; /* Increased padding */
            border-radius: 20px; /* More rounded corners */
            box-shadow: 0 10px 40px var(--shadow-color); /* Stronger, softer shadow */
            width: 100%;
            max-width: 420px; /* Slightly wider */
            border: 1px solid var(--border-alpha); /* Subtle border */
            color: var(--text-color);
            position: relative; /* Added for absolute positioning of the back arrow box */
            z-index: 1;
            animation: fadeInScale 0.8s ease-out forwards; /* Entry animation */
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 35px; /* More space below title */
            font-size: 2.4em; /* Larger title */
            font-weight: 800; /* Extra bold */
            color: var(--text-color);
            text-shadow: 0 3px 6px rgba(0,0,0,0.4); /* More pronounced text shadow */
            position: relative;
            padding-bottom: 10px;
        }

        .login-container h2::after { /* Stylish underline for title */
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 70px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-blue), var(--light-blue-accent));
            border-radius: 2px;
        }

        /* Styling for the back arrow box */
        .back-arrow-box {
            position: absolute;
            top: 15px; /* Position the box */
            left: 20px; /* Position the box */
            background: rgba(255, 255, 255, 0.3); /* Semi-transparent background for the box */
            backdrop-filter: blur(5px); /* Subtle blur for the box */
            -webkit-backdrop-filter: blur(5px);
            border-radius: 10px; /* Rounded corners for the box */
            padding: 8px 12px; /* Padding inside the box */
            box-shadow: 0 2px 8px rgba(0,0,0,0.2); /* Shadow for the box */
            display: flex; /* Use flexbox to center the arrow */
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .back-arrow-box:hover {
            transform: translateY(-2px); /* Lift effect on hover */
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); /* Stronger shadow on hover */
        }

        /* Styling for the arrow icon itself within the box */
        .back-arrow-icon {
            font-size: 28px; /* Larger arrow */
            color: #fff; /* White color for the arrow to contrast with the box */
            text-decoration: none;
            transition: color 0.2s ease;
            line-height: 1; /* Ensures vertical alignment */
            font-weight: 800; /* Make arrow bold */
        }

        .back-arrow-icon:hover {
            color: var(--light-blue-accent); /* Light blue color on hover */
        }

        /* Form elements */
        label {
            display: block;
            margin-bottom: 8px; /* More space below label */
            font-weight: 600; /* Bolder labels */
            font-size: 1em;
            color: rgba(255, 255, 255, 0.9); /* Slightly less opaque */
        }

        input {
            width: 100%;
            padding: 14px 18px; /* Increased padding */
            margin-bottom: 20px; /* More space between inputs */
            border: 1px solid var(--border-alpha);
            border-radius: 10px; /* More rounded inputs */
            font-size: 1.05em;
            background-color: var(--input-bg-alpha);
            color: var(--text-color);
            transition: all 0.3s ease;
            font-weight: 600; /* Made input text bolder */
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.6); /* Lighter placeholder text */
            font-weight: 600; /* Made placeholder text bolder */
        }

        input:focus {
            outline: none;
            border-color: var(--light-blue-accent);
            box-shadow: 0 0 0 4px rgba(135, 206, 235, 0.4); /* Stronger glow effect */
            background-color: var(--input-focus-bg-alpha);
        }

        /* Button styling */
        button {
            width: 100%;
            padding: 16px; /* Larger button */
            background: linear-gradient(to right, var(--primary-blue), var(--dark-blue)); /* Blue gradient */
            color: white;
            border: none;
            font-size: 1.2em; /* Larger font */
            font-weight: 700; /* Bolder font */
            cursor: pointer;
            border-radius: 10px; /* More rounded button */
            margin-top: 25px; /* More space above button */
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.5); /* Initial shadow */
            transition: all 0.3s ease;
            letter-spacing: 0.5px; /* Subtle letter spacing */
        }

        button:hover {
            background: linear-gradient(to right, var(--dark-blue), #1a5276); /* Darker blue on hover */
            transform: translateY(-3px); /* More pronounced lift effect */
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.7); /* Stronger shadow on hover */
        }

        /* Error message styling */
        .error {
            color: #ffdddd;
            background-color: var(--error-bg-alpha);
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
            border: 1px solid var(--error-border-alpha);
            font-weight: 600; /* Made error text bolder */
        }

        /* Sign up link */
        .signup-link {
            display: block;
            text-align: center;
            margin-top: 30px; /* More space */
            color: var(--text-color);
            font-size: 1em;
            font-weight: 600; /* Made signup link text bolder */
        }

        .signup-link a {
            color: var(--light-blue-accent); /* Light blue for link */
            text-decoration: none;
            font-weight: 700; /* Bolder link */
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            text-decoration: underline;
            color: #a0e0ff; /* Even lighter blue on hover */
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="back-arrow-box"> <!-- New wrapper for the arrow -->
        <a href="index.php" class="back-arrow-icon" title="Back to Home">&#8592;</a>
    </div>
    <h2><?php echo $login_heading; ?></h2>
    <form method="POST" action="">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Your email address" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Your password" required>
        
        <button type="submit">Login</button>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    </form>
    <div class="signup-link">
        Don't have an account? <a href="register.php">Sign Up Now</a>
    </div>
</div>
</body>
</html>
