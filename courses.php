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
    die("Connection failed: " . $conn->connect_error);
}

// Get user email & first letter
$user_email = $_SESSION['email'];
$first_letter = strtoupper(substr($user_email, 0, 1)); // Changed to uppercase for icon

$interest = $_GET['interest'] ?? null;


// Interest mapping
$interest_mapping = [
    "Security" => ["Cybersecurity", "Ethical Hacking"],
    "Cloud Technology" => ["Cloud Computing"],
    "Database" => ["Database Management Systems"],
    "Blockchain" => ["Blockchain Technology"],
    "Networking" => ["Network Administration"],
    "Design" => ["UI/UX Design"],
    "Embedded Systems" => ["Internet of Things (IoT)"],
    "Data Science" => ["Big Data Analytics"],
    "Project Management" => ["Agile Project Management"],
    "Emerging Technology" => ["Augmented and Virtual Reality (AR/VR)"],
    "Development" => ["Development", "Software Development"],
    "Artificial Intelligence" => ["Machine Learning", "Artificial Intelligence", "Computer Vision"],
    "Computer Science" => ["Data Structures and Algorithms", "Operating Systems"]
];

// This boolean flag will determine if a valid filter is active
$is_filter_active = ($interest && array_key_exists($interest, $interest_mapping));

if ($is_filter_active) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE category = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('s', $interest);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM courses");
}

