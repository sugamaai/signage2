<?php
// サイネージ管理ツール。
// 1. 既存ページ（signage50、および本ツールで追加したページ）の画像差し替え
// 2. signage50_benkyokai.html と同じ挙動の新規ページ追加（ランダム表示にも自動登録）
// HTML側のファイル名変更は行わず、常に同じ画像パスへ上書き保存する。

declare(strict_types=1);

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('config.php が見つかりません。config.sample.php を参考に作成してください。');
}
$config = require $configFile;

$rootDir = __DIR__ . '/..';
$registryPath = $rootDir . '/img/info/signage_pages.json';
$signage50MetaPath = $rootDir . '/img/info/signage50_meta.json';
$templatePath = $rootDir . '/signage50_benkyokai.html';
$oldImgTag = '<img src="img/info/20261027.png" alt="勉強会" id="signageImage">';

$maxFileSize = 10 * 1024 * 1024; // 10MB
$allowedMime = ['image/png' => 'png', 'image/jpeg' => 'jpg'];

$error = '';
$success = '';

function loadRegistry(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function saveRegistry(string $path, array $data): void
{
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function formatUntil(?string $until): string
{
    if (!$until) {
        return '設定なし（期限なし・常時表示）';
    }
    $date = DateTime::createFromFormat('Y-m-d\TH:i', $until);
    return $date ? $date->format('Y年n月j日 H:i') . ' まで' : $until;
}

$registry = loadRegistry($registryPath);

$signage50Until = null;
if (is_file($signage50MetaPath)) {
    $meta = json_decode((string) file_get_contents($signage50MetaPath), true);
    if (is_array($meta) && !empty($meta['until'])) {
        $signage50Until = (string) $meta['until'];
    }
}

// 画像差し替えの対象一覧（組み込みのsignage50 + 追加ページ）
$targets = [
    'signage50' => [
        'label' => 'signage50_benkyokai.html（勉強会）',
        'html' => 'signage50_benkyokai.html',
        'image' => 'img/info/20261027.png',
        'until' => $signage50Until,
    ],
];
foreach ($registry as $entry) {
    $targets[$entry['id']] = [
        'label' => $entry['html'] . '（' . $entry['alt'] . '）',
        'html' => $entry['html'],
        'image' => $entry['image'],
        'until' => $entry['until'] ?? null,
    ];
}

$formAction = $_POST['form_action'] ?? '';

// ---- 画像の差し替え ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $formAction === 'replace') {
    $password = $_POST['password'] ?? '';
    $targetId = $_POST['target'] ?? '';
    $untilInput = trim($_POST['until'] ?? '');

    if (!password_verify($password, $config['password_hash'])) {
        $error = 'パスワードが違います。';
    } elseif (!isset($targets[$targetId])) {
        $error = '対象のページが見つかりません。';
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
        } else {
            $absoluteTargetImage = $rootDir . '/' . $targets[$targetId]['image'];
            if (!move_uploaded_file($tmpPath, $absoluteTargetImage)) {
                $error = '画像の保存に失敗しました。サーバーの書き込み権限を確認してください。';
            } else {
                @chmod($absoluteTargetImage, 0644);
                $untilValue = $untilInput !== '' ? $untilInput : null;

                if ($targetId === 'signage50') {
                    file_put_contents($signage50MetaPath, json_encode(['until' => $untilValue]), LOCK_EX);
                    $signage50Until = $untilValue;
                } else {
                    foreach ($registry as &$entry) {
                        if ($entry['id'] === $targetId) {
                            $entry['until'] = $untilValue;
                        }
                    }
                    unset($entry);
                    saveRegistry($registryPath, $registry);
                }
                $targets[$targetId]['until'] = $untilValue;
                $success = htmlspecialchars($targets[$targetId]['label'], ENT_QUOTES, 'UTF-8') . ' の画像を更新しました。';
            }
        }
    }
}

