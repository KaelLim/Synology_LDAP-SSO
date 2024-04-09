<?php
$user_id = $_GET['user_id']; // 从URL获取user_id

// 读取LDAP服务器配置
$settings = json_decode(file_get_contents('../JSON/ldap_settings.json'), true);

$ldap_server = $settings['ldap_server'];
$ldap_user_dn = $settings['ldap_user_dn'];
$ldap_password = $settings['ldap_password'];
$ldap_base_dn = $settings['ldap_base_dn'];

header('Content-Type: application/json'); // 设置响应类型为JSON

// 连接到LDAP服务器
$ldapconn = ldap_connect($ldap_server) or die(json_encode(["error" => "Could not connect to LDAP server."]));
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

// 绑定到LDAP服务器
if (!@ldap_bind($ldapconn, $ldap_user_dn, $ldap_password)) {
    echo json_encode(["error" => "LDAP bind failed."]);
    exit;
}

// 第一步：根据user_id查询用户的DN
$filter = "(uidNumber={$user_id})"; // 根据实际LDAP属性调整
$search = ldap_search($ldapconn, $ldap_base_dn, $filter, ["dn"]);
$entries = ldap_get_entries($ldapconn, $search);

if ($entries["count"] > 0) {
    $userDn = $entries[0]["dn"];

    // 第二步：根据用户的DN查询所属群组
    $filter = "(&(objectclass=posixGroup)(member={$userDn}))";
    $search = ldap_search($ldapconn, $ldap_base_dn, $filter, ["cn"]);
    $groupsInfo = ldap_get_entries($ldapconn, $search);

    $groups = [];
    for ($i = 0; $i < $groupsInfo["count"]; $i++) {
        $groups[] = $groupsInfo[$i]["cn"][0];
    }

    $userData = ["user_id" => $user_id, "groups" => $groups];
    file_put_contents('../JSON/set_user_groups.json', json_encode($userData, JSON_PRETTY_PRINT));

    echo json_encode(["user_id" => $user_id, "groups" => $groups]);
} else {
    echo json_encode(["error" => "User not found"]);
}

ldap_close($ldapconn);
?>
