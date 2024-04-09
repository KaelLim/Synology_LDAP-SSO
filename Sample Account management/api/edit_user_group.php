<?php
header('Content-Type: application/json');
$settings = json_decode(file_get_contents('../JSON/ldap_settings.json'), true);
$ldapUsers = json_decode(file_get_contents('../JSON/ldap.json'), true);

$user_id = $_POST['userId'];
$place = $_POST['place'];
$newGroup = $_POST['group'];
$oldGroup = $_POST['groupOld'];

$ldapconn = ldap_connect($settings['ldap_server']);
if (!$ldapconn) {
    die(json_encode(['error' => '無法連接到 LDAP 伺服器。']));
}

ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

if (!ldap_bind($ldapconn, $settings['ldap_user_dn'], $settings['ldap_password'])) {
    die(json_encode(['error' => 'LDAP 綁定失敗。']));
}

$filter = "(uidNumber=$user_id)";
$search = ldap_search($ldapconn, $settings['ldap_base_dn'], $filter);
$entries = ldap_get_entries($ldapconn, $search);

if ($entries['count'] == 0) {
    die(json_encode(['error' => '未找到用戶。']));
}

$uid = $entries[0]['uid'][0];

$currentGroupDN = "cn=" . $place . $oldGroup . ",cn=groups," . $settings['ldap_base_dn'];
$newGroupDN = "cn=" . $place . $newGroup . ",cn=groups," . $settings['ldap_base_dn'];

if (!ldap_mod_del($ldapconn, $currentGroupDN, ['memberUid' => [$uid]])) {
    ldap_close($ldapconn);
    die(json_encode(['error' => "從當前群組移除用戶失敗。LDAP 錯誤：" . ldap_error($ldapconn)]));
}

if (!ldap_mod_add($ldapconn, $newGroupDN, ['memberUid' => [$uid]])) {
    ldap_close($ldapconn);
    die(json_encode(['error' => "將用戶添加到新群組失敗。LDAP 錯誤：" . ldap_error($ldapconn)]));
}

// 更新 JSON 檔案
foreach ($ldapUsers as $key => $user) {
    if ($user['user_id'] == $user_id) {
        $ldapUsers[$key]['group'] = $newGroup;
        break;
    }
}

if (file_put_contents('../JSON/ldap.json', json_encode($ldapUsers, JSON_PRETTY_PRINT)) === false) {
    ldap_close($ldapconn);
    die(json_encode(['error' => '無法更新本地 JSON 檔案。']));
}

echo json_encode(['success' => '用戶群組資訊更新成功。']);

ldap_close($ldapconn);
?>
