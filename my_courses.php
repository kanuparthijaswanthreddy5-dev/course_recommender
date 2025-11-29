<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Enable error reporting (useful for development, disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
$servername = "localhost";
$username = "root";
$password = ""; // <--- IMPORTANT: SET YOUR DATABASE PASSWORD HERE
$dbname = "course_recommender_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch enrolled courses for the current user
$stmt = $conn->prepare("
    SELECT c.course_id, c.name, c.description, e.enrollment_date, e.status
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.user_id = ?
    ORDER BY e.enrollment_date DESC
");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Enrolled Courses</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #2c3e50; /* Dark background */
            background-image: url('images/coursebackground.jpg'); /* Your existing background */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #ecf0f1;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background: rgba(33, 47, 60, 0.9); /* Slightly darker, more opaque background */
            backdrop-filter: blur(12px); /* Slightly stronger blur */
            -webkit-backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5); /* More prominent shadow */
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            font-size: 2.8rem; /* Slightly larger title */
            margin-bottom: 30px; /* More space below title */
            color: #3498db; /* Blue accent for title */
            font-weight: 700;
            position: relative;
            padding-bottom: 15px; /* More space below underline */
        }
        h1::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 100px; /* Longer underline */
            height: 5px; /* Thicker underline */
            background: linear-gradient(to right, #3498db, #2ecc71); /* Blue to green gradient */
            border-radius: 3px;
        }
        .message {
            background-color: rgba(231, 76, 60, 0.2); /* Soft red for error messages */
            color: #e74c3c;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e74c3c;
        }
        .course-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px; /* Increased gap for better separation */
            margin-top: 30px;
        }
        .course-card {
            background: rgba(255, 255, 255, 0.05); /* Lighter, more subtle background */
            padding: 20px; /* Increased padding */
            border-radius: 12px; /* Slightly more rounded corners */
            border: 1px solid rgba(255, 255, 255, 0.08); /* More subtle border */
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25); /* Stronger shadow */
            transition: transform 0.2s ease, background 0.2s ease; /* Added background transition */
            text-align: left;
            display: flex; /* Flexbox for internal layout */
            flex-direction: column;
            justify-content: space-between; /* Pushes button to bottom */
        }
        .course-card:hover {
            transform: translateY(-5px); /* More pronounced lift on hover */
            background: rgba(255, 255, 255, 0.1); /* Slightly brighter on hover */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4); /* Stronger shadow on hover */
        }
        .course-card h3 {
            color: #2ecc71; /* Green accent for course titles */
            margin-top: 0;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .course-card p {
            font-size: 0.95rem;
            line-height: 1.5;
            color: #bdc3c7; /* Light grey for description */
            flex-grow: 1; /* Allows description to take up available space */
        }
        .course-card .enroll-info {
            font-size: 0.85rem;
            color: #7f8c8d; /* Darker grey for meta info */
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.05); /* Subtle separator */
        }
        .course-card .enroll-info strong {
            color: #ecf0f1;
        }
        .course-card .enroll-info .status {
            font-weight: 600;
            color: #3498db; /* Blue for status */
        }
        .course-card .enroll-info .status.completed {
            color: #2ecc71; /* Green for completed */
        }
        .course-card .enroll-info .status.dropped {
            color: #e74c3c; /* Red for dropped */
        }
        .view-course-button {
            display: inline-block;
            margin-top: 20px; /* Space above button */
            padding: 10px 20px;
            background: linear-gradient(to right, #3498db, #2980b9); /* Blue gradient */
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        .view-course-button:hover {
            background: linear-gradient(to right, #2980b9, #2072a7);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        .back-button {
            display: inline-block;
            margin-top: 40px;
            padding: 14px 35px;
            background: linear-gradient(to right, #2ecc71, #27ae60);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
            letter-spacing: 0.5px;
        }
        .back-button:hover {
            background: linear-gradient(to right, #27ae60, #229a5b);
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(46, 204, 113, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Enrolled Courses</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <p><?= $_SESSION['message']; ?></p>
            </div>
            <?php unset($_SESSION['message']); // Clear the message after displaying ?>
        <?php endif; ?>

        <?php if ($enrolled_courses->num_rows > 0): ?>
            <div class="course-list">
                <?php while ($row = $enrolled_courses->fetch_assoc()): ?>
                    <div class="course-card">
                        <div>
                            <h3><?= htmlspecialchars($row['name']) ?></h3>
                            <p><?= htmlspecialchars(substr($row['description'], 0, 150)) . (strlen($row['description']) > 150 ? '...' : '') ?></p>
                        </div>
                        <div class="enroll-info">
                            <strong>Enrolled On:</strong> <?= date('M d, Y', strtotime($row['enrollment_date'])) ?><br>
                            <strong>Status:</strong> <span class="status <?= strtolower(htmlspecialchars($row['status'])) ?>"><?= ucfirst(htmlspecialchars($row['status'])) ?></span>
                        </div>
                        <a href="course_details.php?course_id=<?= $row['course_id'] ?>" class="view-course-button">View Course Details</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>You are not currently enrolled in any courses.</p>
        <?php endif; ?>

        <a href="courses.php" class="back-button">← Browse More Courses</a>
    </div>
</body>
</html>