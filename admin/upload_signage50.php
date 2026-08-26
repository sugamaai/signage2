<?php
// signage50_benkyokai.html が参照している画像（img/info/20261027.png）を
// ブラウザから差し替えるための管理ツール。
// HTML側のファイル名は変更しない前提のため、常に同じパスへ上書き保存する。

declare(strict_types=1);

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('config.php が見つかりません。config.sample.php を参考に作成してください。');
}
$config = require $configFile;

$targetPath = __DIR__ . '/../img/info/20261027.png';
$publicPath = '../img/info/20261027.png';
$metaPath = __DIR__ . '/../img/info/signage50_meta.json';
$maxFileSize = 10 * 1024 * 1024; // 10MB
$allowedMime = ['image/png' => 'png', 'image/jpeg' => 'jpg'];

$error = '';
$success = false;

// 現在設定されている表示終了日時（フォームの初期値表示用）
$currentUntil = '';
if (is_file($metaPath)) {
    $meta = json_decode((string) file_get_contents($metaPath), true);
    if (is_array($meta) && !empty($meta['until'])) {
        $currentUntil = (string) $meta['until'];
    }
}

$currentUntilDisplay = '設定なし（期限なし・常時表示）';
$untilDate = DateTime::createFromFormat('Y-m-d\TH:i', $currentUntil);
if ($untilDate !== false) {
    $currentUntilDisplay = $untilDate->format('Y年n月j日 H:i') . ' まで';
}
$currentImageUpdated = is_file($targetPath) ? date('Y年n月j日 H:i', filemtime($targetPath)) : '未アップロード';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $untilInput = trim($_POST['until'] ?? ''); // datetime-local: "2026-08-31T23:59"

    if (!password_verify($password, $config['password_hash'])) {
        $error = 'パスワードが違います。';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = '画像のアップロードに失敗しました。';
    } elseif ($_FILES['image']['size'] > $maxFileSize) {
        $error = 'ファイルサイズが大きすぎます（10MBまで）。';
    } elseif ($untilInput !== '' && DateTime::createFromFormat('Y-m-d\TH:i', $untilInput) === false) {
        $error = '表示終了日時の形式が正しくありません。';
    } else {
        $tmpPath = $_FILES['image']['tmp_name'];
        $imageInfo = @getimagesize($tmpPath);

        if ($imageInfo === false || !isset($allowedMime[$imageInfo['mime']])) {
            $error = 'PNGまたはJPEG形式の画像をアップロードしてください。';
        } elseif (!move_uploaded_file($tmpPath, $targetPath)) {
            $error = '画像の保存に失敗しました。サーバーの書き込み権限を確認してください。';
        } else {
            @chmod($targetPath, 0644);
            file_put_contents($metaPath, json_encode(['until' => $untilInput !== '' ? $untilInput : null]));
            $currentUntil = $untilInput;
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>サイネージ画像差し替え（signage50）</title>
<meta name="robots" content="noindex,nofollow">
<style>
  body { font-family: sans-serif; max-width: 480px; margin: 40px auto; padding: 0 16px; }
  .msg-error { color: #c00; margin-bottom: 12px; }
  .msg-success { color: #080; margin-bottom: 12px; }
  .preview { max-width: 100%; margin-top: 12px; border: 1px solid #ddd; }
  label { display: block; margin: 12px 0 4px; }
</style>
</head>
<body>
<h1>サイネージ画像差し替え</h1>
<p>signage50_benkyokai.html が表示する画像を差し替えます。HTMLの編集は不要です。</p>

<?php if ($error !== ''): ?>
  <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if ($success): ?>
  <p class="msg-success">画像を更新しました。</p>
<?php endif; ?>

<h2>現在の設定</h2>
<img class="preview" src="<?= htmlspecialchars($publicPath, ENT_QUOTES, 'UTF-8') ?>?t=<?= time() ?>" alt="現在の表示画像">
<table style="margin-top:8px;font-size:14px;">
  <tr><td style="padding-right:12px;color:#666;">最終更新</td><td><?= htmlspecialchars($currentImageUpdated, ENT_QUOTES, 'UTF-8') ?></td></tr>
  <tr><td style="padding-right:12px;color:#666;">表示終了日時</td><td><strong><?= htmlspecialchars($currentUntilDisplay, ENT_QUOTES, 'UTF-8') ?></strong></td></tr>
</table>

<h2>画像を差し替える</h2>
<form method="post" enctype="multipart/form-data">
  <label>パスワード</label>
  <input type="password" name="password" required>

  <label>新しい画像（PNG / JPEG）</label>
  <input type="file" name="image" accept="image/png,image/jpeg" required>

  <label>表示終了日時（任意）</label>
  <input type="datetime-local" name="until" value="<?= htmlspecialchars($currentUntil, ENT_QUOTES, 'UTF-8') ?>">
  <p style="font-size:12px;color:#666;margin-top:4px;">設定すると、この日時を過ぎた時点でランダム表示の対象から自動的に除外されます。空欄のままにすると期限なしで表示され続けます。</p>

  <p><button type="submit">アップロードして差し替える</button></p>
</form>
</body>
</html>
