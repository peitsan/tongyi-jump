<?php
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isWechat = strpos($ua, 'MicroMessenger') !== false;

$q = $_GET['q'] ?? '';
$encoded = rawurlencode($q);

$dest = 'tongyi://page/chat?tab=mainChat&inputText=' . $encoded;
$intent = 'intent://page/chat?tab=mainChat&inputText=' . $encoded . '#Intent;scheme=tongyi;end';
$fallback = 'https://m.tongyi.com/app/tongyi/tongyi-hybrid/download-guide';
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>正在打开千问...</title>
  <style>
    body { font-family: -apple-system, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f5f5f5; }
    .box { text-align: center; padding: 32px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: min(90vw, 420px); }
    .loader { width: 40px; height: 40px; border: 3px solid #eee; border-top: 3px solid #1677ff; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px; }
    .hint { color: #666; margin: 0 0 16px; }
    .btn { display: inline-block; padding: 10px 14px; border-radius: 10px; background: #1677ff; color: #fff; text-decoration: none; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div class="box">
    <div class="loader"></div>
    <p class="hint"><?php echo $isWechat ? '检测到微信环境，正在尝试拉起系统浏览器...' : '正在打开千问...'; ?></p>
    <?php if ($isWechat): ?>
      <a class="btn" href="<?php echo htmlspecialchars($intent, ENT_QUOTES, 'UTF-8'); ?>">在系统浏览器中打开</a>
    <?php endif; ?>
  </div>
  <script>
    const isWechat = <?php echo $isWechat ? 'true' : 'false'; ?>;
    const dest = '<?php echo htmlspecialchars($dest, ENT_QUOTES, 'UTF-8'); ?>';
    const intent = '<?php echo htmlspecialchars($intent, ENT_QUOTES, 'UTF-8'); ?>';
    const fallback = '<?php echo htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8'); ?>';

    const fallbackTimer = setTimeout(() => {
      window.location.href = fallback;
    }, 2000);

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        clearTimeout(fallbackTimer);
      }
    });

    window.location.href = isWechat ? intent : dest;
  </script>
</body>
</html>
