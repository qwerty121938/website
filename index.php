<?php
// ...existing code...
<?php
// 簡易 PHP Blog (深色系、可在本機或 PHP 主機上使用)
// - 文章儲存在 ./posts/*.md（如果沒有會自動建立）
// - 每篇檔案格式：第一行標題、第二行日期、第三行開始為內容
// - 後台管理：訪問 ?admin，提交表單可建立新文章（請先修改 $ADMIN_PASS）

$ADMIN_PASS = 'changeme'; // <<-- 請改成自己的密碼
$POSTS_DIR = __DIR__ . '/posts';

if (!is_dir($POSTS_DIR)) {
    mkdir($POSTS_DIR, 0755, true);
}

function slugify($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\-]+/u', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'post';
}

function list_posts($dir) {
    $files = glob($dir . '/*.md');
    usort($files, function($a, $b){ return filemtime($b) - filemtime($a); });
    $posts = [];
    foreach ($files as $f) {
        $posts[] = parse_post($f);
    }
    return $posts;
}

function parse_post($file) {
    $text = file($file, FILE_IGNORE_NEW_LINES);
    $title = isset($text[0]) ? $text[0] : '無標題';
    $date = isset($text[1]) ? $text[1] : date('Y-m-d H:i');
    $content = implode("\n", array_slice($text, 2));
    $slug = basename($file, '.md');
    return ['title'=>$title, 'date'=>$date, 'content'=>$content, 'slug'=>$slug];
}

function render_html($text) {
    // 簡單處理 Markdown 標題與換行
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = nl2br($text);
    return $text;
}

// 處理新增文章
$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $pass = $_POST['pass'] ?? '';
    if ($pass !== $ADMIN_PASS) {
        $messages[] = ['type'=>'error','text'=>'密碼錯誤'];
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($title === '' || $content === '') {
            $messages[] = ['type'=>'error','text'=>'標題與內容不可為空'];
        } else {
            $slug = date('YmdHis') . '-' . slugify($title);
            $filename = "$POSTS_DIR/$slug.md";
            $date = date('Y-m-d H:i');
            $body = $title . "\n" . $date . "\n" . $content;
            if (file_put_contents($filename, $body) !== false) {
                $messages[] = ['type'=>'success','text'=>'文章已建立'];
                header("Location: ?post=$slug");
                exit;
            } else {
                $messages[] = ['type'=>'error','text'=>'儲存失敗，請檢查目錄權限'];
            }
        }
    }
}

// 讀取單一文章或列表
$viewPost = $_GET['post'] ?? null;
$admin = isset($_GET['admin']);

$posts = list_posts($POSTS_DIR);
$single = $viewPost ? parse_post("$POSTS_DIR/$viewPost.md") : null;