// ---- ページを追加する ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $formAction === 'add') {
    $password = $_POST['password'] ?? '';
    $slug = trim($_POST['slug'] ?? '');
    $label = trim($_POST['label'] ?? '');
    $untilInput = trim($_POST['until'] ?? '');

    $htmlFilename = 'signage_' . $slug . '.html';
    $htmlAbsPath = $rootDir . '/' . $htmlFilename;

    if (!password_verify($password, $config['password_hash'])) {
        $error = 'パスワードが違います。';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]{1,40}$/', $slug)) {
        $error = 'ページ識別名は半角英数字・ハイフン・アンダースコアのみ、40文字以内で入力してください。';
    } elseif ($label === '') {
        $error = '表示名を入力してください。';
    } elseif (isset($targets[$slug])) {
        $error = 'このページ識別名は既に使用されています。';
    } elseif (is_file($htmlAbsPath)) {
        $error = '同名のファイルが既に存在するため作成できません。';
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
        } else {
            $ext = $allowedMime[$imageInfo['mime']];
            $imageRelPath = 'img/info/' . $slug . '.' . $ext;
            $imageAbsPath = $rootDir . '/' . $imageRelPath;

            $template = @file_get_contents($templatePath);

            if ($template === false || strpos($template, $oldImgTag) === false) {
                $error = 'テンプレート（signage50_benkyokai.html）の読み込みに失敗しました。';
            } elseif (!move_uploaded_file($tmpPath, $imageAbsPath)) {
                $error = '画像の保存に失敗しました。サーバーの書き込み権限を確認してください。';
            } else {
                @chmod($imageAbsPath, 0644);

                $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                $newImgTag = '<img src="' . $imageRelPath . '" alt="' . $safeLabel . '" id="signageImage">';
                $newHtml = str_replace($oldImgTag, $newImgTag, $template);
                file_put_contents($htmlAbsPath, $newHtml);

                $untilValue = $untilInput !== '' ? $untilInput : null;
                $registry[] = [
                    'id' => $slug,
                    'html' => $htmlFilename,
                    'image' => $imageRelPath,
                    'alt' => $label,
                    'until' => $untilValue,
                ];
                saveRegistry($registryPath, $registry);

                $targets[$slug] = [
                    'label' => $htmlFilename . '（' . $label . '）',
                    'html' => $htmlFilename,
                    'image' => $imageRelPath,
                    'until' => $untilValue,
                ];

                $success = htmlspecialchars($htmlFilename, ENT_QUOTES, 'UTF-8') . ' を作成し、ランダム表示に追加しました。';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>サイネージ管理</title>
<meta name="robots" content="noindex,nofollow">
<style>
  body { font-family: sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; }
  .msg-error { color: #c00; margin-bottom: 12px; }
  .msg-success { color: #080; margin-bottom: 12px; }
  .preview { max-width: 100%; }
  label { display: block; margin: 12px 0 4px; }
  table { border-collapse: collapse; width: 100%; font-size: 14px; }
  th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
  th { background: #f5f5f5; }
  td img { max-width: 120px; display: block; }
  .hint { font-size: 12px; color: #666; margin-top: 4px; }
  fieldset { margin-top: 32px; }
</style>
</head>
<body>
<h1>サイネージ管理</h1>
<p>画像の差し替え、および新しいサイネージページの追加ができます。</p>

<?php if ($error !== ''): ?>
  <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if ($success !== ''): ?>
  <p class="msg-success"><?= $success ?></p>
<?php endif; ?>

<h2>現在のページ一覧</h2>
<table>
  <tr><th>ページ</th><th>プレビュー</th><th>表示終了日時</th></tr>
  <?php foreach ($targets as $id => $t): ?>
  <tr>
    <td>
      <a href="../<?= htmlspecialchars($t['html'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($t['html'], ENT_QUOTES, 'UTF-8') ?></a>
    </td>
    <td><img src="../<?= htmlspecialchars($t['image'], ENT_QUOTES, 'UTF-8') ?>?t=<?= time() ?>" alt=""></td>
    <td><?= htmlspecialchars(formatUntil($t['until']), ENT_QUOTES, 'UTF-8') ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<fieldset>
<legend>画像を差し替える</legend>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="form_action" value="replace">

  <label>対象ページ</label>
  <select name="target" id="targetSelect" onchange="fillUntil()">
    <?php foreach ($targets as $id => $t): ?>
      <option value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" data-until="<?= htmlspecialchars($t['until'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label>パスワード</label>
  <input type="password" name="password" required>

  <label>新しい画像（PNG / JPEG）</label>
  <input type="file" name="image" accept="image/png,image/jpeg" required>

  <label>表示終了日時（任意）</label>
  <input type="datetime-local" name="until" id="untilInput">
  <p class="hint">設定すると、この日時を過ぎた時点でランダム表示の対象から自動的に除外されます。空欄のままにすると期限なしで表示され続けます。</p>

  <p><button type="submit">アップロードして差し替える</button></p>
</form>
</fieldset>

<fieldset>
<legend>ページを追加する</legend>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="form_action" value="add">

  <label>パスワード</label>
  <input type="password" name="password" required>

  <label>ページ識別名（半角英数字・ハイフン・アンダースコアのみ）</label>
  <input type="text" name="slug" pattern="[a-zA-Z0-9_-]{1,40}" maxlength="40" required placeholder="例: event202609">
  <p class="hint">「signage_識別名.html」というファイル名で新規ページが作成されます。</p>

  <label>表示名（画像の説明）</label>
  <input type="text" name="label" maxlength="100" required placeholder="例: 9月イベント告知">

  <label>画像（PNG / JPEG）</label>
  <input type="file" name="image" accept="image/png,image/jpeg" required>

  <label>表示終了日時（任意）</label>
  <input type="datetime-local" name="until">
  <p class="hint">signage50_benkyokai.html と同じ見た目・挙動のページが作成され、ランダム表示にも自動的に追加されます。</p>

  <p><button type="submit">ページを追加する</button></p>
</form>
</fieldset>

<script>
function fillUntil() {
  var select = document.getElementById('targetSelect');
  var opt = select.options[select.selectedIndex];
  document.getElementById('untilInput').value = opt.getAttribute('data-until') || '';
}
document.addEventListener('DOMContentLoaded', fillUntil);
</script>
</body>
</html>
