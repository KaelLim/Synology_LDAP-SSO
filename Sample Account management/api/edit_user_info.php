<?php
header('Content-Type: application/json');
$settings = json_decode(file_get_contents('../JSON/ldap_settings.json'), true);

$user_id = $_POST['userId'];
$username = $_POST['username'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$place = $_POST['place'];
$group = $_POST['group'];
$oldUsername = $_POST['oldUsername'];
$oldPhone = $_POST['oldPhone'];

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

$userDn = $entries[0]['dn'];
$newUid = $username . $phone; // 正確地使用 $newUid 替代 $uniqueUsername
$newRdn = "uid=" . $newUid;
$newParentDN = "cn=users," . $settings['ldap_base_dn']; // 使用正確的父DN

if (ldap_rename($ldapconn, $userDn, $newRdn, $newParentDN, true)) {
    // 更新用戶的其他信息
    $updateInfo = [
        'cn' => $username, // 確保這是合法的cn值
        'telephoneNumber' => $phone,
        'mail' => $email // 確保$email是一個有效的郵箱地址
    ];

    $newUserDN = $newRdn . "," . $newParentDN; // 構建新的用戶DN

    if (!ldap_modify($ldapconn, $newUserDN, $updateInfo)) {
        die(json_encode(['error' => "更新用戶信息失敗。LDAP 錯誤：" . ldap_error($ldapconn)]));
    }

    // 構建群組的 DN 並更新群組中的 memberUid
    $groupDN = "cn=" . $place . $group . ",cn=groups," . $settings['ldap_base_dn'];
    $memberUidInfo = ['memberUid' => [$newUid]];

    if (!ldap_mod_replace($ldapconn, $groupDN, $memberUidInfo)) {
        die(json_encode(['error' => "替換群組中的 memberUid 失敗。LDAP 錯誤：" . ldap_error($ldapconn)]));
    }

    echo json_encode(['success' => '用戶信息及群組 memberUid 更新成功。']);
} else {
    die(json_encode(['error' => "修改用戶UID失敗：" . ldap_error($ldapconn)]));
}

ldap_close($ldapconn);
?>
