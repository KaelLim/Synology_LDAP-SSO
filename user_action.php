<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $username = $_POST['username'];

    $data_json = json_decode(file_get_contents('data.json'), true);
    $ldap_json = json_decode(file_get_contents('ldap.json'), true);

    if ($action == "approve") {
        // 處理通過動作：移動用戶從 data.json 到 ldap.json
        foreach ($data_json as $key => $user) {
            if ($user['username'] == $username) {
                // 僅保留必要的信息
                $new_user = array(
                    'user_id' => generateUserId(), // 生成 user_id
                    'username' => $user['username'],
                    'phone' => $user['phone'],
                    'email' => $user['email'],
                    'group' => $user['group']
                );

                array_push($ldap_json, $new_user);
                unset($data_json[$key]);
                break;
            }
        }
    } elseif ($action == "reject") {
        // 處理不通過動作：從 data.json 中刪除用戶
        foreach ($data_json as $key => $user) {
            if ($user['username'] == $username) {
                unset($data_json[$key]);
                break;
            }
        }
    }

    // 將更新後的數據寫回文件
    file_put_contents('data.json', json_encode($data_json, JSON_PRETTY_PRINT));
    file_put_contents('ldap.json', json_encode($ldap_json, JSON_PRETTY_PRINT));

    echo "操作成功完成";
}

function generateUserId() {
    // 讀取 ldap.json 檔案
    $ldap_json = json_decode(file_get_contents('ldap.json'), true);
    if (!empty($ldap_json)) {
        // 獲取最後一個用戶的 user_id 並加 1
        $last_user = end($ldap_json);
        return $last_user['user_id'] + 1;
    } else {
        // 如果 ldap.json 是空的，從 10000 開始
        return 10000;
    }
}
?>
