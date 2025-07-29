<?php
require_once __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
$pdo = getPDO();
// Load last 50 messages in this thread
$stmt = $pdo->prepare("SELECT * FROM conversations WHERE thread_id=? ORDER BY created_at ASC LIMIT 50");
$stmt->execute([$_SESSION['thread_id']]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Chatbot</title>
  <style>
    body { font-family:sans-serif; max-width:600px;margin:0 auto;}
    #messages{border:1px solid #ccc;padding:10px;height:400px;overflow-y:scroll;}
    .user{color:blue;margin:5px;}
    .bot{color:green;margin:5px;}
    #feedback{display:none;margin-top:5px;}
  </style>
</head>
<body>
  <h2>Chat with Bot</h2>
  <button onclick="window.location='reset_thread.php'">Start New Topic</button>
  <button onclick="window.location='logout.php'">Logout</button>

  <div id="messages">
    <?php foreach ($history as $m): ?>
      <div class="<?= $m['message'] ? 'user' : 'bot' ?>">
        <?= htmlspecialchars($m['message'] ?: $m['response']) ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div>
    <select id="tone">
      <option value="default">Default</option>
      <option value="friendly">Friendly</option>
      <option value="formal">Formal</option>
      <option value="educator">Educator</option>
    </select>
    <input id="text" autocomplete="off">
    <button id="send">Send</button>
  </div>

  <div id="feedback">
    <button id="thumbs-up">👍</button>
    <button id="thumbs-down">👎</button>
    <div id="suggestion-box" style="display:none;">
      <textarea id="suggestion" placeholder="Your suggestion"></textarea>
      <button id="submit-sug">Submit</button>
    </div>
  </div>

  <script>
  let lastConvId = null;
  document.getElementById('send').onclick = async ()=>{
    const msg = document.getElementById('text').value.trim();
    if (!msg) return;
    const tone = document.getElementById('tone').value;
    // Append user message
    const pm = document.createElement('div'); pm.className='user'; pm.textContent=msg;
    document.getElementById('messages').append(pm);
    document.getElementById('text').value='';
    // Call handler
    const res = await fetch('chat_handler.php',{
      method:'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({message:msg,tone})
    });
    const j = await res.json();
    lastConvId = j.conversationId;
    // Append bot response
    const bm = document.createElement('div'); bm.className='bot'; bm.textContent=j.response;
    document.getElementById('messages').append(bm);
    // Show feedback UI
    document.getElementById('feedback').style.display='block';
  };

  document.getElementById('thumbs-up').onclick = ()=>sendFeedback(1);
  document.getElementById('thumbs-down').onclick = ()=>{
    document.getElementById('suggestion-box').style.display='block';
    sendFeedback(0,false);
  };
  document.getElementById('submit-sug').onclick = ()=>sendFeedback(0,true);

  async function sendFeedback(helpful, withSug=true){
    const suggestion = withSug? document.getElementById('suggestion').value : '';
    await fetch('feedback_handler.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({conversationId:lastConvId,helpful,suggestion})
    });
    document.getElementById('feedback').style.display='none';
    document.getElementById('suggestion-box').style.display='none';
  }
  </script>
</body>
</html>