$no_courses_message = "";
if ($result) { // Ensure $result is not false
    if ($is_filter_active && $result->num_rows === 0) {
        $no_courses_message = "<p style='text-align:center; color:white; font-size: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);'>No courses found for the selected interest: " . htmlspecialchars($interest) . "</p>";
    } elseif (!$is_filter_active && $result->num_rows === 0) {
        $no_courses_message = "<p style='text-align:center; color:white; font-size: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);'>There are currently no courses available.</p>";
    }
} else {
    // Handle query error if $result is false
    $no_courses_message = "<p style='text-align:center; color:red; font-size: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);'>Error retrieving course data.</p>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Courses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        :root {
            --primary-gradient-start: #6a11cb;
            --primary-gradient-end: #2575fc;
            --secondary-gradient-start: #00f260;
            --secondary-gradient-end: #0575e6;
            --card-bg-alpha: rgba(255, 255, 255, 0.15);
            --card-border-alpha: rgba(255, 255, 255, 0.3);
            --text-color-light: #e0e0e0;
            --text-color-dark: #333;
            --shadow-strong: rgba(0, 0, 0, 0.5);
        }

        body {
            background-color: #0a0a0a; /* Darker base for contrast */
            margin: 0;
            background-image: url('images/Course_back_image.jpg'); /* Ensure this path is correct */
            font-family: 'Poppins', Arial, sans-serif;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            overflow-x: hidden; /* Prevent horizontal scroll */
            position: relative; /* For pseudo-element overlay */
        }

        /* Subtle overlay for background image */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Darker overlay */
            z-index: -1;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(70, 125, 45, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(70, 125, 45, 0); }
            100% { box-shadow: 0 0 0 0 rgba(70, 125, 45, 0); }
        }

        .user-container {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 100;
            animation: fadeIn 1s ease-out forwards;
        }
        
        /* IMPRESSIVE USER ICON */
        .user-icon {
            background: linear-gradient(135deg, rgb(70, 125, 45) 0%, rgb(100, 180, 70) 100%);
            color: white;
            font-weight: 700;
            font-size: 24px;
            width: 50px; /* Slightly larger */
            height: 50px; /* Slightly larger */
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            border: 4px solid rgba(255, 255, 255, 0.9); /* Thicker, more opaque border */
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4); /* Stronger initial shadow */
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden; /* Hide overflow for pulse effect */
        }
        
        .user-icon::before { /* Pulse effect */
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            opacity: 0;
            animation: pulse 2s infinite;
            z-index: -1;
        }

        .user-icon:hover {
            background: linear-gradient(135deg, rgb(26, 64, 35) 0%, rgb(50, 120, 40) 100%);
            transform: scale(1.15) rotate(5deg); /* More pronounced scale and slight rotation */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6); /* Even stronger shadow */
            border-color: white;
        }
        
        /* IMPRESSIVE LOGOUT BUTTON */
        .logout-btn {
            text-decoration: none;
            font-size: 18px;
            margin-right: 50px;
            font-weight: 700; /* Bolder */
            padding: 12px 25px; /* Adjusted padding */
            color: white;
            background: linear-gradient(to right, var(--primary-gradient-start), var(--primary-gradient-end)); /* Blue/Purple gradient */
            border: 2px solid rgba(255, 255, 255, 0.7);
            border-radius: 15px; /* More rounded */
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }

        .logout-btn:hover {
            background: linear-gradient(to right, var(--primary-gradient-end), var(--primary-gradient-start)); /* Invert gradient on hover */
            transform: translateY(-3px) scale(1.05); /* Lift and scale */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }

        .header-container {
            text-align: center;
            margin: 80px auto 30px; /* More top margin for breathing room */
            animation: slideInUp 1s ease-out forwards;
            opacity: 0;
        }

        #course-title {
            font-size: 44px; /* Larger title */
            font-weight: 800; /* Extra bold */
            padding: 25px 60px; /* Increased padding */
            background: linear-gradient(to right, #ff7e5f, #feb47b); /* Warm, inviting gradient */
            border-radius: 60px; /* More pronounced pill shape */
            display: inline-block;
            color: #4a2a0a; /* Darker text for contrast */
            animation: fadeSlide 1.2s ease-in-out forwards;
            opacity: 0;
            transform: translateY(-15px);
            box-sizing: border-box;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4); /* Stronger shadow */
            letter-spacing: 1px; /* Subtle letter spacing */
        }

        #course-title:hover {
            background: linear-gradient(to right, #feb47b, #ff7e5f); /* Invert gradient on hover */
            color: #fff; /* White text on hover */
            transform: scale(1.03) translateY(-5px); /* More dynamic hover */
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.6);
        }

        @keyframes fadeSlide {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .filter-buttons-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 25px; /* More space */
            margin-top: 30px; /* More margin */
            margin-bottom: 30px; /* More margin */
            animation: fadeIn 1.5s ease-out forwards;
            animation-delay: 0.5s;
            opacity: 0;
        }

        .filter-btn {
            padding: 12px 35px; /* Increased padding */
            font-size: 20px;
            font-weight: 700; /* Bolder */
            color: white; /* White text */
            border: 2px solid var(--card-border-alpha); /* Subtle border */
            background: linear-gradient(to right, var(--secondary-gradient-start), var(--secondary-gradient-end)); /* Vibrant gradient */
            border-radius: 30px; /* More rounded */
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            min-width: 180px; /* Increased min-width */
            min-height: 55px; /* Increased min-height */
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-sizing: border-box;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }

        .filter-btn:hover {
            background: linear-gradient(to right, var(--secondary-gradient-end), var(--secondary-gradient-start)); /* Invert gradient on hover */
            transform: scale(1.05) translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }

        .filter-dropdown {
            display: none;
            margin-top: 20px; /* More space */
            text-align: center;
            animation: fadeIn 1s ease-out forwards;
            animation-delay: 0.8s;
            opacity: 0;
        }

        .filter-dropdown form {
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 20px 30px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 5px 20px rgba(0,0,0,0.4);
        }

        .filter-dropdown select {
            padding: 12px;
            font-size: 18px;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            margin-right: 15px;
            appearance: none; /* Remove default arrow */
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23ffffff%22%20d%3D%22M287%20197.6l-14.2%2014.2-128.8-128.8-128.8%20128.8-14.2-14.2L144%2068.8z%22%2F%3E%3C%2Fsvg%3E'); /* Custom white arrow */
            background-repeat: no-repeat;
            background-position: right 10px top 50%;
            background-size: 12px;
            font-weight: 500;
        }
        .filter-dropdown select option {
            background-color: #333; /* Darker background for options */
            color: white;
        }

        .filter-dropdown button {
            padding: 12px 35px;
            font-size: 18px;
            border-radius: 30px;
            background: linear-gradient(to right, #FF416C, #FF4B2B); /* Red/Orange gradient */
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .filter-dropdown button:hover {
            background: linear-gradient(to right, #FF4B2B, #FF416C);
            transform: translateY(-2px);
        }

        .courses-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 30px; /* More padding */
            gap: 30px; /* More space between cards */
        }

        .course-card {
            width: calc(33.33% - 40px); /* Adjust width for 3 columns with gap */
            margin: 0; /* Margin handled by gap */
            padding: 25px; /* More padding */
            border-radius: 20px; /* More rounded */
            text-align: center;
            transition: transform 0.4s ease-in-out, box-shadow 0.4s ease-in-out, background 0.4s ease-in-out;
            background: var(--card-bg-alpha); /* Transparent white */
            backdrop-filter: blur(15px); /* Stronger blur */
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--card-border-alpha); /* Light border */
            box-shadow: 0 8px 25px var(--shadow-strong); /* Stronger shadow */
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out forwards; /* Animate cards on load */
            opacity: 0;
            /* Stagger animation delay for cards */
            animation-delay: calc(0.1s * var(--card-index)); 
        }
        
        /* Dynamic background for course cards on hover */
        .course-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #8A2BE2,rgb(244, 240, 159)); /* Vibrant purple/pink gradient */
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
            z-index: -1;
        }

        .course-card:hover::before {
            opacity: 1;
        }

        .course-card:hover {
            transform: scale(1.07) translateY(-15px) rotate(1deg); /* More dramatic hover with slight rotation */
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.8); /* Even stronger shadow */
            border-color: rgba(255, 255, 255, 0.6); /* More visible border on hover */
        }

        .course-card img {
            width: 100%;
            max-height: 180px; /* Max height for images */
            object-fit: cover; /* Ensure images cover the area */
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .course-card h3 {
            font-size: 24px; /* Larger heading */
            margin: 15px 0 10px;
            color: white;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        .course-card p {
            font-size: 16px;
            color: var(--text-color-light);
            margin-bottom: 10px;
            line-height: 1.5;
            font-weight: 400;
        }
        .course-card p strong {
            color: #f0f0f0;
        }

        .enroll-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: linear-gradient(to right, #00C9FF, #92FE9D); /* Bright blue/green gradient */
            color: #333; /* Dark text for contrast */
            border: none;
            border-radius: 30px; /* Pill shape */
            text-decoration: none;
            font-size: 18px;
            font-weight: 700;
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .enroll-btn:hover {
            background: linear-gradient(to right, #92FE9D, #00C9FF); /* Invert gradient */
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
        }

        .cw-footer {
            text-align:center;
            padding:48px 16px 32px;
            font-family:'Poppins', sans-serif; /* Changed to Poppins */
            background:rgba(235, 223, 191, 0.94); /* Existing background */
            color:var(--text-dark); /* Changed to dark text */
            margin-top: 50px;
            border-top: 5px solid var(--primary-gradient-start); /* Consistent border color */
            box-shadow: 0 -4px 15px var(--shadow-dark);
            }
            .cw-footer__logo {
            width:48px;
            margin:0 auto 12px;
            display:block;
            fill: var(--dark-blue); /* Darker logo color */
            }
            .cw-footer h2 {
            margin:0 0 6px;
            font-size:28px; /* Slightly larger */
            font-weight:800; /* Bolder */
            color:var(--dark-blue); /* Darker heading color */
            }
            .cw-footer p.tagline {
            margin:0 0 24px;
            font-size:16px;
            color: #555;
            }
            .cw-socials {
            display:flex;
            justify-content:center;
            gap:32px;
            margin-bottom:28px;
            }
            .cw-socials a svg {
            width:32px; /* Larger social icons */
            height:32px;
            fill:#777; /* Default grey */
            transition:fill .25s, transform .25s;
            }
            .cw-socials a:hover svg { 
            transform: translateY(-3px) scale(1.1);
        }
        /* GitHub icon (first link) */
        .cw-socials a:nth-child(1) svg { fill:#171515; }
        .cw-socials a:nth-child(1):hover svg { fill:#6e5494; /* GitHub purple */ }

        /* LinkedIn icon (second link) */
        .cw-socials a:nth-child(2) svg { fill:#0A66C2; }
        .cw-socials a:nth-child(2):hover svg { fill:#0077b5; /* LinkedIn blue */ }

        /* Twitter icon (third link) */
        .cw-socials a:nth-child(3) svg { fill:#1DA1F2; }
        .cw-socials a:nth-child(3):hover svg { fill:#1DA1F2; /* Twitter blue */ }


        /* purple accent */
            .cw-footer small {
            display:block;
            margin-top:8px;
            font-size:14px;
            color: #666;
            }
            .cw-footer a { 
            color:var(--primary-blue); /* Consistent primary blue */
            text-decoration:none; 
            font-weight: 600;
        }
            .cw-footer a:hover { 
            text-decoration:underline; 
            color: var(--light-blue-accent);
        }


        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .course-card {
                width: calc(50% - 30px); /* 2 columns on larger tablets/laptops */
            }
        }

        @media (max-width: 768px) {
            .user-container {
                top: 15px;
                right: 15px;
                gap: 10px;
            }
            .user-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
            .logout-btn {
                font-size: 16px;
                padding: 8px 18px;
            }
            #course-title {
                font-size: 32px;
                padding: 20px 40px;
            }
            .filter-buttons-wrapper {
                flex-direction: column;
                gap: 15px;
            }
            .filter-btn {
                min-width: 200px;
                min-height: 50px;
            }
            .filter-dropdown form {
                padding: 15px 20px;
            }
            .filter-dropdown select, .filter-dropdown button {
                font-size: 16px;
                padding: 10px 20px;
                margin-right: 0;
                margin-bottom: 10px;
            }
            .course-card {
                width: calc(100% - 30px); /* 1 column on mobile */
            }
            .course-card h3 {
                font-size: 20px;
            }
            .course-card p {
                font-size: 14px;
            }
            .enroll-btn {
                font-size: 16px;
                padding: 10px 20px;
            }
            .cw-footer h2 {
                font-size: 22px;
            }
        }

        @media (max-width: 480px) {
            .header-container {
                margin-top: 60px;
            }
            #course-title {
                font-size: 28px;
                padding: 15px 30px;
            }
            .filter-btn {
                font-size: 18px;
                padding: 10px 25px;
            }
            .filter-dropdown select {
                width: calc(100% - 10px);
                margin-bottom: 15px;
            }
            .filter-dropdown button {
                width: 100%;
            }
        }
    </style>
    <script>
    function toggleDropdown() {
        const dropdown = document.getElementById('filterDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }

    // Function to apply stagger animation to course cards
    document.addEventListener('DOMContentLoaded', () => {
        const courseCards = document.querySelectorAll('.course-card');
        courseCards.forEach((card, index) => {
            card.style.setProperty('--card-index', index);
        });
    });
    </script>
</head>
<body>

<div class="user-container">
    <!-- Changed to an anchor tag to link to profile.php -->
    <a href="profile.php" class="user-icon" title="<?php echo htmlspecialchars($user_email); ?>">
        <?php echo htmlspecialchars($first_letter); ?>
    </a>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="header-container">
    <h2 id="course-title">
    <?php
    if ($is_filter_active) {
        echo htmlspecialchars($interest) . " Courses";
    } else {
        echo "Discover Your Next Course";
    }
    ?>
</h2>
    <div class="filter-buttons-wrapper"> <!-- New container for filter buttons -->
        <button class="filter-btn" onclick="toggleDropdown()">Filter</button>
        <?php if ($is_filter_active): ?>
            <a href="?" class="filter-btn">Clear Filter</a> 
        <?php endif; ?>
    </div>
    <div class="filter-dropdown" id="filterDropdown" style="display: none;">
        <form method="GET" action="">
            <select name="interest" required>
                <option value="">Select Interest</option>
                <?php foreach ($interest_mapping as $key => $value): ?>
                    <option value="<?php echo htmlspecialchars($key); ?>" <?php if ($interest === $key) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($key); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Apply Filter</button>
        </form>
    </div>
</div>
<div class="courses-container">
    <?php 
    if (!empty($no_courses_message)) {
        echo $no_courses_message; // Display message if no courses found or error
    } elseif ($result && $result->num_rows > 0) { 
        while ($row = $result->fetch_assoc()) { 
    ?>
        <div class="course-card">
            <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Course Image">
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($row['short_intro'])); ?></p>
            <a href="course_details.php?course_id=<?php echo htmlspecialchars($row['course_id']); ?>" class="enroll-btn">Explore Now</a>
        </div>
    <?php 
        } 
    }
    if ($conn) { $conn->close(); } 
    ?>
</div>

<footer class="cw-footer">
    <svg class="cw-footer__logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 3h8a3 3 0 0 1 3 3v14a3 3 0 0 0-3-3H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm8 0h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-8a3 3 0 0 0-3 3V6a3 3 0 0 1 3-3ZM8 7h4v2H8V7Zm0 4h4v2H8v-2Z"/>
    </svg>

    <h2>Intelligent Course Recommendation System</h2>
    <p class="tagline">Your intelligent guide to learning.</p>

    <div class="cw-socials">
        <a href="https://github.com/your-repo" aria-label="GitHub">
        <svg viewBox="0 0 24 24"><path d="M12 .5a12 12 0 0 0-3.79 23.4c.6.11.82-.26.82-.58v-2.04c-3.34.73-4.04-1.6-4.04-1.6-.55-1.4-1.34-1.77-1.34-1.77-1.1-.75.08-.73.08-.73 1.22.09 1.86 1.25 1.86 1.25 1.08 1.84 2.83 1.31 3.52 1 .11-.78.42-1.31.76-1.61-2.66-.3-5.47-1.33-5.47-5.9 0-1.3.47-2.36 1.24-3.19-.12-.3-.54-1.52.12-3.16 0 0 1-.32 3.31 1.23A11.5 11.5 0 0 1 12 6.8a11.5 11.5 0 0 1 3.02.41c2.3-1.55 3.3-1.23 3.3-1.23.66 1.64.24 2.86.12 3.16.77.83 1.23 1.9 1.23 3.2 0 4.59-2.82 5.6-5.5 5.89.43.37.82 1.09.82 2.2v3.26c0 .32.21.7.82.58A12 12 0 0 0 12 .5Z"/></svg>
        </a>
        <a href="https://linkedin.com/in/your-profile" aria-label="LinkedIn">
        <svg viewBox="0 0 24 24"><path d="M4.98 3.5A2.5 2.5 0 1 1 5 8.5a2.5 2.5 0 0 1 0-5Zm.02 5.75H2V22h3V9.25Zm7.25 0H11V22h3v-6.5c0-1.72 2-1.86 2 0V22h3v-7.79c0-4.6-5.25-4.43-6.75-2.16v-2.8Z"/></svg>
        </a>
        <a href="https://twitter.com/your-handle" aria-label="Twitter">
        <svg viewBox="0 0 24 24"><path d="M23 2.94a9.6 9.6 0 0 1-2.83.78A4.93 4.93 0 0 0 22.39.37a9.8 9.8 0 0 1-3.13 1.2A4.9 4.9 0 0 0 16.2 0c-2.73 0-4.95 2.23-4.95 4.97 0 .39.04.77.12 1.13A13.94 13.94 0 0 1 1.64.88a4.97 4.97 0 0 0-.67 2.5 5 5 0 0 0 2.2 4.14 4.8 4.8 0 0 1-2.24-.62v.07c0 2.4 1.7 4.4 3.95 4.86-.41.11-.85.17-1.3.17-.32 0-.63-.03-.93-.09.64 2 2.5 3.46 4.7 3.5A9.86 9.86 0 0 1 0 19.54a13.88 13.88 0 0 0 7.55 2.22c9.06 0 14.01-7.55 14.01-14.09 0-.21 0-.42-.01-.63A10.07 10.07 0 0 0 23 2.94Z"/></svg>
        </a>
    </div>

    <small>© 2025 Intelligent Course Recommendation System. All rights reserved.</small>
    <small>Built by <a href="#">Nitesh & Jashwanth</a>.</small>
</footer>

</body>
</html>
