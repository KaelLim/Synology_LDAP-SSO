<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>慈濟志工雲-北區</title>
    <script type="text/javascript" src="https://sample.me:6322/webman/sso/synoSSO-1.0.0.js"></script>
</head>
<body>
    <div class="header">
        <h1>慈濟志工雲-北區</h1>
        <button id="logout-button">SSO登出</button>
    </div>
</body>
</html>

<?php
$data_users = json_decode(file_get_contents('JSON/data.json'), true);
$ldap_users = json_decode(file_get_contents('JSON/ldap.json'), true);

// 載入群組到地區的映射
$group_to_place = json_decode(file_get_contents('JSON/group_to_place.json'), true);

// 假設我們已經知道目前使用者所屬的群組
$userGroupsFilePath = 'JSON/set_user_groups.json';
if (file_exists($userGroupsFilePath)) {
     $userGroupsJson = file_get_contents($userGroupsFilePath);
     $userGroupsData = json_decode($userGroupsJson, true);

     // 檢查是否能夠成功解析 JSON 數據，且資料中包含 groups 鍵
     if ($userGroupsData && isset($userGroupsData['groups'])) {
         $current_user_groups = $userGroupsData['groups'];
     } else {
         $current_user_groups = []; // 如果無法解析數據，則假設沒有群組資訊
     }
} else {
     $current_user_groups = []; // 如果檔案不存在，則假設沒有群組資訊
}

// 確定使用者有權限查看的地區
$allowed_places = array_map(function($group) use ($group_to_place) {
     return $group_to_place[$group] ?? null; // 使用映射找到對應的地區，如果沒有映射則回傳null
}, $current_user_groups);
$allowed_places = array_filter($allowed_places);

$filtered_users = array_filter($data_users, function($user) use ($allowed_places) {
     return in_array($user['place'], $allowed_places); // 僅保留使用者有權限查看的區域的資料
});

$filtered_ldap_users = array_filter($ldap_users, function($user) use ($allowed_places) {
    return in_array($user['place'], $allowed_places);
});


