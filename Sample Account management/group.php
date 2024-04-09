<?php
// 从配置文件读取LDAP设置
$settingsJson = file_get_contents('JSON/ldap_settings.json');
$settings = json_decode($settingsJson, true);

$ldapServer = $settings['ldap_server'];
$ldapUserDn = $settings['ldap_user_dn'];
$ldapPassword = $settings['ldap_password'];
$ldapBaseDn = $settings['ldap_base_dn'];

// 特定群组和组织单元的变量
$specificGroup = '北一合心管理員'; // 请根据需要修改

$membersDetails = []; // 用于存储所有成员详细信息的数组

// 连接LDAP服务器
$ldapconn = ldap_connect($ldapServer) or die("Could not connect to LDAP server.");
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

if ($ldapconn) {
    // 绑定到LDAP服务器
    $ldapbind = ldap_bind($ldapconn, $ldapUserDn, $ldapPassword) or die("Could not bind to LDAP server.");
    
    if ($ldapbind) {
        // 拼接群组的DN
        $groupDn = "cn=$specificGroup," . $ldapBaseDn;
        
        // 搜索特定群组中的所有成员UID
        $searchFilter = "(&(objectClass=posixGroup)(cn=$specificGroup))";
        $result = ldap_search($ldapconn, $ldapBaseDn, $searchFilter, ["memberUid"]);
        $entries = ldap_get_entries($ldapconn, $result);
        
        if ($entries["count"] > 0 && isset($entries[0]["memberuid"])) {
            foreach ($entries[0]["memberuid"] as $uid) {
                if ($uid == "count") continue; // 跳过"count"键

                // 对每个memberUid进行详细信息查询
                $userFilter = "(uid=$uid)";
                $userResult = ldap_search($ldapconn, $ldapBaseDn, $userFilter);
                $userInfo = ldap_get_entries($ldapconn, $userResult);
                
                if ($userInfo["count"] > 0) {
                    // 获取所有属性
                    $userDetails = [];
                    foreach($userInfo[0] as $attribute => $values) {
                        if ($attribute == "count") continue; // 跳过"count"键
                        if (is_array($values)) {
                            $userDetails[$attribute] = $values[0]; // 取第一个值，如果需要所有值，可以直接赋值$values
                        }
                    }
                    $membersDetails[] = $userDetails;
                }
            }
        } else {
            echo "No members found in the group.\n";
        }
    } else {
        echo "LDAP bind failed...";
    }
    
    // 关闭LDAP连接
    ldap_close($ldapconn);
} else {
    echo "Unable to connect to LDAP server.";
}

// 以JSON格式输出所有成员的详细信息
header('Content-Type: application/json; charset=utf-8');
echo "<pre>" . json_encode($membersDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
?>
