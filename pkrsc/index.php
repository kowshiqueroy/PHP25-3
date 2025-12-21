<?php require 'includes/header.php'; ?>

<section class="hero">
    <div class="container">
        <h1 style="font-size: 2.5rem; margin-bottom: 10px;">স্বাগতম আমাদের শিক্ষা পরিবারে</h1>
        <p style="font-size: 1.2rem; margin-bottom: 20px;">আমরা আধুনিক ও নৈতিক শিক্ষার সমন্বয়ে আগামীর নেতৃত্ব গড়ে তুলি</p>
        <a href="admission.php" class="btn" style="background: var(--accent); color: #333; font-weight: bold;">
            <i class="fas fa-user-plus"></i> এখনই ভর্তি হোন
        </a>
    </div>
</section>

<div class="container">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        
        <div class="main-content">
            <div class="card">
                <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap; justify-content: center;">
                    <div style="background: #ddd; width: 150px; height: 180px; flex-shrink: 0; border-radius: 4px;">
                        </div>
                    <div>
                        <h2 style="color: var(--primary); text-align: center;">অধ্যক্ষের বাণী</h2>
                        <p style="text-align: center;">আমরা আধুনিক ও নৈতিক শিক্ষার সমন্বয়ে আগামীর নেতৃত্ব গড়ে তুলি</p>
                        <p style="color: var(--primary); font-weight: bold;  text-align: center;">নাম</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">আমাদের বৈশিষ্ট্যসমূহ</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><i class="fas fa-check-circle" style="color: green;"></i> মাল্টিমিডিয়া ক্লাসরুম</div>
                    <div><i class="fas fa-check-circle" style="color: green;"></i> আধুনিক কম্পিউটার ল্যাব</div>
                    <div><i class="fas fa-check-circle" style="color: green;"></i> সিসি ক্যামেরা দ্বারা নিয়ন্ত্রিত</div>
                    <div><i class="fas fa-check-circle" style="color: green;"></i> অভিজ্ঞ শিক্ষকমণ্ডলী</div>
                    <div><i class="fas fa-check-circle" style="color: green;"></i> বিস্তৃত পাঠ্যক্রম</div>
                    <div><i class="fas fa-check-circle" style="color: green;"></i> নিরাপদ পরিবেশ</div>
                    <div><i class="fas fa-check-circle" style="color: green;"></i> বিভিন্ন ক্লাব</div>
                    <div><i class="fas fa-check-circle" style="color: green;"></i> ইংরেজি চর্চা</div>
                </div>
            </div>
        </div>

        <aside>
            <div class="card">
                <h3 style="background: var(--secondary); color: white; padding: 10px; border-radius: 4px; text-align: center; margin-top: -35px; margin-bottom: 20px;">
                    <i class="fas fa-bell"></i> নোটিশ বোর্ড
                </h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="border-bottom: 1px solid #eee; padding: 10px 0;">
                        <small style="color: #888;">১২ ডিসেম্বর ২০২৫</small><br>
                        <a href="#" style="text-decoration: none; color: #333; font-weight: 600;">বার্ষিক পরীক্ষার রুটিন প্রকাশ</a>
                    </li>
                    <li style="border-bottom: 1px solid #eee; padding: 10px 0;">
                        <small style="color: #888;">১০ ডিসেম্বর ২০২৫</small><br>
                        <a href="#" style="text-decoration: none; color: #333; font-weight: 600;">শীতকালীন ছুটির বিজ্ঞপ্তি</a>
                    </li>
                </ul>
                <a href="notice.php" class="btn" style="width: 100%; text-align: center; margin-top: 10px;">সকল নোটিশ</a>
            </div>
            
            <div class="card" style="text-align: center;">
                <h3>জরুরি হটলাইন</h3>
                <h1 style="color: var(--secondary);">999</h1>
            </div>
        </aside>

    </div>
</div>

<?php require 'includes/footer.php'; ?>