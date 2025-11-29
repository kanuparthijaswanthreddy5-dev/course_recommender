<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Enable error reporting for development (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure a course_id is provided via POST
if (!isset($_POST['course_id'])) {
    $_SESSION['message'] = "Error: No course ID provided for enrollment.";
    header("Location: courses.php"); // Redirect back to courses list
    exit;
}

// DB connection
$servername = "localhost";
$username = "root";
$password = ""; // <--- IMPORTANT: SET YOUR DATABASE PASSWORD HERE
$dbname = "course_recommender_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    $_SESSION['message'] = "Database connection failed during enrollment: " . $conn->connect_error;
    header("Location: courses.php"); // Redirect back on DB error
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = (int)$_POST['course_id']; // Sanitize and cast to integer

// --- Data from the form (even though not inserted into enrollments, it's captured here) ---
// Note: These fields are usually part of the users table.
// They are passed in the form, but are NOT inserted into the 'enrollments' table.
// If you intend to save *updated* user profile data, that should be a separate "update profile" script.
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$organization = trim($_POST['school_college_company_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');
$referral = trim($_POST['referral'] ?? '');

// --- Basic Validation (Optional but recommended) ---
// You might want to do more robust validation here, but for this problem,
// we focus on the core enrollment logic.
if (empty($first_name) || empty($last_name) || empty($phone) || empty($email) || empty($address) || empty($pincode)) {
    $_SESSION['message'] = "Error: Please fill in all required personal details for enrollment.";
    header("Location: course_details.php?course_id=" . $course_id); // Redirect back to course page
    exit;
}

// 1. Check if the user is ALREADY enrolled in the course
$stmt_check = $conn->prepare("SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ?");
if ($stmt_check === false) {
    $_SESSION['message'] = "Error preparing enrollment check: " . $conn->error;
    header("Location: enrollment_success.php"); // Redirect to show the error
    exit;
}
$stmt_check->bind_param('ii', $user_id, $course_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // User is already enrolled
    $_SESSION['message'] = "You are already enrolled in this course.";
    $stmt_check->close();
    $conn->close();
    header("Location: enrollment_success.php"); // Redirect to show message
    exit;
}
$stmt_check->close();

// 2. If not enrolled, proceed with enrollment
// Only user_id and course_id are inserted into the 'enrollments' table.
// Enrollment_date and status are handled by default values in the table schema.
$stmt_enroll = $conn->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
if ($stmt_enroll === false) {
    $_SESSION['message'] = "Error preparing enrollment: " . $conn->error;
    header("Location: enrollment_success.php"); // Redirect to show the error
    exit;
}
$stmt_enroll->bind_param('ii', $user_id, $course_id);

if ($stmt_enroll->execute()) {
    $_SESSION['message'] = "Enrollment successful! You can now view this course in 'My Courses'.";
} else {
    // This could happen if foreign key constraints fail or other unexpected DB errors
    $_SESSION['message'] = "An error occurred during enrollment: " . $stmt_enroll->error;
}

$stmt_enroll->close();
$conn->close();

// Always redirect to the success page to display the message
header("Location: enrollment_success.php");
exit;
?>