<?php
// 引入LDAP伺服器設定
$settings = json_decode(file_get_contents('../JSON/ldap_settings.json'), true);

// 獲取從請求中傳過來的用戶ID
$user_id = $_GET['user_id']; // 注意改成 GET 方便前端發送

// 連接到LDAP伺服器
$ldapconn = ldap_connect($settings['ldap_server']);
if (!$ldapconn) {
    die(json_encode(['error' => 'Unable to connect to LDAP server.']));
}

ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3); // 設定LDAP協議版本

if (!ldap_bind($ldapconn, $settings['ldap_user_dn'], $settings['ldap_password'])) {
    die(json_encode(['error' => 'LDAP bind failed.']));
}

// 構造查詢過濾器以查找相應的用戶DN
$filter = "(uidNumber=$user_id)";
$search = ldap_search($ldapconn, $settings['ldap_base_dn'], $filter);
$entries = ldap_get_entries($ldapconn, $search);

if ($entries['count'] == 0) {
    die(json_encode(['error' => 'User not found.']));
}

$userDn = $entries[0]['dn']; // 獲取用戶的DN

if (!ldap_delete($ldapconn, $userDn)) {
    die(json_encode(['error' => 'Failed to delete user.']));
}

// 以下是新增的部分，用於從ldap.json刪除用戶資料
$ldapUsersFilePath = '../JSON/ldap.json';
$ldapUsers = json_decode(file_get_contents($ldapUsersFilePath), true);

foreach ($ldapUsers as $index => $user) {
    if ($user['user_id'] == $user_id) {
        unset($ldapUsers[$index]);
        break;
    }
}

file_put_contents($ldapUsersFilePath, json_encode(array_values($ldapUsers), JSON_PRETTY_PRINT));

echo json_encode(['success' => 'User deleted successfully.']);

ldap_close($ldapconn);
?>
