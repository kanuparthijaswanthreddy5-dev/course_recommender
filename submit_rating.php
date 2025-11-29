<?php
session_start();

// Ensure user is logged in and request is AJAX
if (!isset($_SESSION['user_id']) || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized request.']);
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // <--- IMPORTANT: SET YOUR DATABASE PASSWORD HERE
$dbname = "course_recommender_db";

// Get data from the AJAX request
$user_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'] ?? null;
$rating = $_POST['rating'] ?? null;

// Validate data
if (!is_numeric($course_id) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid rating data.']);
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if the user is enrolled and has completed the course (you'll need to adjust the query based on your status values)
$stmt_check = $conn->prepare("SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'completed'");
if ($stmt_check === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error preparing enrollment check: ' . $conn->error]);
    $conn->close();
    exit;
}
$stmt_check->bind_param('ii', $user_id, $course_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'You are not eligible to rate this course.']);
    $stmt_check->close();
    $conn->close();
    exit;
}
$stmt_check->close();

// Update the rating in the enrollments table
$stmt_update = $conn->prepare("UPDATE enrollments SET rating = ? WHERE user_id = ? AND course_id = ?");
if ($stmt_update === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error preparing rating update: ' . $conn->error]);
    $conn->close();
    exit;
}
$stmt_update->bind_param('iii', $rating, $user_id, $course_id);

if ($stmt_update->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Rating submitted successfully!', 'rating' => $rating, 'course_id' => $course_id]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error updating rating: ' . $stmt_update->error]);
}

$stmt_update->close();
$conn->close();
?>