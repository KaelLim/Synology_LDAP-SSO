<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $username = $_POST['username'];
    $groupName = $_POST['groupName'];

    $data_json = json_decode(file_get_contents('../JSON/data.json'), true);
    $ldap_json = json_decode(file_get_contents('../JSON/ldap.json'), true);

    if ($action == "approve") {
        foreach ($data_json as $key => $user) {
            if ($user['username'] == $username) {
                $user['user_id'] = generateUserId(); 
                addUserToLDAP($user, $groupName);

                $new_user = array(
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'phone' => $user['phone'],
                    'email' => $user['email'],
                    'place' => $user['place'],
                    'group' => $groupName
                );

                array_push($ldap_json, $new_user);
                unset($data_json[$key]);
                break;
            }
        }
    } elseif ($action == "reject") {
        foreach ($data_json as $key => $user) {
            if ($user['username'] == $username) {
                unset($data_json[$key]);
                break;
            }
        }
    }

    file_put_contents('../JSON/data.json', json_encode($data_json, JSON_PRETTY_PRINT));
    file_put_contents('../JSON/ldap.json', json_encode($ldap_json, JSON_PRETTY_PRINT));

    echo "操作成功完成";
}

function generateUserId() {
    $ldap_json = json_decode(file_get_contents('../JSON/ldap.json'), true);
    if (!empty($ldap_json)) {
        $last_user = end($ldap_json);
        return $last_user['user_id'] + 1;
    } else {
        return 10000;
    }
}

function addUserToLDAP($user, $groupName) {
    if (!isset($user['username']) || !isset($user['phone']) || !isset($user['email'])) {
        error_log("錯誤：用戶數據不完整");
        return false;
    }
    $ldap_settings = json_decode(file_get_contents('../JSON/ldap_settings.json'), true);

    $ldap_server = $ldap_settings['ldap_server'];
    $ldap_user_dn = $ldap_settings['ldap_user_dn'];
    $ldap_password = $ldap_settings['ldap_password'];
    $ldap_base_dn = $ldap_settings['ldap_base_dn'];

    $ldapconn = ldap_connect($ldap_server);
    if (!$ldapconn) {
        error_log("LDAP连接失败");
        return;
    }
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    if (!ldap_bind($ldapconn, $ldap_user_dn, $ldap_password)) {
        error_log("LDAP绑定失败: " . ldap_error($ldapconn));
        ldap_close($ldapconn);
        return;
    }
    $uniqueUsername = $user['username'] . $user['phone'];

    if ($ldapconn && ldap_bind($ldapconn, $ldap_user_dn, $ldap_password)) {
        $info = array(
            "cn" => $uniqueUsername,
            "uid" => $uniqueUsername,
            "sn" => $user['username'],
            "uidNumber" => $user['user_id'],
            "gidNumber" => "1000001",
            "loginShell" => "/bin/sh",
            "homeDirectory" => "/home/".$uniqueUsername,
            "mail" => $user['email'],
            "userPassword" => "{CRYPT}" . password_hash("tcf94800552", PASSWORD_BCRYPT),
            "objectclass" => array("top", "person", "organizationalPerson", "inetOrgPerson", "posixAccount", "shadowAccount"),
            "shadowLastChange" => floor(time() / (24 * 60 * 60)),
            "shadowMin" => 0,
            "shadowMax" => 99999,
            "shadowWarning" => 7,
            "shadowExpire" => -1,
            "shadowInactive" => 0,
            "shadowFlag" => 0,
            'telephoneNumber' => $user['phone'],
        );

        ldap_add($ldapconn, "uid=".$uniqueUsername.",cn=users,".$ldap_base_dn, $info);
        $combinedGroup = $user['place']. $groupName;

        $groupDn = "cn={$combinedGroup},cn=groups,".$ldap_base_dn;
        $userDn = "uid=".$uniqueUsername.",cn=users,".$ldap_base_dn;
        addUserToGroup($ldapconn, $userDn, $groupDn, $uniqueUsername);

        ldap_close($ldapconn);
    }
}

function addUserToGroup($ldapconn, $userDn, $groupDn, $uniqueUsername) {
    // 準備要添加的屬性和它們的值
    $modifications = array(
        'member' => array($userDn),
        'memberuid' => array($uniqueUsername)
    );

    // 嘗試將用戶添加到群組
    if (ldap_mod_add($ldapconn, $groupDn, $modifications)) {
        error_log("成功將用戶 $userDn 和 $uniqueUsername 添加到群組 $groupDn");
    } else {
        error_log("將用戶 $userDn 和 $uniqueUsername 添加到群組 $groupDn 失敗: " . ldap_error($ldapconn));
    }
}
?>
