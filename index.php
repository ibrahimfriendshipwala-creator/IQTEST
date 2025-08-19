<?php
// index.php
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>IQ Test — Home</title>
<style>
/* Internal CSS — clean, modern, responsive */
:root{
  --bg:#0f1724; --card:#0b1220; --accent:#4f46e5; --muted:#94a3b8; --glass: rgba(255,255,255,0.03);
}
*{box-sizing:border-box;font-family:Inter,ui-sans-serif,system-ui,Segoe UI,Roboto,"Helvetica Neue",Arial;}
body{margin:0;background:linear-gradient(180deg,#071029 0%,#0b1220 100%);color:#e6eef8;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
.container{max-width:960px;width:100%;padding:28px;border-radius:18px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));box-shadow:0 8px 30px rgba(2,6,23,0.6);}
.header{display:flex;align-items:center;gap:18px;}
.logo{
  width:72px;height:72px;border-radius:12px;background:linear-gradient(135deg,var(--accent),#06b6d4);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;color:white;
  box-shadow:0 6px 20px rgba(79,70,229,0.2);
}
.title{font-size:20px;margin:0}
.lead{color:var(--muted);margin-top:6px}
.grid{display:grid;grid-template-columns:1fr 320px;gap:20px;margin-top:20px}
.card{background:var(--card);padding:18px;border-radius:12px;box-shadow:0 8px 30px rgba(2,6,23,0.6)}
.actions{display:flex;flex-direction:column;gap:12px;align-items:stretch}
.btn{background:linear-gradient(90deg,var(--accent),#06b6d4);border:none;padding:12px 16px;border-radius:10px;color:white;font-weight:600;cursor:pointer;box-shadow:0 8px 20px rgba(79,70,229,0.18)}
.btn.secondary{background:transparent;border:1px solid rgba(255,255,255,0.06);color:var(--muted);font-weight:600}
.small{font-size:13px;color:var(--muted)}
.footer{margin-top:16px;color:var(--muted);font-size:13px;text-align:center}
@media(max-width:880px){.grid{grid-template-columns:1fr;}.logo{width:56px;height:56px}}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="logo">IQ</div>
    <div>
      <h1 class="title">Online IQ Test <small style="display:block;font-weight:600;color:#c7d2fe;font-size:14px">Measure reasoning, patterns & problem solving</small></h1>
      <div class="lead">A quick ~10-question test to estimate cognitive strengths. Results show your score, estimated IQ range and personalized tips.</div>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <h3 style="margin-top:0">About this test</h3>
      <p class="small">This is a short IQ-style assessment built for practice and entertainment. It includes logical reasoning, pattern recognition and numerical problems. Results provide a basic estimate — not a clinical diagnosis.</p>
      <hr style="border:none;border-top:1px solid rgba(255,255,255,0.03);margin:16px 0">
      <ul class="small" style="padding-left:18px">
        <li>~10 adaptive-style questions (fixed sample here).</li>
        <li>Time yourself if you want — test isn't strictly timed.</li>
        <li>Share your result or retake anytime.</li>
      </ul>
      <div style="margin-top:14px">
        <button class="btn" id="startBtn">Start Test</button>
        <button class="btn secondary" onclick="location.href='quiz.php'">Take Practice Now</button>
      </div>
    </div>

    <div class="card">
      <h3 style="margin-top:0">Ready?</h3>
      <p class="small">Click start and you'll be taken to the test page. Use the Next / Previous controls to navigate. At the end, submit to get your IQ estimate and tips.</p>

      <div style="margin-top:16px" class="actions">
        <input id="nameInput" placeholder="Enter your name (optional)" style="padding:10px;border-radius:8px;background:var(--glass);border:1px solid rgba(255,255,255,0.03);color:inherit">
        <div style="display:flex;gap:8px">
          <button class="btn" id="startWithName">Start with name</button>
        </div>
      </div>

      <div class="footer">
        DB: <code>dbxutryicovexp</code> • All files contain internal CSS & JS • Redirection via JS
      </div>
    </div>
  </div>
</div>

<script>
// JS redirection instructions (no PHP header redirects)
document.getElementById('startBtn').addEventListener('click', function(){
  // go to quiz.php
  window.location.href = 'quiz.php';
});
document.getElementById('startWithName').addEventListener('click', function(){
  var name = document.getElementById('nameInput').value.trim();
  // pass name via query string
  var target = 'quiz.php' + (name ? '?name=' + encodeURIComponent(name) : '');
  window.location.href = target;
});
</script>
</body>
</html>
