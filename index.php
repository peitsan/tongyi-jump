<?php
const MAX_QUERY_LENGTH = 500;

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isWechat = strpos($ua, 'MicroMessenger') !== false;

$q = $_GET['q'] ?? '';
$q = mb_substr($q, 0, MAX_QUERY_LENGTH, 'UTF-8');
$encoded = rawurlencode($q);
$deepLinkPath = 'page/chat?tab=mainChat&inputText=' . $encoded;

$dest = 'tongyi://' . $deepLinkPath;
$intent = 'intent://' . $deepLinkPath . '#Intent;scheme=tongyi;end';
$fallback = 'https://m.tongyi.com/app/tongyi/tongyi-hybrid/download-guide';
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>正在打开通义千问...</title>
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
    <p class="hint"><?php echo $isWechat ? '检测到微信环境，正在尝试拉起系统浏览器...' : '正在打开通义千问...'; ?></p>
    <?php if ($isWechat): ?>
      <a class="btn" href="<?php echo htmlspecialchars($intent, ENT_QUOTES, 'UTF-8'); ?>">在系统浏览器中打开</a>
    <?php endif; ?>
  </div>
  <script>
    const isWechat = <?php echo $isWechat ? 'true' : 'false'; ?>;
    const dest = <?php echo json_encode($dest, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const intent = <?php echo json_encode($intent, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const fallback = <?php echo json_encode($fallback, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const FALLBACK_TIMEOUT_MS = 2000;

    const fallbackTimer = setTimeout(() => {
      window.location.href = fallback;
    }, FALLBACK_TIMEOUT_MS);

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        clearTimeout(fallbackTimer);
      }
    });

    window.location.href = isWechat ? intent : dest;
  </script>
</body>
</html>
