<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="logo.png">
    <title>পারভেজ খান রেসিডেন্সিয়াল স্কুল এন্ড কলেজ</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* --- 1. গ্লোবাল এবং ভেরিয়েবল --- */
        :root {
            --primary-color: #0056b3; /* প্রধান রঙ (নীল) */
            --secondary-color: #007bff; /* মাধ্যমিক রঙ */
            --accent-color: #28a745; /* হাইলাইট (সবুজ) */
            --text-color: #333;
            --light-bg: #f4f8ff;
            --white-bg: #ffffff;
            --border-color: #dee2e6;
            --font-family: 'Baloo Da 2', 'Kalpurush', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            line-height: 1.7;
            background-color: var(--light-bg);
            color: var(--text-color);
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 0 auto;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        a {
            text-decoration: none;
            color: var(--secondary-color);
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        section {
            padding: 40px 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.2rem;
            margin-bottom: 30px;
            position: relative;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--accent-color);
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: var(--accent-color);
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #218838;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .card {
            background: var(--white-bg);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 20px;
            overflow: hidden;
        }

        /* --- 2. হেডার এবং নেভিগেশন --- */

        @media (max-width: 768px) {
            .top-bar-info {
                padding: 2px;
            font-size: 0.8rem;
            justify-content: center;
            flex-direction: column;
            align-items: center;
            gap: 1px;
            }
        }
        .top-bar {
            background: var(--primary-color);
            color: #fff;
            padding: 8px 0;
            font-size: 0.9rem;
        }

        .top-bar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .top-bar-info span {
            margin-right: 15px;
        }
        .top-bar-info i {
            margin-right: 5px;
            color: var(--accent-color);
        }

        .main-nav {
            background: var(--white-bg);
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .main-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }

        .logo-text h1 {
            font-size: 1.4rem;
            margin: 0;
            line-height: 1.2;
        }
        .logo-text p {
            font-size: 0.9rem;
            margin: 0;
        }

        .nav-links {
            list-style: none;
            display: none; /* মোবাইল ভিউতে হাইড */
            flex-direction: column;
            width: 100%;
            background: var(--white-bg);
            position: absolute;
            top: 100px; /* Adjust based on nav height */
            left: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-links.active {
            display: flex; /* জাভাস্ক্রিপ্ট দিয়ে টগল হবে */
        }

        .nav-links li {
            width: 100%;
            text-align: center;
        }

        .nav-links li a {
            display: block;
            padding: 15px;
            color: var(--text-color);
            font-weight: 500;
            transition: background 0.3s, color 0.3s;
        }

        .nav-links li a:hover {
            background: var(--primary-color);
            color: #fff;
        }

        .menu-toggle {
            display: block; /* মোবাইল ভিউতে দেখা যাবে */
            font-size: 1.8rem;
            color: var(--primary-color);
            background: none;
            border: none;
            cursor: pointer;
        }

        /* --- 3. হিরো সেকশন (ব্যানার) --- */
        #hero {
            background: url('b.jpeg') center center/cover no-repeat;
            color: #fff;
            padding: 80px 0;
            text-align: center;
            position: relative;
        }
        
        #hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-content h2 {
            font-size: 2.5rem;
            color: #fff;
            margin-bottom: 15px;
        }
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 25px;
        }

        /* --- 4. পরিচিতি সেকশন --- */
        #introduction .card {
            text-align: center;
            font-size: 1.1rem;
        }

        /* --- 5. বাণী সেকশন --- */
        .message-card {
            text-align: center;
        }
        .message-card img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--border-color);
            margin-bottom: 15px;
        }
        .message-card h4 {
            font-size: 1.3rem;
            color: var(--accent-color);
            margin-bottom: 5px;
        }
        .message-card p.title {
            font-style: italic;
            color: #555;
            margin-bottom: 15px;
        }

        /* --- 6. শ্রেষ্ঠ সেকশন (শিক্ষক ও শিক্ষার্থী) --- */
        .award-card {
            text-align: center;
        }
        .award-card img {
            width: 160px;
            height: 160px;
            border-radius: 8px;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .award-card h4 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        .award-card p.achievement {
            font-size: 0.9rem;
            color: #555;
            font-style: italic;
        }

        /* --- 7. নোটিশ বোর্ড ও গ্যালারি --- */
        .notice-board ul {
            list-style: none;
        }
        .notice-board li {
            background: var(--light-bg);
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid var(--accent-color);
            transition: box-shadow 0.3s;
        }
        .notice-board li:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .notice-board li a {
            font-weight: 500;
        }
        .notice-board li span {
            display: block;
            font-size: 0.85rem;
            color: #777;
        }

        .gallery-grid {
            margin: 100px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
        }
        .gallery-item img {
            width: 100%;
            height: 100px;
            object-fit: fill;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .gallery-item img:hover {
            transform: scale(2.05);
        
            max-height: 100%;
        }

        /* --- 8. জাতীয় সংগীত --- */
        #anthem {
            background: var(--primary-color);
            color: #fff;
            text-align: center;
        }
        #anthem h2 {
            color: #fff;
        }
        #anthem blockquote {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 20px;
            font-style: italic;
        }
        #anthem audio {
            width: 100%;
            max-width: 400px;
        }

        /* --- 9. ক্লাবস সেকশন --- */
        .clubs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        .club-card {
            background: var(--white-bg);
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .club-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        .club-card i {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        .club-card h4 {
            font-size: 1.2rem;
            margin-bottom: 0;
        }

        /* --- 10. ভর্তি সেকশন --- */
        .admission-form, .admission-info {
            background: var(--white-bg);
            padding: 25px;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-family: var(--font-family);
        }
        .admission-info h4 {
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .admission-info ul {
            list-style-type: '✔';
            padding-left: 20px;
        }
        .admission-info li {
            margin-bottom: 10px;
        }

        /* --- 11. রুটিন ও ফলাফল --- */
        .routine-table-container {
            overflow-x: auto;
        }
        .routine-table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
            text-align: center;
            background: var(--white-bg);
        }
        .routine-table th,
        .routine-table td {
            border: 1px solid var(--border-color);
            padding: 12px;
        }
        .routine-table th {
            background: var(--primary-color);
            color: #fff;
        }
        .routine-table td {
            font-size: 0.9rem;
        }
        
        .result-links .btn {
            margin: 5px;
        }

        /* --- 12. শিক্ষকবৃন্দ --- */
        .teachers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .teacher-card {
            background: var(--white-bg);
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .teacher-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 4px solid var(--light-bg);
        }
        .teacher-card h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .teacher-card p {
            font-size: 0.9rem;
            color: #555;
            margin: 0;
        }

        /* --- 13. অ্যাডমিন লগইন --- */
        #login {
            background: var(--light-bg);
        }
        .login-panel {
            max-width: 400px;
            margin: 0 auto;
            background: var(--white-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .login-panel h3 {
            text-align: center;
        }

        /* --- 14. ফুটার --- */
        footer {
            background: #002b5c; /* গাঢ় নীল */
            color: #f0f0f0;
            padding: 40px 0;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        .footer-widget h4 {
            color: #fff;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 5px;
            display: inline-block;
        }
        .footer-widget p i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        .footer-widget ul {
            list-style: none;
        }
        .footer-widget ul li a {
            color: #f0f0f0;
            transition: color 0.3s;
            display: block;
            margin-bottom: 8px;
        }
        .footer-widget ul li a:hover {
            color: var(--accent-color);
        }
        .social-icons a {
            color: #fff;
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s;
        }
        .social-icons a:hover {
            color: var(--accent-color);
        }

        .copyright {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #3a5b82;
            font-size: 0.9rem;
        }

        /* --- 15. রেসপন্সিভ ডিজাইন (ডেস্কটপ) --- */
        @media (min-width: 768px) {
            .top-bar .container {
                flex-wrap: nowrap;
            }
            
            .menu-toggle {
                display: none;
            }

            .nav-links {
                display: flex;
                flex-direction: row;
                position: static;
                width: auto;
                background: none;
                box-shadow: none;
            }

            .nav-links li {
                width: auto;
            }
            .nav-links li a {
                padding: 10px 15px;
                border-radius: 5px;
            }
            .nav-links li a:hover {
                background: var(--light-bg);
                color: var(--primary-color);
            }
            
            .logo-text h1 {
                font-size: 1.8rem;
            }
            
            #hero {
                padding: 120px 0;
            }
            .hero-content h2 {
                font-size: 3.5rem;
            }

            .grid-2 {
                grid-template-columns: 1fr 1fr;
            }

            #notice-gallery.grid-2 {
                grid-template-columns: 1.2fr 1fr; /* নোটিশ বোর্ড বড় */
            }

            #admission .grid-2 {
                grid-template-columns: 2fr 1fr; /* ফর্ম বড় */
            }
            
            .clubs-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .footer-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .footer-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            .teachers-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* --- 16. মুদ্রণ (Print) স্টাইল --- */
        @media print {
            body {
                font-family: 'Times New Roman', serif;
                background: #fff;
                color: #000;
            }
            
            .top-bar, .main-nav, #hero, #admission form, #login, footer, .menu-toggle, .btn {
                display: none !important;
            }
            
            .container {
                width: 100%;
                max-width: 100%;
                margin: 0;
            }
            
            section {
                padding: 20px 0;
            }
            
            .card, .admission-info {
                box-shadow: none;
                border: 1px solid #ccc;
            }
            
            .grid-2, .teachers-grid, .clubs-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            h1, h2, h3, h4 {
                color: #000;
            }
            
            a {
                text-decoration: none;
                color: #000;
            }
            
            .routine-table th {
                background: #eee;
                color: #000;
            }
        }

    </style>
</head>
<body>

    <header class="top-bar">
        <div class="container">
            <div class="top-bar-info">
                <span><i class="fa-solid fa-phone"></i>01724-162121</span>
                <span><i class="fa-solid fa-envelope"></i>pkrsc.2018@gmail.com</span>
            </div>
            <div class="top-bar-links">
                <span>EMIS: 00704051910</span> | <span>SC: 487425</span>
                <a href="admin" style="color: #fff; margin-left: 15px;">লগইন</a>
            </div>
        </div>
    </header>

    <nav class="main-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="logo.png" alt="লোগো">
                <div class="logo-text">
                    <h1>পারভেজ খান রেসিডেন্সিয়াল স্কুল এন্ড কলেজ</h1>
                    <p>হাড়োয়া, দেবীর ডাঙ্গা, নীলফামারী । স্থাপিতঃ ২০১৮</p>
                </div>
            </a>

            <button class="menu-toggle" id="menu-toggle" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>

            <ul class="nav-links" id="nav-links">
                <li><a href="index.php">প্রচ্ছদ</a></li>
                <li><a href="admission.php">ভর্তি</a></li>
                <li><a href="academics.php">একাডেমিক</a></li>
                <li><a href="teachers.php">শিক্ষকবৃন্দ</a></li>
                <li><a href="gallery.php">গ্যালারি</a></li>
                <li><a href="notice.php">নোটিশ</a></li>
                <li><a href="club.php">ক্লাব</a></li>
                <li><a href="other.php">অন্যান্য</a></li>
            </ul>
        </div>
    </nav>