// 現在$filtered_users包含了所有目前使用者有權限查看的使用者數據，可以在HTML中遍歷這個數組來顯示數據
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Noto+Sans+TC">
</head>
<body>
<div class="review-container">
    <div class="tab">
        <button class="tablinks" onclick="openTab(event, 'DataUsers')">待審查用戶</button>
        <button class="tablinks" onclick="openTab(event, 'LdapUsers')">已審查用戶</button>
    </div>
    <div id="DataUsers" class="tabcontent">
        <div class="data-users">
            <h2>待審查用戶</h2>
            <table>
                <tr>
                    <th>用戶名</th>
                    <th>電話號碼</th>
                    <th>電子郵件</th>
                    <th>地區</th>
                    <th>群組</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($filtered_users as $user): // 使用過濾後的用戶數據 ?>
                    <tr id="user_<?php echo htmlspecialchars($user['username']); ?>">
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['place']); ?></td>
                        <td>
                            <!-- 根據需要顯示的使用者群組資訊調整 -->
                            <input type="radio" name="group_<?php echo $user['username']; ?>" value="出班志工" id="volunteer_<?php echo $user['username']; ?>" checked>
                            <label for="volunteer_<?php echo $user['username']; ?>">出班志工</label>
                            <input type="radio" name="group_<?php echo $user['username']; ?>" value="和氣管理員" id="harmony_<?php echo $user['username']; ?>">
                            <label for="harmony_<?php echo $user['username']; ?>">和氣管理員</label>
                            <input type="radio" name="group_<?php echo $user['username']; ?>" value="合心管理員" id="harmony_<?php echo $user['username']; ?>">
                            <label for="harmony_<?php echo $user['username']; ?>">合心管理員</label><br>
                        </td>
                        <td>
                            <button onclick="approveUser('<?php echo $user['username']; ?>', '<?php echo $user['phone']; ?>')">通過</button>
                            <button onclick="rejectUser('<?php echo $user['username']; ?>', '<?php echo $user['phone']; ?>')">不通過</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div id="LdapUsers" class="tabcontent" style="display:none;">
        <div class="ldap-users">
            <h2>已審查用戶</h2>
            <table>
                <tr>
                    <th>用戶ID</th>
                    <th>用戶名</th>
                    <th>電話號碼</th>
                    <th>電子郵件</th>
                    <th>地區</th>
                    <th>群組</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($filtered_ldap_users as $user): ?>
                    <?php
                    $allowedGroups = ['合心管理員', '和氣管理員', '出班志工'];
                    $userGroups = is_array($user['group']) ? $user['group'] : [$user['group']]; // 確保$user['group']是一個數組
                    $displayGroups = array_intersect($userGroups, $allowedGroups); // 只顯示允許的群組
                    ?>
                    <?php if (!empty($displayGroups)): // 如果有允許顯示的群組，則顯示該用戶 ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username'] . ' ' . $user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['place']); ?></td>
                            <td><?php echo htmlspecialchars(implode(', ', $displayGroups)); ?></td>
                            <td>
                                <button onclick="editUserInfo(this)"
                                        data-user-id="<?php echo htmlspecialchars($user['user_id']); ?>"
                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                        data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                        data-place="<?php echo htmlspecialchars($user['place']); ?>"
                                        data-group="<?php echo htmlspecialchars(implode(', ', $displayGroups)); ?>"
                                        data-old-username="<?php echo htmlspecialchars($user['username']); ?>"
                                        data-old-phone="<?php echo htmlspecialchars($user['phone']); ?>" 
                                        >修改資訊</button>
                                        <button onclick="editUserGroup(this)"
                                        data-user-id="<?php echo htmlspecialchars($user['user_id']); ?>"
                                        data-place="<?php echo htmlspecialchars($user['place']); ?>"
                                        data-group="<?php echo htmlspecialchars(implode(', ', $displayGroups)); ?>"
                                        data-group-old="<?php echo htmlspecialchars(implode(', ', $displayGroups)); ?>"
                                        >修改群組</button>
                                <button onclick="deleteUser('<?php echo $user['user_id']; ?>')">刪除</button>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div id="editUserInfoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editUserInfoModal')">&times;</span>
            <h2>編輯使用者資訊</h2>
            <form id="editUserInfoForm">
                <input type="hidden" id="userInfoUserId" name="userId">
                <input type="hidden" id="userInfoUserPlace" name="place">
                <input type="hidden" id="userInfoUserGroup" name="group">
                <input type="hidden" id="oldUsername" name="oldUsername" value="oldUsername">
                <input type="hidden" id="oldPhone" name="oldPhone" value="oldPhone">
                <label for="userInfoUserName">使用者名稱：</label>
                <input type="text" id="userInfoUserName" name="username" required><br><br>
                <label for="userInfoUserPhone">電話號碼：</label>
                <input type="text" id="userInfoUserPhone" name="phone" required><br><br>
                <label for="userInfoUserEmail">電子郵件：</label>
                <input type="email" id="userInfoUserEmail" name="email" required><br><br>
                <input type="submit" value="更新">
            </form>
        </div>
    </div>
    <div id="editUserGroupModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editUserGroupModal')">&times;</span>
            <h2>編輯使用者群組</h2>
            <form id="editUserGroupForm">
                <input type="hidden" id="userGroupUserId" name="userId">
                
                <label for="userGroupUserPlace">地區：</label>
                <input type="text" id="userGroupUserPlace" name="place" required readonly><br><br>

                <label for="userGroup">群組：</label>
                <select id="userGroup" name="group">
                    <option value="出班志工">出班志工</option>
                    <option value="和氣管理員">和氣管理員</option>
                    <option value="合心管理員">合心管理員</option>
                </select><br><br>
                <input type="hidden" id="userGroupOld" name="groupOld">
                <input type="submit" value="更新">
            </form>
        </div>
    </div>

</div>
    <script type="text/javascript" src="js/SSO.js"></script>
    <script type="text/javascript" src="js/edit.js"></script>
    <script type="text/javascript" src="js/request.js"></script>
    <script type="text/javascript" src="js/logout.js"></script>
</body>
</html>