?><!doctype html>
<html lang="zh-Hant">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>我的簡易心得部落格</title>
<style>
:root{
  --bg:#0f1720; --panel:#0b1220; --muted:#9aa4b2; --accent:#8ab4f8; --card:#0d1520;
  --radius:12px;
  color-scheme: dark;
}
*{box-sizing:border-box}
body{margin:0;font-family:Inter,system-ui,Segoe UI,Helvetica,Arial,'Noto Sans TC',sans-serif;background:linear-gradient(180deg,#071021 0%,#0b1220 100%);color:#e6eef8;min-height:100vh}
.container{max-width:900px;margin:36px auto;padding:24px}
.header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px}
.brand{display:flex;gap:12px;align-items:center}
.logo{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#1f2937,#0ea5a0);display:flex;align-items:center;justify-content:center;font-weight:700}
.title{font-size:1.25rem;font-weight:600}
.subtitle{color:var(--muted);font-size:0.9rem}
.card{background:var(--card);padding:18px;border-radius:var(--radius);box-shadow:0 6px 30px rgba(2,6,23,0.6)}
.post-list{display:flex;flex-direction:column;gap:12px}
.post-item{display:flex;flex-direction:column;padding:12px;border-radius:10px;background:linear-gradient(180deg,rgba(255,255,255,0.02),transparent);border:1px solid rgba(255,255,255,0.03)}
.post-item a{color:inherit;text-decoration:none}
.meta{color:var(--muted);font-size:0.85rem;margin-bottom:8px}
.controls{display:flex;gap:8px;align-items:center}
.btn{background:transparent;border:1px solid rgba(255,255,255,0.06);color:var(--accent);padding:8px 12px;border-radius:10px;cursor:pointer}
.btn.ghost{border-style:dashed;color:var(--muted)}
.form-row{display:flex;flex-direction:column;gap:8px;margin-bottom:12px}
.input,textarea{background:transparent;border:1px solid rgba(255,255,255,0.04);padding:10px;border-radius:8px;color:inherit}
textarea{min-height:160px;resize:vertical}
.notice{padding:10px;border-radius:8px;margin-bottom:12px}
.notice.error{background:#2a1414;color:#ffb4b4;border:1px solid rgba(255,80,80,0.12)}
.notice.success{background:#0b1a12;color:#baf2c5;border:1px solid rgba(80,255,140,0.08)}
.footer{margin-top:28px;color:var(--muted);font-size:0.9rem;text-align:center}
@media (max-width:600px){.container{padding:16px}}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="brand">
      <div class="logo">B</div>
      <div>
        <div class="title">我的心得部落格</div>
        <div class="subtitle">深色簡潔風格 — 寫下你的想法</div>
      </div>
    </div>
    <div class="controls">
      <a class="btn" href="./">文章列表</a>
      <a class="btn" href="?admin">撰寫新文章</a>
    </div>
  </div>

  <?php foreach ($messages as $m): ?>
    <div class="notice <?php echo $m['type']==='error'?'error':'success' ?>"><?php echo htmlspecialchars($m['text']); ?></div>
  <?php endforeach; ?>

  <div class="card">
    <?php if ($single): ?>
      <article>
        <h1 style="margin-top:0"><?php echo htmlspecialchars($single['title']); ?></h1>
        <div class="meta"><?php echo htmlspecialchars($single['date']); ?></div>
        <div class="content"><?php echo render_html($single['content']); ?></div>
        <div style="margin-top:18px"><a class="btn ghost" href="./">← 返回列表</a></div>
      </article>

    <?php elseif ($admin): ?>
      <h2 style="margin-top:0">撰寫新文章</h2>
      <form method="post" class="form">
        <input type="hidden" name="action" value="create">
        <div class="form-row">
          <label class="subtitle">管理者密碼</label>
          <input class="input" type="password" name="pass" required placeholder="輸入管理密碼">
        </div>
        <div class="form-row">
          <label class="subtitle">標題</label>
          <input class="input" type="text" name="title" required placeholder="文章標題">
        </div>
        <div class="form-row">
          <label class="subtitle">內容（支援簡單 Markdown 標題，使用換行分段）</label>
          <textarea name="content" required placeholder="在此撰寫你的心得"></textarea>
        </div>
        <div style="display:flex;gap:8px">
          <button class="btn" type="submit">發佈文章</button>
          <a class="btn ghost" href="./">取消</a>
        </div>
      </form>

    <?php else: ?>
      <h2 style="margin-top:0">最新文章</h2>
      <?php if (empty($posts)): ?>
        <p class="subtitle">目前沒有文章。點選「撰寫新文章」開始。</p>
      <?php else: ?>
        <div class="post-list">
          <?php foreach ($posts as $p): ?>
            <div class="post-item">
              <a href="?post=<?php echo urlencode($p['slug']); ?>">
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <strong><?php echo htmlspecialchars($p['title']); ?></strong>
                  <div class="meta"><?php echo htmlspecialchars($p['date']); ?></div>
                </div>
                <div class="meta" style="margin-top:6px;color:var(--muted)"><?php
                  // 顯示前 120 字作為預覽
                  $preview = mb_substr(strip_tags($p['content']), 0, 120, 'UTF-8');
                  echo htmlspecialchars($preview) . (mb_strlen($p['content'],'UTF-8')>120 ? '…':'');
                ?></div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="footer">小提示：文章會儲存在 <code>/posts</code> 目錄。請將此專案上傳到支援 PHP 的主機以執行。</div>
</div>