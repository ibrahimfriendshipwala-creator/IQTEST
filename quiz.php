<?php
// quiz.php
require_once 'db.php';

// Fetch all questions
$stmt = $pdo->query("SELECT id, question, opt1, opt2, opt3, opt4 FROM questions ORDER BY id ASC");
$questions = $stmt->fetchAll();
$total = count($questions);

// optional name from query
$user_name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Take IQ Test</title>
<style>
/* internal CSS — polished look */
:root{--bg:#071025;--card:#071728;--accent:#8b5cf6;--muted:#93a6bf}
*{box-sizing:border-box;font-family:Inter,system-ui,Segoe UI,Roboto;}
body{margin:0;background:linear-gradient(180deg,#041223, #071025);color:#eaf2ff;min-height:100vh;padding:20px;display:flex;align-items:center;justify-content:center}
.wrapper{max-width:900px;width:100%;border-radius:16px;padding:20px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));box-shadow:0 10px 30px rgba(2,6,23,0.6)}
.header{display:flex;justify-content:space-between;align-items:center}
.hleft h2{margin:0}
.hright{color:var(--muted);font-size:13px}
.card{margin-top:14px;padding:18px;border-radius:12px;background:rgba(255,255,255,0.02)}
.question{font-size:18px;margin-bottom:14px}
.options{display:flex;flex-direction:column;gap:10px}
.option{padding:10px;border-radius:10px;background:transparent;border:1px solid rgba(255,255,255,0.04);cursor:pointer}
.option.selected{border-color:rgba(139,92,246,0.9);background:linear-gradient(90deg, rgba(139,92,246,0.06), rgba(6,182,212,0.03))}
.controls{display:flex;justify-content:space-between;align-items:center;margin-top:16px}
.btn{padding:10px 14px;border-radius:10px;border:none;background:linear-gradient(90deg,#8b5cf6,#06b6d4);color:white;font-weight:600;cursor:pointer}
.btn.secondary{background:transparent;border:1px solid rgba(255,255,255,0.05);color:var(--muted)}
.progress{font-size:13px;color:var(--muted)}
.bottom-note{margin-top:12px;font-size:13px;color:var(--muted)}
@media(max-width:720px){.header{flex-direction:column;align-items:flex-start;gap:10px}}
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="hleft">
      <h2>IQ Test</h2>
      <div class="progress">Questions: <span id="qcount">1</span> / <?php echo $total ?></div>
    </div>
    <div class="hright">Estimated time: <?php echo max(5, round($total * 0.8)); ?> minutes</div>
  </div>

  <div class="card" id="quizCard">
    <!-- Questions rendered into JS -->
    <div id="questionArea">
      Loading questions...
    </div>

    <div class="controls">
      <div>
        <button class="btn secondary" id="prevBtn" onclick="prevQ()">Previous</button>
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <div class="progress"><span id="progressText"></span></div>
        <button class="btn" id="nextBtn" onclick="nextQ()">Next</button>
      </div>
    </div>

    <div class="bottom-note">
      <small>Use navigation to change answers. When done, click Submit to calculate your IQ estimate.</small>
    </div>
  </div>

  <form id="submitForm" method="post" action="results.php" style="display:none">
    <!-- JS will populate hidden inputs for answers and name -->
    <input type="hidden" name="user_name" id="hiddenUserName" value="<?php echo $user_name ?>">
  </form>
</div>

<script>
// Questions data from PHP
const QUESTIONS = <?php echo json_encode($questions, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
let current = 0;
const total = QUESTIONS.length;
const answers = {}; // answers[id] = selectedOptionNumber (1..4)

// render function
function renderQuestion(index){
  const q = QUESTIONS[index];
  document.getElementById('qcount').textContent = index+1;
  const area = document.getElementById('questionArea');
  let html = `<div class="question"><strong>Q${index+1}.</strong> ${escapeHtml(q.question)}</div>`;
  html += '<div class="options">';
  for (let i=1;i<=4;i++){
    const opt = q['opt'+i];
    const selected = (answers[q.id] && answers[q.id] === i) ? ' selected' : '';
    html += `<div class="option${selected}" data-qid="${q.id}" data-opt="${i}" onclick="chooseOption(${q.id},${i}, this)"><strong>${String.fromCharCode(64+i)}.</strong> ${escapeHtml(opt)}</div>`;
  }
  html += '</div>';
  // Submit button on last
  html += `<div style="margin-top:14px;display:flex;justify-content:flex-end"><button class="btn" onclick="handleNext(event)">${index === total-1 ? 'Submit' : 'Next'}</button></div>`;
  area.innerHTML = html;
  document.getElementById('progressText').textContent = `Question ${index+1} of ${total}`;
  // enable/disable prev
  document.getElementById('prevBtn').disabled = (index === 0);
}

function chooseOption(qid, opt, el){
  answers[qid] = opt;
  // update UI: clear selected for this question
  const opts = document.querySelectorAll(`[data-qid="${qid}"]`);
  opts.forEach(o => { o.classList.remove('selected'); });
  el.classList.add('selected');
}

function nextQ(){
  if (current < total-1) { current++; renderQuestion(current); }
}

function prevQ(){
  if (current > 0) { current--; renderQuestion(current); }
}

function handleNext(e){
  e.preventDefault();
  if (current === total-1) {
    // submit: prepare hidden form inputs then redirect to results.php via JS by submitting form
    submitAnswers();
  } else {
    nextQ();
  }
}

function submitAnswers(){
  // Put answers into hidden form inputs
  const form = document.getElementById('submitForm');
  // clear previous extra inputs
  Array.from(form.querySelectorAll('input[name^="answers"]')).forEach(n => n.remove());
  for (const q of QUESTIONS){
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = `answers[${q.id}]`;
    input.value = answers[q.id] ? answers[q.id] : ''; // empty if unanswered
    form.appendChild(input);
  }
  // ensure name included
  const userNameInput = document.getElementById('hiddenUserName');
  if (!userNameInput.value) {
    // if blank, ask user to confirm anonymous
    if (!confirm('Submit without a name? (OK = anonymous, Cancel = enter name)')) {
      const name = prompt('Enter your name (optional):','');
      if (name !== null) userNameInput.value = name.trim();
      // if still empty proceed
    }
  }
  // Submit via JS (redirect + POST)
  form.submit();
}

// helpers
function escapeHtml(text){
  if (!text) return '';
  return text.replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];});
}

// initialize
renderQuestion(0);
</script>
</body>
</html>
