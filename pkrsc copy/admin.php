   <?php
require_once 'header.php';
?>
    <main>

       

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

   <?php
require_once 'footer.php';
?>