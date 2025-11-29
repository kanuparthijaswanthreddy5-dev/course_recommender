<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
$servername = "localhost";
$username = "root";
$password = ""; // <--- IMPORTANT: SET YOUR DATABASE ROOT PASSWORD HERE
$dbname = "course_recommender_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$stmt_user = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
if ($stmt_user === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("User not found.");
}

$user_data = $result_user->fetch_assoc();
$stmt_user->close();

// Fetch the total number of courses the user has applied for
$stmt_applied_courses = $conn->prepare("SELECT COUNT(*) AS total_applied_courses FROM enrollments WHERE user_id = ?");
if ($stmt_applied_courses === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_applied_courses->bind_param('i', $user_id);
$stmt_applied_courses->execute();
$result_applied_courses = $stmt_applied_courses->get_result();

if ($result_applied_courses->num_rows > 0) {
    $row_applied_courses = $result_applied_courses->fetch_assoc();
    $total_applied_courses = $row_applied_courses['total_applied_courses'];
} else {
    $total_applied_courses = 0;
}

$stmt_applied_courses->close();

// Fetch enrolled courses with status and existing rating
$stmt_enrolled_courses = $conn->prepare("
    SELECT c.name AS course_name, e.status, e.course_id, e.rating
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.user_id = ?
");

if ($stmt_enrolled_courses === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_enrolled_courses->bind_param('i', $user_id);
$stmt_enrolled_courses->execute();
$enrolled_courses = $stmt_enrolled_courses->get_result();

// Close DB connection
$stmt_enrolled_courses->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #2c3e50; /* Darker background */
            background-image: url('images/courseback.jpg'); /* Use your existing background */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #ecf0f1; /* Light text color */
        }

        .profile-container {
            background: rgba(33, 47, 60, 0.9); /* Slightly darker, more opaque background */
            backdrop-filter: blur(12px); /* Slightly stronger blur */
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15); /* Softer border */
            border-radius: 15px; /* Slightly less rounded corners */
            padding: 40px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5); /* More prominent shadow */
            max-width: 650px; /* Slightly wider for better content distribution */
            width: 90%; /* Responsive width */
            text-align: center;
            animation: fadeIn 0.8s ease-out;
            position: relative;
            overflow: hidden; /* Ensures clock doesn't overflow */
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-container h1 {
            font-size: 2.8rem; /* Slightly larger title */
            margin-bottom: 30px; /* More space below title */
            color: #3498db; /* Blue accent for title */
            font-weight: 700;
            position: relative;
            padding-bottom: 15px; /* More space below underline */
        }

        .profile-container h1::after {
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

        .profile-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px; /* Increased gap for better separation */
            text-align: left;
            margin-top: 35px;
        }

        @media (min-width: 600px) {
            .profile-info {
                grid-template-columns: 1fr 1fr;
            }
        }

        .info-item {
            background: rgba(255, 255, 255, 0.05); /* Lighter, more subtle background */
            padding: 20px 25px; /* Increased padding */
            border-radius: 12px; /* Slightly more rounded corners */
            border: 1px solid rgba(255, 255, 255, 0.08); /* More subtle border */
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25); /* Stronger shadow */
            transition: transform 0.2s ease, background 0.2s ease; /* Added background transition */
        }

        .info-item:hover {
            transform: translateY(-5px); /* More pronounced lift on hover */
            background: rgba(255, 255, 255, 0.1); /* Slightly brighter on hover */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4); /* Stronger shadow on hover */
        }

        .info-item strong {
            display: block;
            font-size: 1rem; /* Slightly larger label font */
            color: #b0c4de; /* Softer, slightly blue-ish grey for labels */
            margin-bottom: 8px; /* More space below label */
            font-weight: 500; /* Medium weight for labels */
        }

        .info-item span {
            font-size: 1.2rem; /* Larger main text */
            color: #ecf0f1;
            word-wrap: break-word;
            line-height: 1.4; /* Better readability for long text */
        }

        .back-button {
            display: inline-block;
            margin-top: 50px; /* More space above button */
            padding: 14px 35px; /* Larger padding */
            background: linear-gradient(to right, #2ecc71, #27ae60); /* Green gradient button */
            color: white;
            text-decoration: none;
            border-radius: 10px; /* Slightly more rounded button */
            font-weight: 600;
            font-size: 1.1rem; /* Larger button text */
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4); /* More prominent shadow */
            letter-spacing: 0.5px; /* Slightly increased letter spacing */
        }

        .back-button:hover {
            background: linear-gradient(to right, #27ae60, #229a5b);
            transform: translateY(-4px); /* More pronounced lift on hover */
            box-shadow: 0 10px 25px rgba(46, 204, 113, 0.5); /* Stronger shadow on hover */
        }

        h2 {
            font-size: 2rem;
            color: #3498db;
            margin-top: 40px;
            margin-bottom: 20px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column; /* Arrange items vertically */
            justify-content: space-between;
            align-items: flex-start; /* Align items to the left */
        }

        li span {
            color: #ecf0f1;
            margin-bottom: 5px; /* Add some space below course name and status */
        }

        li .status {
            font-weight: bold;
            color: #2ecc71; /* Default to green */
        }
        li .status.pending {
            color: #f39c12;
        }
        li .status.completed {
            color: #2ecc71;
        }
        li .status.dropped {
            color: #e74c3c;
        }

        .rating-container {
            display: flex; /* Align "Rate this course" and stars horizontally */
            align-items: center;
            gap: 10px; /* Space between label and stars */
        }

        .rating {
            display: inline-block;
        }
        .rating .star.active {
            color: gold; /* Highlight the active rating */
        }
        .star {
            cursor: pointer;
            font-size: 1.5em;
            color: gold; /* Initial color for stars */
        }

        .rating-container p {
            margin: 0; /* Remove default paragraph margins */
        }

        .status-bar-container {
            background-color: #bdc3c7;
            border-radius: 5px;
            height: 10px;
            width: 150px;
            overflow: hidden;
            margin-top: 5px; /* Add some space below status */
            margin-bottom: 10px; /* Add some space above rating */
        }

        .status-bar {
            background-color: #2ecc71;
            height: 100%;
            width: 0%; /* Will be set dynamically based on status */
            border-radius: 5px;
            transition: width 0.3s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Your Profile</h1>
        <div class="profile-info">
            <div class="info-item">
                <strong>Email:</strong>
                <span><?= htmlspecialchars($user_data['email'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>First Name:</strong>
                <span><?= htmlspecialchars($user_data['first_name'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Last Name:</strong>
                <span><?= htmlspecialchars($user_data['last_name'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Phone Number:</strong>
                <span><?= htmlspecialchars($user_data['phone'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Organization:</strong>
                <span><?= htmlspecialchars($user_data['school_college_company_name'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Address:</strong>
                <span><?= nl2br(htmlspecialchars($user_data['address'] ?? 'N/A')) ?></span>
            </div>
            <div class="info-item">
                <strong>Pincode:</strong>
                <span><?= htmlspecialchars($user_data['pincode'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Study Level:</strong>
                <span><?= htmlspecialchars($user_data['study'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Date of Birth:</strong>
                <span><?= htmlspecialchars($user_data['dob'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Total Courses Applied:</strong>
                <span><?= htmlspecialchars($total_applied_courses) ?></span>
            </div>
        </div>

        <h2>Enrolled Courses</h2>
        <?php if ($enrolled_courses->num_rows > 0): ?>
            <ul>
                <?php while ($row_enrolled = $enrolled_courses->fetch_assoc()): ?>
                    <li>
                        <span><?= htmlspecialchars($row_enrolled['course_name']) ?></span>
                        <span class="status <?= strtolower(htmlspecialchars($row_enrolled['status'])) ?>">
                            <?php
                            $status = htmlspecialchars($row_enrolled['status']);
                            if (strtolower($status) == 'enrolled') {
                                echo 'In Progress';
                            } else {
                                echo ucfirst(strtolower($status));
                            }
                            ?>
                        </span>
                        <div class="status-bar-container">
                            <div class="status-bar" style="width: <?php
    $status_bar = strtolower($row_enrolled['status']);
    if ($status_bar == 'completed') echo '100%';
    elseif ($status_bar == 'in progress' || $status_bar == 'enrolled') echo '50%';
    elseif ($status_bar == 'pending') echo '10%';
    elseif ($status_bar == 'dropped') echo '0%';
    else echo '0%';
    ?>"></div>
                        </div>
                        <?php if (strtolower($row_enrolled['status']) == 'completed'): ?>
                            <div class="rating-container">
                                <strong>Rate this course:</strong>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star" data-rating="<?= $i ?>" data-course-id="<?= $row_enrolled['course_id'] ?>">☆</span>
                                    <?php endfor; ?>
                                    <?php if (!empty($row_enrolled['rating'])): ?>
                                        <p>Your rating: <?= htmlspecialchars($row_enrolled['rating']) ?> ★</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No courses enrolled yet.</p>
        <?php endif; ?>

        <a href="my_courses.php" class="back-button" style="margin-right: 10px;">Go to My Courses</a>
        <a href="courses.php" class="back-button">← Back to Courses</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.rating .star');

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    const courseId = this.dataset.courseId;
                    const parentRatingDiv = this.parentNode;

                    // Send AJAX request to submit_rating.php
                    fetch('submit_rating.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest' // Important for security in submit_rating.php
                        },
                        body: `course_id=${courseId}&rating=${rating}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Update the displayed rating (you might want to refresh the enrolled courses section)
                            parentRatingDiv.innerHTML = `<p>Your rating: ${data.rating} ★</p>`;
                            alert(data.message); // Or display a nicer message
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting rating:', error);
                        alert('An error occurred while submitting the rating.');
                    });

                    // You might also want to visually update the stars to show the selected rating
                    // This could involve highlighting stars up to the clicked one
                    stars.forEach(s => {
                        if (s.dataset.rating <= rating && s.dataset.courseId === courseId) {
                            s.textContent = '★'; // Filled star
                            s.classList.add('active');
                        } else if (s.dataset.courseId === courseId) {
                            s.textContent = '☆'; // Outline star
                            s.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>