   <?php
require_once 'header.php';
?>
    <main>

       

      <section id="clubs" class="container">
            <h2 class="section-title">সহ-শিক্ষা কার্যক্রম (ক্লাব)</h2>
            <div class="clubs-grid">
                <div class="club-card">
                    <i class="fa-solid fa-computer"></i>
                    <h4>বিজ্ঞান ক্লাব</h4>
                    <h4>Science Club</h4>
                    <p>বিজ্ঞান ও কম্পিউটার চর্চা</p>
                    
                </div>
                <div class="club-card">
                    <i class="fa-solid fa-comments"></i>
                    <h4>ভাষা ও দক্ষতা ক্লাব</h4>
                     <h4>Language and Skills Club</h4>
                    <p>বাংলা, ইংরেজী, বিতর্ক, আবৃত্তি</p>
                </div>
                <div class="club-card">
                    <i class="fa-solid fa-masks-theater"></i>
                    <h4>সাংস্কৃতিক ক্লাব</h4>
                    <h4>Cultural Club</h4>
                    <p>নৃত্য, গান, নাটক, চিত্রাঙ্কন</p>
                </div>
                <div class="club-card">
                    <i class="fa-solid fa-football"></i>
                    <h4>ক্রীড়া ক্লাব</h4>
                    <h4>Sports Club</h4>
                    <p>ফুটবল, ক্রিকেট, ব্যাডমিন্টন</p>
                </div>
            </div>
        </section>

<section id="club-leaders" class="container">
  <h2 class="section-title">💙 ক্লাব নেতৃত্ব ও কার্যক্রম (Club Leadership & Activities)</h2>
  <div class="leaders-grid">
    <div class="leader-card">
      <h4>🧪 Science Club</h4>
      <p><span class="tag">Convenor:</span> ড. রহমান</p>
      <p><span class="tag">President:</span> সাদিয়া ইসলাম</p>
      <p><span class="tag">Meeting Time:</span> প্রতি বৃহস্পতিবার, ৩:৩০ PM</p>
      <p><span class="tag">Place:</span> ল্যাবরেটরি রুম</p>
      <p><span class="tag">Next Program:</span> রোবটিক্স ওয়ার্কশপ</p>
    </div>

    <div class="leader-card">
      <h4>💬 Language & Skills Club</h4>
      <p><span class="tag">Convenor:</span> মিসেস করিম</p>
      <p><span class="tag">President:</span> আরিফ হোসেন</p>
      <p><span class="tag">Meeting Time:</span> প্রতি মঙ্গলবার, ২:০০ PM</p>
      <p><span class="tag">Place:</span> লাইব্রেরি হল</p>
      <p><span class="tag">Next Program:</span> ইংরেজি বিতর্ক প্রতিযোগিতা</p>
    </div>

    <div class="leader-card">
      <h4>🎭 Cultural Club</h4>
      <p><span class="tag">Convenor:</span> জনাব দত্ত</p>
      <p><span class="tag">President:</span> রুবিনা আক্তার</p>
      <p><span class="tag">Meeting Time:</span> প্রতি বুধবার, ৪:০০ PM</p>
      <p><span class="tag">Place:</span> অডিটোরিয়াম</p>
      <p><span class="tag">Next Program:</span> নাটক প্রদর্শনী</p>
    </div>

    <div class="leader-card">
      <h4>⚽ Sports Club</h4>
      <p><span class="tag">Convenor:</span> কোচ আলম</p>
      <p><span class="tag">President:</span> মাহমুদুল হাসান</p>
      <p><span class="tag">Meeting Time:</span> প্রতি শুক্রবার, ৫:০০ PM</p>
      <p><span class="tag">Place:</span> খেলার মাঠ</p>
      <p><span class="tag">Next Program:</span> আন্তঃস্কুল ফুটবল টুর্নামেন্ট</p>
    </div>
  </div>
</section>

<style>
  /* Blue Theme Gen Z Style */
  #club-leaders {
    margin-top: 40px;
    padding: 25px;
    background: linear-gradient(135deg, #0077b6, #00b4d8);
    border-radius: 12px;
    color: #fff;
    font-family: 'Poppins', sans-serif;
  }

  #club-leaders .section-title {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 30px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 1px;
  }

  .leaders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
  }

  .leader-card {
    background: #ffffff;
    color: #222;
    border-radius: 10px;
    padding: 20px;
    text-align: left;
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .leader-card:hover {
    transform: translateY(-6px) scale(1.03);
    box-shadow: 0 12px 20px rgba(0,0,0,0.2);
  }

  .leader-card h4 {
    font-size: 1.3rem;
    margin-bottom: 12px;
    color: #0077b6;
    font-weight: 600;
  }

  .leader-card p {
    margin: 6px 0;
    font-size: 0.95rem;
    color: #333;
  }

  .tag {
    font-weight: 600;
    color: #0077b6;
    background: #caf0f8;
    padding: 3px 7px;
    border-radius: 5px;
  }
</style>





    </main>

   <?php
require_once 'footer.php';
?>