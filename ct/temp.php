<?php
require_once 'head.php';
?>
    <section>
      <h2>📋 Enhanced Form</h2>
      <div class="form-group">
        <label>Name</label>
        <input type="text" placeholder="Your Name" />
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" placeholder="Email Address" />
      </div>
      <div class="form-group">
        <label>Gender</label>
        <div class="radio-group">
          <label><input type="radio" name="gender" /> Male</label>
          <label><input type="radio" name="gender" /> Female</label>
          <label><input type="radio" name="gender" /> Other</label>
        </div>
      </div>
      <div class="form-group">
        <label>Interests</label>
        <div class="checkbox-group">
          <label><input type="checkbox" /> Coding</label>
          <label><input type="checkbox" /> Design</label>
          <label><input type="checkbox" /> Gaming</label>
        </div>
      </div>
      <div class="form-group">
        <label>Country</label>
        <select>
          <option>Bangladesh</option>
          <option>India</option>
          <option>USA</option>
          <option>UK</option>
        </select>
      </div>
      <button onclick="alert('Form submitted! 🚀')">Submit</button>
    </section>

    <section>
      <h2>📊 Demo Table</h2>
      <table>
        <tr><th>Name</th><th>Email</th></tr>
        <tr><td>🌈 Alice</td><td>alice@vibe.com</td></tr>
        <tr><td>🔥 Bob</td><td>bob@coolmail.com</td></tr>
      </table>
    </section>

     <section>
      <h2>📊 Demo Table 2</h2>
      <table>
        <tr><th>Name</th><th>Email</th> <th>Name</th><th>Email</th></tr>
        <tr><td>🌈 Alice</td><td>alice@vibe.com</td><td>🌈 Alice</td><td>alice@vibe.com</td></tr>
        <tr><td>🔥 Bob</td><td>bob@coolmail.com</td><td>🔥 Bob</td><td>bob@coolmail.com</td></tr>
      </table>
    </section>

    <section>
      <h2>🧩 Quick Actions</h2>
      <div class="grid">
        <div class="grid-item" onclick="alert('Profile clicked')">
          <i>👤</i>Profile
        </div>
        <div class="grid-item" onclick="alert('Settings clicked')">
          <i>⚙️</i>Settings
        </div>
        <div class="grid-item" onclick="alert('Messages clicked')">
          <i>💬</i>Messages
        </div>
        <div class="grid-item" onclick="alert('Help clicked')">
          <i>🆘</i>Help
        </div>
      </div>
    </section>
<?php
require_once 'foot.php';
?>