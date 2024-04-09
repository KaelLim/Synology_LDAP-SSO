<?php
// 清空 JSON/set_user_groups.json
$file = '../JSON/set_user_groups.json';
if (file_exists($file)) {
    file_put_contents($file, json_encode([]));  // 將陣列轉為 JSON 字串並保存
}

echo json_encode(['success' => 'Logged out and data cleared.']);
?>