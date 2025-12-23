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
            background: url('b.jpg') center center/cover no-repeat;
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
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
        }
        .gallery-item img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .gallery-item img:hover {
            transform: scale(1.05);
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
                <span><i class="fa-solid fa-phone"></i> ফোন: ০১৫২১২১২১২১১</span>
                <span><i class="fa-solid fa-envelope"></i> ইমেইল: pkrsc.2018@gmail.com</span>
            </div>
            <div class="top-bar-links">
                <span>EIIN: ৩৪২৩৪</span> | <span>স্থাপিত: ২০১৮</span>
                <a href="#login" style="color: #fff; margin-left: 15px;">অ্যাডমিন লগইন</a>
            </div>
        </div>
    </header>

    <nav class="main-nav">
        <div class="container">
            <a href="#" class="logo">
                <img src="logo.png" alt="লোগো">
                <div class="logo-text">
                    <h1>পারভেজ খান রেসিডেন্সিয়াল স্কুল এন্ড কলেজ</h1>
                    <p>হাড়োয়া, দেবীর ডাঙ্গা, নীলফামারী</p>
                </div>
            </a>

            <button class="menu-toggle" id="menu-toggle" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>

            <ul class="nav-links" id="nav-links">
                <li><a href="#">প্রচ্ছদ</a></li>
                <li><a href="#introduction">আমাদের সম্পর্কে</a></li>
                <li><a href="#admission">ভর্তি</a></li>
                <li><a href="#academics">একাডেমিক</a></li>
                <li><a href="#teachers">শিক্ষকবৃন্দ</a></li>
                <li><a href="#gallery">গ্যালারি</a></li>
                <li><a href="#notice">নোটিশ</a></li>
            </ul>
        </div>
    </nav>

    <main>

        <section id="hero">
            <div class="hero-content">
                <h2>শিক্ষাই জাতির মেরুদণ্ড</h2>
                <p>একটি আদর্শ ও মানসম্মত শিক্ষা প্রতিষ্ঠান</p>
                <a href="#admission" class="btn">ভর্তি তথ্য</a>
            </div>
        </section>

        <section id="introduction" class="container">
            <h2 class="section-title">আমাদের সম্পর্কে</h2>
            <div class="card">
                <p>পারভেজ খান রেসিডেন্সিয়াল স্কুল এন্ড কলেজ, ২০১৮ সালে প্রতিষ্ঠিত, নীলফামারীর হাড়োয়া, দেবীর ডাঙ্গা এলাকায় অবস্থিত একটি স্বনামধন্য শিক্ষা প্রতিষ্ঠান। আমরা শিক্ষার্থীদের মেধা ও মননের বিকাশে অঙ্গীকারবদ্ধ। আধুনিক শিক্ষা উপকরণ, অভিজ্ঞ শিক্ষক মণ্ডলী এবং একটি শিক্ষাবান্ধব পরিবেশ নিশ্চিত করার মাধ্যমে আমরা প্রতিটি শিক্ষার্থীকে ভবিষ্যতের জন্য যোগ্য নাগরিক হিসেবে গড়ে তুলতে সচেষ্ট। আমাদের লক্ষ্য শুধু একাডেমিক সাফল্যই নয়, বরং মানবিক মূল্যবোধ সম্পন্ন মানুষ তৈরি করা।</p>
            </div>
        </section>

        <section id="messages" class="container">
            <div class="grid-2">
                <div class="card message-card">
                    <img src="p.jpg" alt="প্রতিষ্ঠাতা">
                    <h4>মোঃ পারভেজ খান</h4>
                    <p class="title">প্রতিষ্ঠাতা সভাপতি ও অধ্যক্ষ</p>
                    <p>"শিক্ষার আলো প্রতিটি ঘরে পৌঁছে দেওয়াই আমাদের মূল লক্ষ্য। আমরা এমন একটি প্রজন্ম তৈরি করতে চাই যারা দেশ ও দশের কল্যাণে নিবেদিত প্রাণ হবে।"</p>
                </div>
                <div class="card message-card">
                    <img src="p.png" alt="অধ্যক্ষ">
                    <h4>অধ্যাপক ড. আশরাফ আলী (ডেমো)</h4>
                    <p class="title">উপাধ্যক্ষ</p>
                    <p>"শৃঙ্খলা, অধ্যবসায় এবং সৃজনশীলতাই সাফল্যের চাবিকাঠি। আমরা আমাদের শিক্ষার্থীদের এই মন্ত্রেই দীক্ষিত করি এবং তাদের সুপ্ত প্রতিভার বিকাশ ঘটাই।"</p>
                </div>
            </div>
        </section>

        <section id="awards" class="container">
            <div class="grid-2">
                <div class="card award-card">
                    <h3 class="section-title" style="font-size: 1.8rem; margin-bottom: 20px;">মাস সেরা শিক্ষক</h3>
                    <img src="p.png" alt="মাস সেরা শিক্ষক">
                    <h4>জনাব মোঃ রবিউল ইসলাম (ডেমো)</h4>
                    <p>সিনিয়র শিক্ষক (গণিত)</p>
                    <p class="achievement">"শিক্ষার্থীদের মধ্যে গণিতের প্রতি ভালোবাসা তৈরি এবং উদ্ভাবনী পাঠদানের জন্য তিনি প্রশংসিত।"</p>
                </div>
                <div class="card award-card">
                    <h3 class="section-title" style="font-size: 1.8rem; margin-bottom: 20px;">মাস সেরা শিক্ষার্থী</h3>
                    <img src="p.png" alt="মাস সেরা শিক্ষার্থী">
                    <h4>জান্নাতুল ফেরদৌস (ডেমো)</h4>
                    <p>শ্রেণী: দশম, বিজ্ঞান বিভাগ</p>
                    <p class="achievement">"শ্রেণীকক্ষে সর্বোচ্চ উপস্থিতি, দুর্দান্ত ফলাফল এবং সহ-শিক্ষা কার্যক্রমে অংশগ্রহণের জন্য নির্বাচিত।"</p>
                </div>
            </div>
        </section>

        <section id="notice-gallery" class="container">
            <div class="grid-2">
                <div id="notice">
                    <h2 class="section-title">নোটিশ বোর্ড</h2>
                    <div class="card notice-board">
                        <ul>
                            <li>
                                <a href="#">বার্ষিক ক্রীড়া প্রতিযোগিতা-২০২৬ এর সময়সূচী।</a>
                                <span><i class="fa-regular fa-calendar-days"></i> ১০ নভেম্বর, ২০২৫</span>
                            </li>
                            <li>
                                <a href="#">এসএসসি নির্বাচনী পরীক্ষার ফলাফল প্রকাশ।</a>
                                <span><i class="fa-regular fa-calendar-days"></i> ০৮ নভেম্বর, ২০২৫</span>
                            </li>
                            <li>
                                <a href="#">অভিভাবক সমাবেশ সংক্রান্ত বিজ্ঞপ্তি।</a>
                                <span><i class="fa-regular fa-calendar-days"></i> ০৫ নভেম্বর, ২০২৫</span>
                            </li>
                            <li>
                                <a href="#">অক্টোবর মাসের বেতন পরিশোধের শেষ তারিখ।</a>
                                <span><i class="fa-regular fa-calendar-days"></i> ০১ নভেম্বর, ২০২৫</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div id="notice">
                    <h2 class="section-title">ছুটির তালিকা</h2>
                    <div class="card notice-board">
                        <ul>
                            <li>
                                <a href="#">বার্ষিক ক্রীড়া প্রতিযোগিতা-২০২৬ এর সময়সূচী।</a>
                                <span><i class="fa-regular fa-calendar-days"></i> ১০ নভেম্বর, ২০২৫</span>
                            </li>
                            <li>
                                <a href="#">এসএসসি নির্বাচনী পরীক্ষার ফলাফল প্রকাশ।</a>
                                <span><i class="fa-regular fa-calendar-days"></i> ০৮ নভেম্বর, ২০২৫</span>
                            </li>
                            <li>
                                <a href="#">অভিভাবক সমাবেশ সংক্রান্ত বিজ্ঞপ্তি।</a>
                                <span><i class="fa-regular fa-calendar-days"></i> ০৫ নভেম্বর, ২০২৫</span>
                            </li>
                            <li>
                                <a href="#">অক্টোবর মাসের বেতন পরিশোধের শেষ তারিখ।</a>
                                <span><i class="fa-regular fa-calendar-days"></i> ০১ নভেম্বর, ২০২৫</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
            </div>
        </section>

        <section id="notice-gallery" class="container">
           
           
                <div id="gallery">
                    <h2 class="section-title">ফটো গ্যালারি</h2>
                    <div class="card">
                        <div class="gallery-grid">
                            <div class="gallery-item"><img src="p.png" alt="গ্যালারি ছবি"></div>
                            <div class="gallery-item"><img src="p.png" alt="গ্যালারি ছবি"></div>
                            <div class="gallery-item"><img src="p.png" alt="গ্যালারি ছবি"></div>
                            <div class="gallery-item"><img src="p.png" alt="গ্যালারি ছবি"></div>
                            <div class="gallery-item"><img src="p.png" alt="গ্যালারি ছবি"></div>
                            <div class="gallery-item"><img src="p.png" alt="গ্যালারি ছবি"></div>
                            <div class="gallery-item"><img src="p.png" alt="গ্যালারি ছবি"></div>
                            <div class="gallery-item"><img src="p.png" alt="গ্যালারি ছবি"></div>
                           
                        </div>
                    </div>
                </div>
         
        </section>

        <section id="anthem">
            <div class="container">
                <h2 class="section-title">জাতীয় সংগীত</h2>
                <blockquote>
                    আমার সোনার বাংলা, আমি তোমায় ভালোবাসি।<br>
                    চিরদিন তোমার আকাশ, তোমার বাতাস, আমার প্রাণে বাজায় বাঁশি...
                </blockquote>
                <audio controls>
                    <source src="demo_anthem.mp3" type="audio/mpeg">
                    আপনার ব্রাউজার অডিও সাপোর্ট করে না।
                </audio>
            </div>
        </section>

        <section id="clubs" class="container">
            <h2 class="section-title">সহ-শিক্ষা কার্যক্রম (ক্লাব)</h2>
            <div class="clubs-grid">
                <div class="club-card">
                    <i class="fa-solid fa-flask"></i>
                    <h4>বিজ্ঞান ক্লাব</h4>
                </div>
                <div class="club-card">
                    <i class="fa-solid fa-comments"></i>
                    <h4>বিতর্ক ক্লাব</h4>
                </div>
                <div class="club-card">
                    <i class="fa-solid fa-computer"></i>
                    <h4>আইসিটি ক্লাব</h4>
                </div>
                <div class="club-card">
                    <i class="fa-solid fa-book-open"></i>
                    <h4>সাহিত্য ক্লাব</h4>
                </div>
            </div>
        </section>

        <section id="admission" class="container">
            <h2 class="section-title">ভর্তি তথ্য</h2>
            <div class="grid-2">
                <div class="admission-form card">
                    <h3>ভর্তি ফর্ম (ডেমো)</h3>
                    <form>
                        <div class="form-group">
                            <label for="name">শিক্ষার্থীর নাম</label>
                            <input type="text" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="fname">পিতার নাম</label>
                            <input type="text" id="fname" required>
                        </div>
                        <div class="form-group">
                            <label for="class">আগ্রহী শ্রেণী</label>
                            <select id="class">
                                <option value="6">৬ষ্ঠ শ্রেণী</option>
                                <option value="7">৭ম শ্রেণী</option>
                                <option value="8">৮ম শ্রেণী</option>
                                <option value="9">৯ম শ্রেণী</option>
                                <option value="11">একাদশ শ্রেণী</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">মোবাইল নম্বর</label>
                            <input type="tel" id="phone" required>
                        </div>
                        <button type="submit" class="btn">সাবমিট</button>
                    </form>
                </div>
                <div class="admission-info card">
                    <h4>ভর্তির যোগ্যতা</h4>
                    <ul>
                        <li>পূর্ববর্তী শ্রেণীর ছাড়পত্র।</li>
                        <li>২ কপি পাসপোর্ট সাইজের ছবি।</li>
                        <li>জন্ম সনদ/আইডি কার্ডের কপি।</li>
                        <li>ভর্তি পরীক্ষায় উত্তীর্ণ হতে হবে।</li>
                    </ul>
                    <hr style="margin: 20px 0;">
                    <h4>যোগাযোগ</h4>
                    <p>
                        <strong>ঠিকানা:</strong> হাড়োয়া, দেবীর ডাঙ্গা, নীলফামারী।<br>
                        <strong>ফোন:</strong> ০১৫২১২১২১২১১<br>
                        <strong>ইমেইল:</strong> admission@pkrsac.edu.bd
                    </p>
                </div>
            </div>
        </section>
        
        <section id="academics" class="container">
            <h2 class="section-title">একাডেমিক কর্নার</h2>
            
            <div class="grid-2">
                <div class="card">
                    <h3>একাডেমিক ফলাফল</h3>
                    <p>আপনার পরীক্ষার ফলাফল দেখতে নিচের বাটন ক্লিক করুন অথবা পিডিএফ ডাউনলোড করুন।</p>
                    <div class="result-links">
                        <a href="#" class="btn">ফলাফল দেখুন (অনলাইন)</a>
                        <a href="#" class="btn" style="background-color: var(--secondary-color);">ফলাফল (পিডিএফ)</a>
                    </div>
                </div>
                <div class="card">
                    <h3>ক্লাস রুটিন</h3>
                    <p>সাপ্তাহিক ক্লাস রুটিন দেখতে নিচের বাটন ক্লিক করুন।</p>
                     <a href="#" class="btn">সম্পূর্ণ রুটিন দেখুন</a>
                </div>
            </div>
            
            <div class="card" style="margin-top: 30px;">
                <h3>ডেমো ক্লাস রুটিন (১০ম শ্রেণী - বিজ্ঞান)</h3>
                <div class="routine-table-container">
                    <table class="routine-table">
                        <thead>
                            <tr>
                                <th>বার</th>
                                <th>১ম পিরিয়ড</th>
                                <th>২য় পিরিয়ড</th>
                                <th>৩য় পিরিয়ড</th>
                                <th>৪র্থ পিরিয়ড</th>
                                <th>বিরতি</th>
                                <th>৫ম পিরিয়ড</th>
                                <th>৬ষ্ঠ পিরিয়ড</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>শনিবার</td>
                                <td>বাংলা ১ম</td>
                                <td>ইংরেজি ১ম</td>
                                <td>গণিত</td>
                                <td>পদার্থ</td>
                                <td>--</td>
                                <td>রসায়ন</td>
                                <td>জীববিজ্ঞান</td>
                            </tr>
                            <tr>
                                <td>রবিবার</td>
                                <td>বাংলা ২য়</td>
                                <td>ইংরেজি ২য়</td>
                                <td>গণিত</td>
                                <td>রসায়ন</td>
                                <td>--</td>
                                <td>পদার্থ</td>
                                <td>আইসিটি</td>
                            </tr>
                            <tr>
                                <td>সোমবার</td>
                                <td>বাংলা ১ম</td>
                                <td>ইংরেজি ১ম</td>
                                <td>গণিত</td>
                                <td>জীববিজ্ঞান</td>
                                <td>--</td>
                                <td>পদার্থ</td>
                                <td>ধর্ম</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="teachers" class="container">
            <h2 class="section-title">আমাদের অভিজ্ঞ শিক্ষকগণ</h2>
            <div class="teachers-grid">
                <div class="teacher-card">
                    <img src="p.png" alt="শিক্ষক ১">
                    <h4>মোঃ আব্দুল করিম (ডেমো)</h4>
                    <p>সিনিয়র শিক্ষক (বাংলা)</p>
                </div>
                <div class="teacher-card">
                    <img src="p.png" alt="শিক্ষক ২">
                    <h4>মোসাঃ ফাতেমা বেগম (ডেমো)</h4>
                    <p>সিনিয়র শিক্ষক (ইংরেজি)</p>
                </div>
                <div class="teacher-card">
                    <img src="p.png" alt="শিক্ষক ৩">
                    <h4>জনাব মোঃ রবিউল ইসলাম (ডেমো)</h4>
                    <p>সিনিয়র শিক্ষক (গণিত)</p>
                </div>
                <div class="teacher-card">
                    <img src="p.png" alt="শিক্ষক ৪">
                    <h4>আশরাফুল আলম (ডেমো)</h4>
                    <p>সহকারী শিক্ষক (বিজ্ঞান)</p>
                </div>  <div class="teacher-card">
                    <img src="p.png" alt="শিক্ষক ১">
                    <h4>মোঃ আব্দুল করিম (ডেমো)</h4>
                    <p>সিনিয়র শিক্ষক (বাংলা)</p>
                </div>
                <div class="teacher-card">
                    <img src="p.png" alt="শিক্ষক ২">
                    <h4>মোসাঃ ফাতেমা বেগম (ডেমো)</h4>
                    <p>সিনিয়র শিক্ষক (ইংরেজি)</p>
                </div>
                <div class="teacher-card">
                    <img src="p.png" alt="শিক্ষক ৩">
                    <h4>জনাব মোঃ রবিউল ইসলাম (ডেমো)</h4>
                    <p>সিনিয়র শিক্ষক (গণিত)</p>
                </div>
                <div class="teacher-card">
                    <img src="p.png" alt="শিক্ষক ৪">
                    <h4>আশরাফুল আলম (ডেমো)</h4>
                    <p>সহকারী শিক্ষক (বিজ্ঞান)</p>
                </div>
            </div>
        </section>

        <section id="login" class="container">
            <div class="login-panel">
                <h3 class="section-title" style="font-size: 1.8rem;">অ্যাডমিন লগইন</h3>
                <form>
                    <div class="form-group">
                        <label for="username">ইউজারনেম</label>
                        <input type="text" id="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">পাসওয়ার্ড</label>
                        <input type="password" id="password" required>
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">লগইন করুন</button>
                </form>
            </div>
        </section>

    </main>

    <footer>
        <div class="container footer-grid">
            <div class="footer-widget">
                <h4>পারভেজ খান রেসিডেন্সিয়াল স্কুল এন্ড কলেজ</h4>
                <p>EIIN: ৩৪২৩৪, স্থাপিত: ২০১৮</p>
                <p><i class="fa-solid fa-location-dot"></i> ঠিকানা: হাড়োয়া, দেবীর ডাঙ্গা, নীলফামারী</p>
                <p><i class="fa-solid fa-phone"></i> ফোন: ০১৫২১২১২১২১১</p>
                <p><i class="fa-solid fa-envelope"></i> ইমেইল: pkrsc.2018@gmail.com</p>
            </div>
            
            <div class="footer-widget">
                <h4>দ্রুত লিঙ্ক</h4>
                <ul>
                    <li><a href="#admission">ভর্তি তথ্য</a></li>
                    <li><a href="#notice">নোটিশ বোর্ড</a></li>
                    <li><a href="#academics">ফলাফল</a></li>
                    <li><a href="#academics">ক্লাস রুটিন</a></li>
                    <li><a href="#teachers">শিক্ষক তালিকা</a></li>
                </ul>
            </div>
            
            <div class="footer-widget">
                <h4>একাডেমিক</h4>
                <ul>
                    <li><a href="#">প্রাতিষ্ঠানিক ক্যালেন্ডার</a></li>
                    <li><a href="#">সিলেবাস</a></li>
                    <li><a href="#">ছুটির তালিকা</a></li>
                    <li><a href="#">লাইব্রেরি</a></li>
                </ul>
            </div>

            <div class="footer-widget">
                <h4>সামাজিক মাধ্যম</h4>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="container copyright">
            <p>&copy; ২০২৫ সর্বস্বত্ব সংরক্ষিত। <a href="mailto:kowshiqueroy@gmail.com" target="_blank">Developed by kowshiqueroy@gmail.com</a></p>
        </div>
    </footer>

    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const navLinks = document.getElementById('nav-links');

        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    </script>

</body>
</html>