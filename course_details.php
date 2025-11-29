<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Enable error reporting for development (disable in production)
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

// Get course_id from URL
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    die("Error: No course ID provided.");
}

// Fetch course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id=?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $course_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Course not found.");
}
$course = $res->fetch_assoc();
$stmt->close();

// --- Check if user is already enrolled in this course ---
$user_id = $_SESSION['user_id'];
$is_enrolled = false;
$stmt_check_enrollment = $conn->prepare("SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ?");
if ($stmt_check_enrollment === false) {
    die("Prepare failed (check enrollment): " . $conn->error);
}
$stmt_check_enrollment->bind_param('ii', $user_id, $course_id);
$stmt_check_enrollment->execute();
$result_check_enrollment = $stmt_check_enrollment->get_result();
if ($result_check_enrollment->num_rows > 0) {
    $is_enrolled = true;
}
$stmt_check_enrollment->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['course_name']) ?> – Course Details</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.7.40/css/intlTelInput.css"/>

    <style>
        :root {
            --pri: #007bff;
            --pri-dark: #0056b3;
            --radius: 16px;
            --modal-card-bg: #ffffff;
            --modal-text-color: #333;
            --modal-border-color: #e0e0e0;
            --input-bg: #f8f8f8;
            --input-border-new: #cccccc;
            --input-focus-border-new: #4CAF50;
            --button-submit-bg: linear-gradient(to right, #4CAF50, #45a049);
            --button-submit-hover-bg: linear-gradient(to right, #45a049, #3e8e41);
            --shadow-subtle: rgba(0, 0, 0, 0.08);
            --shadow-medium-new: rgba(0, 0, 0, 0.15);
            --shadow-strong: rgba(0, 0, 0, 0.25);
        }

        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif
        }

        body {
            background: #f4f4f4 url('images/coursebackground.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 40px 50px;
        }

        .course-details {
            background: rgba(255, 255, 255, 0.2);
            padding: 30px;
            border-radius: var(--radius);
            max-width: 800px;
            margin: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            transition: 0.5s;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: rgb(255, 255, 255);
            text-align: center; /* Center the content inside the course details */
        }

        .course-details img {
            max-width: 100%;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .course-details h1 {
            font-size: 28px;
            margin-bottom: 12px;
            margin-top: 0;
        }

        .course-details p {
            font-size: 16px;
            margin-bottom: 10px;
            text-align: left; /* Align description text to the left */
        }

        /* BUTTON STYLING */
        .btn-row {
            margin-top: 30px;
            text-align: center;
        }

        .back-btn, .enroll-btn, .enrolled-btn {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s ease-in-out;
        }

        .back-btn {
            background-color: #6c757d;
            color: #fff;
        }

        .back-btn:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-subtle);
        }

        .enroll-btn {
            background-color: var(--pri);
            color: #fff;
            cursor: pointer;
            border: none; /* Make it a button style */
        }

        .enroll-btn:hover {
            background-color: var(--pri-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-subtle);
        }

        .enrolled-btn {
            background-color: #28a745; /* Green for already enrolled */
            color: #fff;
            cursor: default;
            opacity: 0.8;
            border: none;
        }
        .enrolled-btn:hover {
            transform: none; /* No hover effect for enrolled button */
            box-shadow: none;
        }


        /* MODAL */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 200;
            opacity: 0;
            pointer-events: none;
            transition: .4s ease-out;
        }

        .modal.show {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-card {
            background: var(--modal-card-bg);
            width: min(90vw, 480px);
            padding: 35px;
            border-radius: var(--radius);
            box-shadow: 0 15px 40px var(--shadow-strong);
            max-height: 90vh;
            overflow: auto;
            animation: pop .5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            color: var(--modal-text-color);
            border: 1px solid var(--modal-border-color);
        }

        @keyframes pop {
            from {
                transform: scale(.9);
                opacity: .6
            }
            to {
                transform: scale(1);
                opacity: 1
            }
        }

        .modal h2 {
            margin: 0 0 25px;
            font-weight: 700;
            font-size: 1.8rem;
            text-align: center;
            color: #222;
            position: relative;
            padding-bottom: 12px;
        }

        .modal h2::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 70px;
            height: 4px;
            background: linear-gradient(to right, #4CAF50, #2196F3);
            border-radius: 2px;
        }

        .fg {
            margin: 20px 0;
            position: relative;
        }

        .fg label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: .95rem;
            color: #555;
        }

        .fg input, .fg textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--input-border-new);
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color .3s ease, box-shadow .3s ease;
            background-color: var(--input-bg);
            color: #333;
        }

        .fg input:focus, .fg textarea:focus {
            outline: none;
            border-color: var(--input-focus-border-new);
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.2);
            background-color: #fff;
        }

        .fg textarea {
            resize: vertical;
            min-height: 90px;
        }

        /* Style for intl-tel-input specific elements */
        .iti {
            width: 100%;
        }

        .iti__flag-container {
            border-radius: 10px 0 0 10px;
        }

        .iti__selected-flag {
            border-radius: 10px 0 0 10px;
        }


        button.submit {
            width: 100%;
            padding: 16px 0;
            margin-top: 30px;
            border: none;
            border-radius: 10px;
            background: var(--button-submit-bg);
            color: #fff;
            font-weight: 700;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
        }

        button.submit:hover {
            background: var(--button-submit-hover-bg);
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(76, 175, 80, 0.4);
        }

        .close-x {
            position: absolute;
            top: 18px;
            right: 18px;
            font-size: 30px;
            cursor: pointer;
            color: #aaa;
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
            transition: color .3s ease, transform .3s ease;
        }

        .close-x:hover {
            color: #555;
            transform: rotate(90deg);
        }
    </style>
