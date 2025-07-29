<?php
// chat.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ensure a thread_id is set
if (!isset($_SESSION['thread_id'])) {
    $_SESSION['thread_id'] = bin2hex(random_bytes(16));
}

// Load past conversation for this thread
$stmt = $pdo->prepare("
    SELECT id, message, response
    FROM conversations
    WHERE thread_id = ? AND user_id = ?
    ORDER BY created_at ASC
");
$stmt->execute([ $_SESSION['thread_id'], $_SESSION['user_id'] ]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chatbot</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 600px;
            margin: 2rem auto;
            display: flex;
            flex-direction: column;
        }
        #controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        #tone-selector {
            padding: 0.3rem;
        }
        #new-topic {
            padding: 0.3rem 0.6rem;
        }
        #messages {
            border: 1px solid #ccc;
            padding: 1rem;
            height: 400px;
            overflow-y: auto;
            background: #f9f9f9;
        }
        .msg {
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        .user {
            text-align: right;
            color: #0044cc;
        }
        .bot {
            text-align: left;
            color: #006600;
        }
        .feedback {
            text-align: left;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
        }
        .feedback button {
            margin-right: 0.3rem;
        }
        #chat-form {
            display: flex;
            margin-top: 0.5rem;
        }
        #user-input {
            flex: 1;
            padding: 0.5rem;
        }
        #chat-form button {
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>

  <div id="controls">
    <select id="tone-selector">
      <option value="">Default Tone</option>
      <option value="friendly">Friendly</option>
      <option value="formal">Formal</option>
      <option value="educator">Educator</option>
    </select>
    <button id="new-topic">Start New Topic</button>
  </div>

  <div id="messages">
    <?php foreach ($history as $row): ?>
      <div class="msg user"><?php echo htmlspecialchars($row['message']); ?></div>
      <div class="msg bot"><?php echo nl2br(htmlspecialchars($row['response'])); ?></div>
    <?php endforeach; ?>
  </div>

  <form id="chat-form">
    <input type="text" id="user-input" placeholder="Type your message…" autocomplete="off" required>
    <button type="submit">Send</button>
  </form>

  <script>
async function sendMessage(text, tone = '') {
  console.log('→ sending to chat_handler:', { text, tone });

  const res = await fetch('chat_handler.php', {
    method: 'POST',
    headers: { 'Content-Type':'application/json' },
    body: JSON.stringify({ message: text, tone: tone })
  });

  console.log('← status:', res.status);
  const payload = await res.json().catch(e => {
    console.error('Invalid JSON:', e, res);
    return null;
  });
  console.log('← payload:', payload);

  return payload;
}

// Example usage
sendMessage('hello').then(resp => {
  if (resp) {
    document.body.insertAdjacentHTML('beforeend',
      `<pre>BOT replied: ${resp.response}</pre>`);
  }
});




    let lastConvId = null;

    function appendMessage(who, text, convId = null) {
      const msgDiv = document.createElement('div');
      msgDiv.classList.add('msg', who);
      msgDiv.textContent = text;
      document.getElementById('messages').appendChild(msgDiv);

      if (who === 'bot' && convId) {
        lastConvId = convId;
        const fb = document.createElement('div');
        fb.classList.add('feedback');
        fb.innerHTML = `
          <button data-helpful="1">👍</button>
          <button data-helpful="0">👎</button>
          <div class="suggestion" style="display:none; margin-top:0.5rem;">
            <input type="text" placeholder="Your suggestion…" />
            <button class="submit-suggestion">Submit</button>
          </div>
        `;
        document.getElementById('messages').appendChild(fb);

        fb.querySelectorAll('button[data-helpful]').forEach(btn => {
          btn.addEventListener('click', () => {
            const helpful = btn.getAttribute('data-helpful');
            if (helpful === '0') {
              fb.querySelector('.suggestion').style.display = 'block';
            } else {
              sendFeedback(lastConvId, 1, null);
              fb.remove();
            }
          });
        });

        fb.querySelector('.submit-suggestion')
          .addEventListener('click', () => {
            const val = fb.querySelector('.suggestion input').value.trim();
            if (!val) return;
            sendFeedback(lastConvId, 0, val);
            fb.remove();
          });
      }

      // scroll to bottom
      document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
    }

    async function sendFeedback(convId, helpful, suggestion) {
      await fetch('feedback_handler.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ conversationId: convId, helpful, suggestion })
      });
    }

    document.getElementById('chat-form').addEventListener('submit', async e => {
      e.preventDefault();
      const inputEl = document.getElementById('user-input');
      const text = inputEl.value.trim();
      if (!text) return;
      appendMessage('user', text);
      inputEl.value = '';

      const tone = document.getElementById('tone-selector').value;

      const res = await fetch('chat_handler.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ message: text, tone })
      });
      const data = await res.json();
      appendMessage('bot', data.response, data.conversationId);
    });

    document.getElementById('new-topic').addEventListener('click', async () => {
      await fetch('reset_thread.php', { method: 'POST' });
      document.getElementById('messages').innerHTML = '';
    });
  </script>

</body>
</html>