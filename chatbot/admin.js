// admin.js

// Utility to fetch JSON
async function fetchJSON(url, opts = {}) {
  const res = await fetch(url, opts);
  return await res.json();
}

// admin.js
async function loadModelMetrics() {
  const data = await fetchJSON('admin_api.php?action=modelMetrics');
  // data: { lastTrained, vocabSize, ... }
  // render in a new <div id="model-metrics">
}
// 1) Load low-performing intents
async function loadLowPerformers() {
  const data = await fetchJSON('admin_api.php?action=lowPerformers');
  let html = '<h2>Low-Performing Intents</h2><table>';
  html += '<tr><th>Intent</th><th>Negative%</th><th>Actions</th></tr>';
  data.forEach(row => {
    html += `<tr>
      <td>${row.name}</td>
      <td>${(row.ratio*100).toFixed(1)}%</td>
      <td>
        <button onclick="editIntent(${row.intent_id})">Edit</button>
      </td>
    </tr>`;
  });
  html += '</table>';
  document.getElementById('low-performers').innerHTML = html;
}

// 2) Load user suggestions
async function loadSuggestions() {
  const data = await fetchJSON('admin_api.php?action=suggestions');
  let html = '<h2>User Suggestions</h2><table>';
  html += '<tr><th>When</th><th>Conv ID</th><th>Intent</th><th>Suggestion</th><th>Action</th></tr>';
  data.forEach(s => {
    html += `<tr>
      <td>${s.created_at}</td>
      <td>${s.conversation_id}</td>
      <td>${s.intent_id}</td>
      <td>${s.suggestion}</td>
      <td>
        <button onclick="applySuggestion(${s.feedback_id}, ${s.intent_id}, \`${s.suggestion.replace(/`/g,'\\`')}\`)">
          Approve
        </button>
      </td>
    </tr>`;
  });
  html += '</table>';
  document.getElementById('suggestions').innerHTML = html;
}

// 3) Load all intents for full management
async function loadIntents() {
  const data = await fetchJSON('admin_api.php?action=intents');
  let html = '<h2>Manage Intents</h2><table>';
  html += '<tr><th>ID</th><th>Name</th><th>Default Response</th><th>Tone</th><th>Emotion</th><th>Patterns</th><th>Save</th></tr>';
  data.forEach(i => {
    const patterns = i.patterns.join(', ');
    html += `<tr>
      <td>${i.id}</td>
      <td><input id="name_${i.id}" value="${i.name}"></td>
      <td><input id="resp_${i.id}" value="${i.default_response}" style="width:100%;"></td>
      <td>
        <select id="tone_${i.id}">
          <option${i.tone_tag==='friendly'?' selected':''}>friendly</option>
          <option${i.tone_tag==='formal'?' selected':''}>formal</option>
          <option${i.tone_tag==='educator'?' selected':''}>educator</option>
        </select>
      </td>
      <td><input id="emo_${i.id}" value="${i.emotion_tag||''}"></td>
      <td>${patterns}</td>
      <td><button onclick="saveIntent(${i.id})">Save</button></td>
    </tr>`;
  });
  html += '</table>';
  document.getElementById('intents-management').innerHTML = html;
}

// Action handlers
function applySuggestion(feedbackId, intentId, suggestion) {
  fetch('admin_api.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'applySuggestion', feedbackId, intentId, suggestion})
  }).then(loadSuggestions).then(loadLowPerformers).then(loadIntents);
}

function saveIntent(id) {
  const name = document.getElementById(`name_${id}`).value;
  const resp = document.getElementById(`resp_${id}`).value;
  const tone = document.getElementById(`tone_${id}`).value;
  const emo  = document.getElementById(`emo_${id}`).value;
  fetch('admin_api.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'saveIntent', id, name, resp, tone, emo})
  }).then(loadIntents).then(loadLowPerformers);
}

// Initialize
loadLowPerformers();
loadSuggestions();
loadIntents();