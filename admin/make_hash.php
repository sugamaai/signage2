<?php
// パスワードハッシュ生成用の一時ツールです。
//
// 使い方:
// 1. このページをブラウザで開く
// 2. 管理画面用に決めたパスワードを入力して「生成」を押す
// 3. 表示されたハッシュ文字列を config.php の password_hash に貼り付ける
// 4. 貼り付けが終わったら、このファイル（make_hash.php）を必ずサーバーから削除する

declare(strict_types=1);

$hash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['password'] ?? '') !== '') {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>パスワードハッシュ生成（一時利用）</title>
<meta name="robots" content="noindex,nofollow">
<style>
  body { font-family: sans-serif; max-width: 480px; margin: 40px auto; padding: 0 16px; }
  textarea { width: 100%; margin-top: 8px; }
  .warn { color: #c00; }
</style>
</head>
<body>
<h1>パスワードハッシュ生成</h1>
<p class="warn">使い終わったら必ずこのファイルを削除してください。</p>

<form method="post">
  <label>設定したいパスワード</label><br>
  <input type="password" name="password" required>
  <button type="submit">生成</button>
</form>

<?php if ($hash !== ''): ?>
  <p>config.php の password_hash に貼り付けてください:</p>
  <textarea rows="3" readonly onclick="this.select()"><?= htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') ?></textarea>
<?php endif; ?>
</body>
</html>
