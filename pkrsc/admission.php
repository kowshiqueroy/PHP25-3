   <?php
require_once 'header.php';
?>
    <main>

       
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
      

    </main>

   <?php
require_once 'footer.php';
?>