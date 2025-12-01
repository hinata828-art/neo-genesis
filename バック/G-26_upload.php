<?php
// アップロード先ディレクトリ（バックの1つ上 → img）
$upload_dir = __DIR__ . '/../img/';

// フォルダが存在しない場合はエラー
if (!is_dir($upload_dir)) {
    die("画像保存先のディレクトリが存在しません: " . $upload_dir);
}

// ファイルが送信されているかチェック
if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
    die("ファイルのアップロードに失敗しました。");
}

$file = $_FILES['product_image'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// 画像だけ許可
$allowed = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($ext, $allowed)) {
    die("許可されていないファイル形式です。");
}

// ファイル名を一意に（重複対策）
$filename = uniqid('img_', true) . '.' . $ext;

// 保存パス
$save_path = $upload_dir . $filename;

// 保存処理
if (!move_uploaded_file($file['tmp_name'], $save_path)) {
    die("画像の保存に失敗しました。");
}

// 呼び出し元にファイル名を返す
echo $filename;
?>
