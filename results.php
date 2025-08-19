<?php
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // redirect back to quiz using JS-friendly redirect
    echo "<script>window.location.href='quiz.php';</script>";
    exit;
}

// collect answers
$answers = isset($_POST['answers']) && is_array($_POST['answers']) ? $_POST['answers'] : [];
$user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : null;

// fetch correct answers for provided question ids
$ids = array_map('intval', array_keys($answers));
if (empty($ids)) {
    $msg = "No answers received. Please take the test.";
    echo "<p>{$msg}</p><p><a href='quiz.php'>Back to Quiz</a></p>";
    exit;
}

// prepare placeholders
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, correct_option FROM questions WHERE id IN ($placeholders)");
$stmt->execute($ids);
$corrects = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // id => correct_option

$total_questions = count($ids);
$correct_count = 0;
foreach ($ids as $id) {
    $given = isset($answers[$id]) && $answers[$id] !== '' ? intval($answers[$id]) : 0;
    if ($given && isset($corrects[$id]) && intval($corrects[$id]) === $given) $correct_count++;
}

// percentage
$percentage = ($total_questions>0) ? ($correct_count / $total_questions) * 100 : 0;

// Map percentage to IQ (simple mapping): 70..130
$iq_score = round(70 + ($percentage/100) * 60); // 0% -> 70, 100% -> 130

// Build feedback
if ($percentage >= 90) {
    $level = "Very High";
    $advice = "Excellent performance — strong reasoning and pattern recognition. Keep practicing puzzle activities and timed problems to sharpen further.";
} else if ($percentage >= 75) {
    $level = "High";
    $advice = "Very good. You show strong problem-solving skills. Try more diverse logic puzzles and speed drills for improvement.";
} else if ($percentage >= 50) {
    $level = "Average";
    $advice = "Average performance. Focus on pattern recognition and basic numerical practice. Brain-training exercises and deliberate practice can help.";
} else {
    $level = "Below Average";
    $advice = "Consider regular practice with logical puzzles, sequence problems and mental math. Breaking problems into smaller steps will help.";
}

// save to results table
try {
    $ins = $pdo->prepare("INSERT INTO results (user_name, score, total_questions, percentage, iq_score, feedback) VALUES (?, ?, ?, ?, ?, ?)");
    $ins->execute([$user_name ?: null, $correct_count, $total_questions, round($percentage,2), $iq_score, $level . " - " . $advice]);
} catch (Exception $e) {
    // continue even if saving fails
}

// show result page
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Your IQ Result</title>
<style>
:root{--bg:#071029;--card:#071728;--accent:#06b6d4;--accent2:#8b5cf6;--muted:#9fb0c8}
*{box-sizing:border-box;font-family:Inter,system-ui,Segoe UI,Roboto;}
body{margin:0;background:linear-gradient(180deg,#041423,#071025);color:#eaf4ff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.container{max-width:880px;width:100%;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));padding:20px;border-radius:16px;box-shadow:0 10px 30px rgba(2,6,23,0.6)}
.header{display:flex;align-items:center;justify-content:space-between}
.title{margin:0}
.card{margin-top:16px;padding:18px;border-radius:12px;background:rgba(255,255,255,0.02)}
.kpi{display:flex;gap:18px;flex-wrap:wrap}
.kpi .item{background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.00));padding:12px;border-radius:10px;min-width:160px;text-align:center}
.big{font-size:28px;font-weight:700}
.small{color:var(--muted);font-size:13px}
.actions{margin-top:14px;display:flex;gap:10px}
.btn{padding:10px 14px;border-radius:10px;border:none;background:linear-gradient(90deg,var(--accent2),var(--accent));color:white;font-weight:600;cursor:pointer}
.btn.secondary{background:transparent;border:1px solid rgba(255,255,255,0.06);color:var(--muted)}
.feedback{margin-top:12px;color:#dff6ff;padding:12px;border-radius:10px;background:linear-gradient(90deg, rgba(6,182,212,0.05), rgba(139,92,246,0.03))}
@media(max-width:720px){.kpi{flex-direction:column}}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h2 class="title">Test Result</h2>
    <div class="small">Taken: <?php echo date('Y-m-d H:i:s'); ?></div>
  </div>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap">
      <div>
        <div class="small">Name</div>
        <div style="font-weight:700;font-size:18px;"><?php echo htmlspecialchars($user_name ?: 'Anonymous'); ?></div>
      </div>
      <div>
        <div class="small">Correct</div>
        <div class="big"><?php echo "{$correct_count} / {$total_questions}"; ?></div>
      </div>
      <div>
        <div class="small">Percentage</div>
        <div class="big"><?php echo round($percentage,2); ?>%</div>
      </div>
      <div>
        <div class="small">Estimated IQ</div>
        <div class="big"><?php echo $iq_score; ?></div>
      </div>
    </div>

    <div class="feedback">
      <strong>Level:</strong> <?php echo $level; ?><br>
      <strong>Feedback:</strong> <?php echo $advice; ?>
    </div>

    <div class="actions">
      <button class="btn" onclick="window.location.href='quiz.php'">Retake Test</button>
      <button class="btn secondary" onclick="shareResult()">Share Result</button>
      <button class="btn secondary" onclick="window.location.href='index.php'">Home</button>
    </div>

    <div style="margin-top:12px;color:var(--muted);font-size:13px">
      <strong>Tip:</strong> For improved results, time yourself and practice pattern and logical reasoning puzzles daily.
    </div>
  </div>
</div>

<script>
function shareResult(){
  const text = `I scored <?php echo $iq_score; ?> IQ (<?php echo round($percentage,2); ?>%) on this IQ test! Try it: ` + window.location.origin + window.location.pathname.replace('results.php','quiz.php');
  if (navigator.share) {
    navigator.share({ title: 'My IQ result', text }).catch(()=>{ alert('Share cancelled'); });
  } else {
    // fallback: copy to clipboard
    navigator.clipboard.writeText(text).then(()=> {
      alert('Result copied to clipboard. Share it anywhere!');
    }, ()=> {
      prompt('Copy this text to share:', text);
    });
  }
}
</script>
</body>
</html>