</head>
<body>

<div class="course-details" id="card">
    <h1><?= htmlspecialchars($course['name'] ?? 'N/A') ?></h1>
    <img src="<?= htmlspecialchars($course['image_path'] ?? 'path/to/default_image.jpg') ?>" alt="Course Image">
    <p><strong>Category:</strong> <?= htmlspecialchars($course['category'] ?? 'N/A') ?></p>
    <p><strong>Duration:</strong> <?= htmlspecialchars($course['duration'] ?? 'N/A') ?></p>
    <p><strong>Instructor:</strong> <?= htmlspecialchars($course['Instructor_name'] ?? 'N/A') ?></p>
    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($course['description'] ?? 'No description available.')) ?></p>

    <div class="btn-row">
        <a href="courses.php" class="back-btn">← Back to courses</a>
        <?php if ($is_enrolled): ?>
            <span class="enrolled-btn">Already Enrolled</span>
            <a href="my_courses.php" class="enroll-btn" style="background-color: #3498db;">Go to My Courses</a>
        <?php else: ?>
            <a href="#" class="enroll-btn" id="enrollBtn">Enroll Now</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!$is_enrolled): ?>
<div class="modal" id="enrollModal">
    <div class="modal-card">
        <button class="close-x" id="closeX">&times;</button>
        <h2>Enrolling for <?= htmlspecialchars($course['course_name'] ?? 'this course') ?></h2>

        <form action="enroll_course.php" method="POST" autocomplete="off">
            <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id) ?>">

            <div class="fg"><label for="first_name">First Name</label><input type="text" id="first_name" name="first_name" required value="<?= htmlspecialchars($_SESSION['first_name'] ?? '') ?>"></div>
            <div class="fg"><label for="last_name">Last Name</label><input type="text" id="last_name" name="last_name" required value="<?= htmlspecialchars($_SESSION['last_name'] ?? '') ?>"></div>

            <div class="fg">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>">
            </div>

            <div class="fg"><label for="school_college_company_name">School / College / Company Name</label>
                <input type="text" id="school_college_company_name" name="school_college_company_name" required value="<?= htmlspecialchars($_SESSION['organization'] ?? '') ?>"></div>

            <div class="fg"><label for="email">Email</label><input type="email" id="email" name="email" required value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>"></div>
            <div class="fg"><label for="address">Address</label><textarea id="address" name="address" rows="3" required><?= htmlspecialchars($_SESSION['address'] ?? '') ?></textarea></div>
            <div class="fg"><label for="pincode">Pincode</label><input type="text" id="pincode" name="pincode" required value="<?= htmlspecialchars($_SESSION['pincode'] ?? '') ?>"></div>
            <div class="fg"><label for="referral">Referral Code</label><input type="text" id="referral" name="referral" value="<?= htmlspecialchars($_SESSION['referral'] ?? '') ?>"></div>

            <button class="submit" type="submit">Submit Enrollment</button>
        </form>
    </div>
</div>
<?php endif; // End of is_enrolled check for modal ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.7.40/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.7.40/js/utils.min.js"></script>

<script>
    <?php if (!$is_enrolled): // Only initialize modal if not enrolled ?>
    const modal = document.getElementById('enrollModal');
    const openBtn = document.getElementById('enrollBtn');
    const closeX = document.getElementById('closeX');

    // Check if elements exist before attaching listeners
    if(openBtn) {
        openBtn.onclick = e => { e.preventDefault(); modal.classList.add('show'); };
    }
    if(closeX) {
        closeX.onclick = () => modal.classList.remove('show');
    }
    if(modal) {
        window.onclick = e => { if (e.target === modal) modal.classList.remove('show'); };
    }

    /* ---------- intl-tel-input ---------- */
    const phoneInput = document.querySelector("#phone");
    if(phoneInput) {
        intlTelInput(phoneInput, {
            initialCountry: "auto",
            geoIpLookup: cb => {
                fetch('https://ipapi.co/json')
                    .then(res => res.json())
                    .then(data => cb(data.country_code))
                    .catch(() => cb("us")); // Fallback to US if API fails
            },
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.7.40/js/utils.min.js"
        });
    }
    <?php endif; ?>
</script>
</body>
</html>