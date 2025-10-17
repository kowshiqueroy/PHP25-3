<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CivicThinkers Survey and Earn</title>
  <link rel="icon" href="logo.png" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Pacifico&display=swap" rel="stylesheet">
 <style>
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Inter', sans-serif;
  }

  body {
    background: linear-gradient(135deg, #ffe29f, #ffa99f);
    color: #333;
    padding: 20px;
  }

  header {
    text-align: center;
    margin-bottom: 30px;
  }

  header img {
    width: 70px;
    height: 70px;
    margin-bottom: 10px;
  }

  header h1 {
    font-family: 'Pacifico', cursive;
    font-size: 2.2rem;
    color: #fff;
    text-shadow: 1px 1px 2px #00000030;
  }

  header p {
    font-size: 1rem;
    color: #fff;
    font-weight: 600;
  }

  .card-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 40px;
  }

  .card {
    background: rgba(255, 255, 255, 0.85);
    border-radius: 16px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    padding: 20px;
    text-align: center;
    position: relative;
    backdrop-filter: blur(6px);
  }

  .card h2 {
    font-size: 1.4rem;
    color: #f8b500;
    margin-bottom: 10px;
  }

  .card p {
    font-size: 1rem;
    color: #444;
    margin-bottom: 6px;
  }

  .card::before {
    content: "🎯";
    position: absolute;
    top: -12px;
    right: -12px;
    background: #f8b500;
    color: #fff;
    font-size: 1.2rem;
    padding: 6px 10px;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  }

  .registration-form {
    background: #fff;
    max-width: 100%;
    margin: 0 auto;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
  }

  .registration-form h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #f8b500;
    font-size: 1.6rem;
  }

  .form-group {
    margin-bottom: 18px;
  }

  .form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
  }

  .form-group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #f8b500;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
  }

  .form-group input:focus {
    border-color: #e09e00;
    outline: none;
  }

  button {
    width: 100%;
    padding: 14px;
    background: linear-gradient(to right, #f8b500, #ff9900);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.3s ease;
  }

  button:hover {
    transform: scale(1.03);
  }

  @media (min-width: 768px) {
    .card-container {
      flex-direction: row;
      flex-wrap: wrap;
      justify-content: center;
    }

    .card {
      width: 260px;
    }

    .registration-form {
      max-width: 400px;
    }
  }
</style>

<body>
  <header>
    <img src="logo.png" alt="CivicThinkers Logo" />
    <h1>CivicThinkers</h1>
    <p>📋 Survey and Earn 💰</p>
  </header>

  <div class="card-container">
    <div class="card">
      <h2>🥉 S1 Tier</h2>
      <p>🔓 1000 coins to Join</p>
      <p>💸 Earn up to 50/day</p>
    </div>
    <div class="card">
      <h2>🥈 S2 Tier</h2>
      <p>🔓 3000 coins to Join</p>
      <p>💸 Earn up to 100/day</p>
    </div>
    <div class="card">
      <h2>🥇 S3 Tier</h2>
      <p>🔓 5000 coins to Join</p>
      <p>💸 Earn up to 200/day</p>
    </div>
    <div class="card">
      <h2>🏆 S4 Tier</h2>
      <p>🔓 10000 coins to Join</p>
      <p>💸 Earn up to 500/day</p>
    </div>
  </div>

  <form class="registration-form" action="register.php" method="POST">
    <h2>🚀 Register Now</h2>
    <div class="form-group">
      <label for="phone">📱 Phone (11 digits)</label>
      <input type="text" id="phone" name="phone" pattern="^01\\d{9}$" maxlength="11" required />
    </div>
    <div class="form-group">
      <label for="password">🔐 Password</label>
      <input type="password" id="password" name="password" required />
    </div>
    <button type="submit">✅ Join & Start Earning</button>
  </form>
</body>
