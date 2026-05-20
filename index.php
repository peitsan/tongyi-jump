<?php
const MAX_QUERY_LENGTH = 500;
const MAX_INTENT_PROMPT_LENGTH = 180;
const INTENT_PREFIX = 'intent://page/chat?tab=mainChat&inputText=';

function sanitize_prompt(string $value): string
{
    $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value) ?? '';
    $value = trim($value);
    return mb_substr($value, 0, MAX_QUERY_LENGTH, 'UTF-8');
}

function get_request_prompt(): string
{
    foreach (['q', 'url', 'intent', 'target'] as $key) {
        if (!array_key_exists($key, $_GET) || is_array($_GET[$key])) {
            continue;
        }

        return sanitize_prompt((string) $_GET[$key]);
    }

    return '';
}

function build_intent_url(string $prompt): string
{
    $prompt = mb_substr($prompt, 0, MAX_INTENT_PROMPT_LENGTH, 'UTF-8');
    if ($prompt === '') {
        return '';
    }

    return INTENT_PREFIX . rawurlencode($prompt) . '#Intent;scheme=tongyi;end';
}

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isWechat = strpos($ua, 'MicroMessenger') !== false;

$q = get_request_prompt();
$encoded = rawurlencode($q);
$intent = build_intent_url($q);
$fallback = $q === '' ? 'https://peitsan.github.io/tongyi-jump/' : 'https://peitsan.github.io/tongyi-jump/?q=' . $encoded;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>正在打开千问...</title>
  <style>
    :root { color-scheme: light; }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; min-height: 100vh; background: #f5f5f5; }
    .center { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 24px; box-sizing: border-box; }
    .box { text-align: center; padding: 40px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); max-width: 92vw; width: 420px; box-sizing: border-box; }
    .loader { width: 40px; height: 40px; border: 3px solid #eee; border-top: 3px solid #1677ff; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    p { color: #666; margin: 0; line-height: 1.6; }
    .actions { margin-top: 16px; display: none; }
    .link-btn { display: inline-block; padding: 10px 14px; border-radius: 10px; background: #1677ff; color: #fff; text-decoration: none; word-break: break-all; }
    .mask { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; padding: 24px; box-sizing: border-box; background: rgba(0, 0, 0, 0.68); z-index: 10; }
    .mask-panel { width: min(100%, 420px); background: #fff; border-radius: 20px; padding: 24px; box-sizing: border-box; text-align: left; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25); }
    .mask-badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #eef4ff; color: #1677ff; font-size: 12px; margin-bottom: 12px; }
    .mask h1 { font-size: 20px; margin: 0 0 10px; }
    .mask p { color: #444; margin: 0 0 14px; }
    .mask ol { margin: 0 0 18px 20px; color: #444; line-height: 1.8; padding: 0; }
    .mask .link-btn { width: 100%; text-align: center; box-sizing: border-box; }
  </style>
</head>
<body>
  <div class="mask" id="wechat-mask">
    <div class="mask-panel">
      <div class="mask-badge">微信内打开提示</div>
      <h1>请从外部浏览器打开</h1>
      <p>请点击右上角“...”，选择“在浏览器中打开”，之后会自动跳转并填充输入内容。</p>
      <ol>
        <li>点击右上角“...”</li>
        <li>选择“在浏览器中打开”</li>
        <li>返回后将自动唤起千问 App</li>
      </ol>
      <p>本页会在当前浏览器缓存输入内容，方便重复打开。</p>
      <a class="link-btn" id="open-link" href="#" target="_blank" rel="noopener noreferrer">在系统浏览器中打开</a>
    </div>
  </div>
  <div class="center">
    <div class="box" id="loading-box">
      <div class="loader"></div>
      <p id="status-text"><?php echo $q !== '' ? '正在打开千问...' : '未识别到可直接跳转的输入内容，请检查链接参数。'; ?></p>
      <div class="actions" id="actions">
        <a class="link-btn" id="fallback-link" href="#" target="_blank" rel="noopener noreferrer" aria-label="在新标签页打开还原后的链接">打开还原后的链接</a>
      </div>
    </div>
  </div>
  <script>
    const isWechat = <?php echo $isWechat ? 'true' : 'false'; ?>;
    const intentUrl = <?php echo json_encode($intent, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const fallback = <?php echo json_encode($fallback, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const statusText = document.getElementById('status-text');
    const actions = document.getElementById('actions');
    const fallbackLink = document.getElementById('fallback-link');
    const loadingBox = document.getElementById('loading-box');
    const wechatMask = document.getElementById('wechat-mask');
    const openLink = document.getElementById('open-link');

    if (isWechat) {
      wechatMask.style.display = 'flex';
      loadingBox.style.filter = 'blur(2px)';
      loadingBox.setAttribute('aria-hidden', 'true');
      openLink.href = intentUrl || fallback;
      openLink.textContent = intentUrl ? '在系统浏览器中打开' : '返回主页';
      statusText.textContent = intentUrl
        ? '检测到微信环境，请通过右上角“...”打开系统浏览器。'
        : '未识别到可填充的输入内容，请先返回上一页重新打开。';
      actions.style.display = 'block';
    } else if (intentUrl) {
      window.location.replace(intentUrl);
    } else {
      statusText.textContent = '未识别到可直接跳转的输入内容，请检查链接参数。';
      actions.style.display = 'block';
    }

    const linkTarget = intentUrl || fallback;
    fallbackLink.href = linkTarget;
    fallbackLink.title = linkTarget;
  </script>
</body>
</html>
