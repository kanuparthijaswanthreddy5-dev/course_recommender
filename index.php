<?php
session_start();

/* If the user is logged-in (session contains user_id) go straight to courses.php */
if (isset($_SESSION['user_id'])) {
    header("Location: courses.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligent Course Recommendation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-blue: #3498db;
            --secondary-green: #2ecc71;
            --dark-blue: #2c3e50;
            --light-grey: #ecf0f1;
            --text-dark: #333;
            --text-light: #f4f7fa;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-dark: rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--light-grey);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden; /* Prevent horizontal scroll from animations */
        }

        /* Header Styling */
        header {
            background: var(--dark-blue);
            color: var(--text-light);
            text-align: center;
            padding: 20px 0;
            box-shadow: 0 4px 15px var(--shadow-dark);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header h1 {
            font-size: 2.5em;
            font-weight: 800;
            letter-spacing: 1px;
        }

        /* Hero Section */
        .hero {
            /* Updated gradient for a more attractive and dynamic look */
            background: linear-gradient(to right, rgba(18, 206, 253, 0.7), rgba(97, 98, 93, 0.7)), 
                        url('images/inno.jpg') center/cover no-repeat;
            color: var(--text-light);
            text-align: center;
            padding: 150px 20px;
            position: relative;
            animation: heroFadeIn 1.5s ease-out forwards;
        }

        @keyframes heroFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero h1 {
            font-size: 3.8em;
            font-weight: 800;
            margin-bottom: 25px;
            text-shadow: 0 4px 8px var(--shadow-dark);
            animation: slideInLeft 1s ease-out forwards;
            opacity: 0;
            animation-delay: 0.5s;
        }

        .hero p {
            font-size: 1.5em;
            font-weight: 500;
            max-width: 800px;
            margin: 0 auto 40px;
            text-shadow: 0 2px 5px var(--shadow-dark);
            animation: slideInRight 1s ease-out forwards;
            opacity: 0;
            animation-delay: 0.8s;
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .hero .get-started-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 40px;
            background: linear-gradient(to right, var(--secondary-green), #27ae60);
            color: white;
            border-radius: 50px; /* Pill shape */
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2em;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.5);
            animation: popIn 0.8s ease-out forwards;
            opacity: 0;
            animation-delay: 1.2s;
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        .hero .get-started-btn:hover {
            background: linear-gradient(to right, #27ae60, #229a5b);
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 25px rgba(46, 204, 113, 0.7);
        }

        /* General Section Styling */
        .section {
            padding: 80px 20px;
            text-align: center;
            background: var(--text-light);
        }

        .section:nth-child(even) {
            background: #eaf2f8; /* Lighter blue-grey for even sections */
        }

        .section h2 {
            font-size: 2.8em;
            font-weight: 700;
            color: var(--dark-blue);
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }

        .section h2::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-blue);
            border-radius: 2px;
        }

        .section p {
            font-size: 1.1em;
            max-width: 900px;
            margin: auto;
            color: var(--text-dark);
        }

        /* Key Heading Box */
        .key-heading-box {
            display: inline-block;
            background-color: var(--primary-blue);
            color: white;
            padding: 15px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px var(--shadow-dark);
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 40px;
            transition: all 0.3s ease;
        }

        .key-heading-box:hover {
            background: var(--dark-blue);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px var(--shadow-dark);
        }

        /* Features Section */
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 50px;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 20px var(--shadow-light);
            padding: 30px 25px;
            width: 280px; /* Increased width */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px var(--shadow-dark);
        }

        .feature-card .icon {
            font-size: 3.5em; /* Larger icons */
            display: block;
            margin-bottom: 15px;
            color: var(--primary-blue);
            transition: color 0.3s ease;
        }
        .feature-card:hover .icon {
            color: var(--secondary-green);
        }

        .feature-card h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: var(--dark-blue);
            font-weight: 700;
        }

        .feature-card p {
            font-size: 1em;
            color: #555;
            font-weight: 400;
        }

        /* Customer Benefits Section (formerly Technology Section) */
        .customer-benefits-section {
            background: var(--dark-blue);
            color: var(--text-light);
            padding: 80px 20px;
            text-align: center;
        }

        .customer-benefits-section h2 {
            color: var(--text-light);
        }

        .benefits-points {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 50px;
        }

        .benefit-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border-radius: 15px;
            padding: 30px;
            width: 300px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .benefit-card:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.2);
        }

        .benefit-card .icon {
            font-size: 3em;
            color: var(--primary-blue);
            margin-bottom: 15px;
        }

        .benefit-card h3 {
            font-size: 1.4em;
            margin-bottom: 10px;
            color: var(--text-light);
            font-weight: 700;
        }

        .benefit-card p {
            font-size: 0.95em;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Footer Styling */
        footer {
            background: #1a2a3a; /* Even darker blue for footer */
            color: var(--light-grey);
            text-align: center;
            padding: 30px 20px;
            font-size: 0.9em;
            border-top: 5px solid var(--primary-blue);
        }

        footer a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: var(--light-blue-accent);
            text-decoration: underline;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.8em;
            }
            .hero p {
                font-size: 1.2em;
            }
            .section h2 {
                font-size: 2em;
            }
            .feature-card, .benefit-card { /* Updated for benefit-card */
                width: calc(50% - 20px); /* 2 columns on smaller screens */
            }
            .about-section {
                padding: 30px;
            }
        }

        @media (max-width: 480px) {
            header h1 {
                font-size: 2em;
            }
            .hero {
                padding: 100px 15px;
            }
            .hero h1 {
                font-size: 2.2em;
            }
            .hero p {
                font-size: 1em;
            }
            .hero .get-started-btn {
                padding: 12px 30px;
                font-size: 1em;
            }
            .section {
                padding: 60px 15px;
            }
            .section h2 {
                font-size: 1.8em;
            }
            .key-heading-box {
                font-size: 1.8em;
                padding: 12px 25px;
            }
            .feature-card, .benefit-card { /* Updated for benefit-card */
                width: 100%; /* 1 column on very small screens */
            }
            .about-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <header>
        <h1>Intelligent Course Recommendation System</h1>
    </header>

    <div class="hero">
        <h1>Unlock Your Potential with Personalized Learning</h1>
        <p>Our system intelligently connects you with the perfect courses, tailored to your interests and academic journey.</p>
        <a href="login.php" class="get-started-btn">Start Your Journey Now!</a>
    </div>

    <div class="section about-section">
        <h2>About This Project</h2>
        <p>
            The <strong>Intelligent Course Recommendation System</strong> is a robust platform designed to revolutionize how students discover and enroll in courses. Leveraging the power of a sophisticated <strong>Database Management System (DBMS)</strong>, it offers highly personalized course suggestions.
        </p>
        <p>
            Our core strength lies in efficiently managing vast amounts of data, including user profiles, course catalogs, and historical interactions. This allows us to deliver recommendations that are not just relevant, but truly transformative for your academic and professional growth. Built with a blend of <strong>PHP</strong>, <strong>MySQL</strong>, <strong>HTML</strong>, and <strong>CSS</strong>, the system ensures a seamless, dynamic, and intuitive user experience.
        </p>
        <p>
            Whether you're a high school graduate, a diploma holder, an undergraduate, or a postgraduate student, our system is engineered to simplify your course selection, making your educational journey smarter and more informed.
        </p>
    </div>

    <div class="section">
        <div class="key-heading-box">Key Features</div>
        <div class="features">
            <div class="feature-card">
                <span class="icon"><i class="fas fa-brain"></i></span>
                <h3>Intelligent Matching</h3>
                <p>Advanced algorithms to connect you with the most relevant courses.</p>
            </div>
            <div class="feature-card">
                <span class="icon"><i class="fas fa-filter"></i></span>
                <h3>Personalized Filters</h3>
                <p>Filter courses by your specific interests and current study level.</p>
            </div>
            <div class="feature-card">
                <span class="icon"><i class="fas fa-shield-alt"></i></span>
                <h3>Secure Authentication</h3>
                <p>Robust user login and registration system for your data security.</p>
            </div>
            <div class="feature-card">
                <span class="icon"><i class="fas fa-laptop-code"></i></span>
                <h3>Diverse Course Catalog</h3>
                <p>Explore a wide array of courses across various disciplines.</p>
            </div>
            <div class="feature-card">
                <span class="icon"><i class="fas fa-mobile-alt"></i></span>
                <h3>Responsive Design</h3>
                <p>Access your recommendations seamlessly on any device.</p>
            </div>
        </div>
    </div>

    <div class="customer-benefits-section"> <!-- Renamed class -->
        <h2>What Our System Offers You</h2> <!-- Changed heading -->
        <p>Experience a tailored learning journey designed to empower your academic and career goals.</p> <!-- Changed description -->
        <div class="benefits-points"> <!-- Renamed class -->
            <div class="benefit-card"> <!-- Renamed class -->
                <span class="icon"><i class="fas fa-magic"></i></span> <!-- New icon -->
                <h3>Effortless Discovery</h3>
                <p>Find courses that genuinely match your interests and aspirations with ease.</p>
            </div>
            <div class="benefit-card"> <!-- Renamed class -->
                <span class="icon"><i class="fas fa-user-check"></i></span> <!-- New icon -->
                <h3>Tailored Recommendations</h3>
                <p>Receive personalized course suggestions based on your unique profile and goals.</p>
            </div>
            <div class="benefit-card"> <!-- Renamed class -->
                <span class="icon"><i class="fas fa-graduation-cap"></i></span> <!-- New icon -->
                <h3>Academic Growth</h3>
                <p>Unlock new opportunities and expand your knowledge with relevant learning paths.</p>
            </div>
            <div class="benefit-card"> <!-- Renamed class -->
                <span class="icon"><i class="fas fa-lightbulb"></i></span> <!-- New icon -->
                <h3>Informed Decisions</h3>
                <p>Make confident choices about your education with clear and concise course insights.</p>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2025 Intelligent Course Recommendation System. All rights reserved. | Developed by Nitesh & Jashwanth
    </footer>

</body>
</html>
