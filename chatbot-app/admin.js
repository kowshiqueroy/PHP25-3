// Fetch metrics, suggestions, low-performing intents
fetch('admin_api.php?action=dashboard')
  .then(r=>r.json())
  .then(data=>{
    const c = document.getElementById('content');
    c.innerHTML = `<h3>Low-Performing Intents</h3><pre>${JSON.stringify(data.low, null,2)}</pre>
                   <h3>User Suggestions</h3><pre>${JSON.stringify(data.suggestions,null,2)}</pre>`;
  });