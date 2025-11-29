<?php
session_start();

// Enable error reporting for development (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
        die("Database Connection failed: " . $conn->connect_error);
    }

    // Retrieve and sanitize form data
    $first_name = trim(htmlspecialchars($_POST['first_name'] ?? ''));
    $last_name = trim(htmlspecialchars($_POST['last_name'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $raw_password = $_POST['password'] ?? ''; // Get raw password for hashing
    $phone = trim(htmlspecialchars($_POST['phone'] ?? ''));
    $school_college_company_name = trim(htmlspecialchars($_POST['school_college_company_name'] ?? ''));
    $address = trim(htmlspecialchars($_POST['address'] ?? ''));
    $pincode = trim(htmlspecialchars($_POST['pincode'] ?? ''));
    $study = trim(htmlspecialchars($_POST['study'] ?? ''));
    $dob = trim(htmlspecialchars($_POST['dob'] ?? ''));
    
    // --- Server-side validation ---
    $errors = [];

    if (empty($first_name)) { $errors[] = "First name is required."; }
    if (empty($last_name)) { $errors[] = "Last name is required."; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    if (empty($raw_password) || strlen($raw_password) < 6) { // Example: password must be at least 6 characters
        $errors[] = "Password is required and must be at least 6 characters long.";
    }
    if (empty($phone)) { $errors[] = "Phone number is required."; }
    if (empty($school_college_company_name)) { $errors[] = "School/College/Company name is required."; }
    if (empty($address)) { $errors[] = "Address is required."; }
    if (empty($pincode)) { $errors[] = "Pincode is required."; }
    if (empty($study)) { $errors[] = "Study level is required."; }
    if (empty($dob)) { $errors[] = "Date of birth is required."; }


    if (empty($errors)) {
        // Hash the password securely
        $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

        // Prepare an INSERT statement for the 'users' table
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone, school_college_company_name, address, pincode, study, dob) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("ssssssssss", $first_name, $last_name, $email, $hashed_password, $phone, $school_college_company_name, $address, $pincode, $study, $dob);

        try {
            if ($stmt->execute()) {
                // Registration successful
                // Redirect to login page instead of logging in automatically
                header("Location: login.php"); 
                exit;
            } else {
                // This else block might not be reached if mysqli_report is strict
                $errors[] = "Error during registration: " . $stmt->error;
            }
        } catch (mysqli_sql_exception $e) {
            // Catch specific SQL exceptions, like duplicate entry
            if ($e->getCode() == 1062) { // MySQL error code for duplicate entry for unique key
                $errors[] = "This email is already registered. Please use a different email.";
            } else {
                $errors[] = "Database error during registration: " . $e->getMessage();
            }
        }

        // Close the statement
        $stmt->close();
    }

    // If there are errors, store them in session and redirect back to registration form
    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect back to this page
        exit;
    }

    // Close the database connection
    $conn->close();

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: url('images/signup.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            font-weight: 600; /* Applied font-weight to body for general text */
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: #000; /* Changed to black */
            position: relative; /* Added for absolute positioning of the back arrow */
        }

        .signup-container h2 { 
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
            font-weight: 800; /* Made title even bolder */
            color: #000; /* Changed to black */
            text-shadow: none; /* Removed text shadow for black text */
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
            color: #000; /* Black color for the arrow */
            margin-bottom: 5px;
            text-decoration: none;
            transition: color 0.2s ease;
            line-height: 1; /* Ensures vertical alignment */
            font-weight: 800; /* Make arrow bold */
        }

        .back-arrow-icon:hover {
            color: #4CAF50; /* Green color on hover */
        }


        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 700; /* Made labels bolder */
            font-size: 0.95em;
            color: #333; /* Changed to dark grey for labels */
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid rgba(0, 0, 0, 0.2); /* Darker border for inputs */
            border-radius: 8px;
            font-size: 1em;
            background-color: rgba(0, 0, 0, 0.05); /* Very light transparent black for inputs */
            color: #333; /* Changed to dark grey for input text */
            transition: all 0.3s ease;
            font-weight: 600; /* Made input text bolder */
        }

        input::placeholder, textarea::placeholder {
            color: rgba(0, 0, 0, 0.5); /* Darker placeholder text */
            font-weight: 600; /* Made placeholder text bolder */
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #4CAF50; /* Green accent on focus */
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.3); /* Green glow effect */
            background-color: rgba(0, 0, 0, 0.1); /* Slightly darker transparent on focus */
        }

        /* Specific styles for select dropdown and its options */
        select {
            background-color: #000; /* Black background for dropdown */
            color: #fff; /* White text for dropdown */
            border: 1px solid #333; /* Darker border for dropdown */
            font-weight: 600; /* Made select text bolder */
        }

        /* For the dropdown options when the list opens */
        select option {
            background-color: #000; /* Black background for options */
            color: #fff; /* White text for options */
            font-weight: 600; /* Made option text bolder */
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #2ecc71, #27ae60);
            color: white;
            border: none;
            font-size: 1.1em;
            font-weight: 700; /* Made button text bolder */
            cursor: pointer;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.4);
            transition: all 0.3s ease;
        }

        button:hover {
            background: linear-gradient(to right, #27ae60, #229a5b);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.6);
        }

        .error,
        .session-errors {
            color: #fff; /* Keep white text for errors on red background */
            background-color: rgba(255, 0, 0, 0.6); /* Slightly more opaque red background */
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
            border: 1px solid rgba(255, 0, 0, 0.8);
            font-weight: 600; /* Made error text bolder */
        }
    </style>
</head>
<body>
<div class="signup-container">
    <div class="back-arrow-box"> <!-- New wrapper for the arrow -->
        <a href="login.php" class="back-arrow-icon" title="Back to Login">&#8592;</a>
    </div>
    <h2>Create Your Account</h2>
    <?php
    // Display session errors if any
    if (isset($_SESSION['registration_errors']) && !empty($_SESSION['registration_errors'])) {
        echo "<div class='session-errors'>";
        foreach ($_SESSION['registration_errors'] as $err) {
            echo "<p>$err</p>";
        }
        unset($_SESSION['registration_errors']); // Clear errors after displaying
        echo "</div>";
    }
    ?>
    <form method="POST" action="">
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>

        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Create a password" required>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>

        <label for="school_college_company_name">School / College / Company Name</label>
        <input type="text" id="school_college_company_name" name="school_college_company_name" placeholder="Your institution/employer" required>

        <label for="address">Address</label>
        <textarea id="address" name="address" rows="3" placeholder="Your full address" required></textarea>

        <label for="pincode">Pincode</label>
        <input type="text" id="pincode" name="pincode" placeholder="Your area pincode" required>

        <label for="study">Select Study Level</label>
        <select id="study" name="study" required>
            <option value="">Select Study</option>
            <option value="SSC">SSC</option>
            <option value="HSC">HSC</option>
            <option value="Diploma">Diploma</option>
            <option value="Bachelor's">Undergraduate</option>
            <option value="Master's">Postgraduate</option>
            <option value="Engineering">Engineering</option>
        </select>
        
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" required>
        
        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>
