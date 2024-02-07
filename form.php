<?php
session_start();

// 假設登入密碼為 "password"
$login_password = "password";
$login_error = '';

// 登出處理
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['loggedin']);
    header('Location: form.php');
    exit;
}

// 處理登入請求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_password'])) {
    if ($_POST['login_password'] === $login_password) {
        $_SESSION['loggedin'] = true;
    } else {
        $login_error = '密碼錯誤';
    }
}

// 檢查是否已登入
if (!isset($_SESSION['loggedin'])) {
    // 顯示登入表單
?>
<!DOCTYPE html>
<html lang="en">
<head>
</head>
<body>
    <div class="login-container">
        <form method="post">
            <h2>登入</h2>
            <input type="password" name="login_password" required>
            <input type="submit" value="登入">
            <?php if ($login_error): ?>
                <p><?php echo $login_error; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// 處理審查頁面的顯示
// 加載 data.json 和 ldap.json 的數據
$data_users = json_decode(file_get_contents('data.json'), true);
$ldap_users = json_decode(file_get_contents('ldap.json'), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="form.css">
</head>
<body>
<div class="review-container">
    <a href="form.php?action=logout">登出</a>
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
                <th>群組</th>
                <th>操作</th>
            </tr>
            <?php foreach ($data_users as $user): ?>
                <tr>
                <tr>
                    <td><?php echo htmlspecialchars($user['username'].$user['phone']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['place']. $user['group']); ?></td>
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
                <th>群組</th>
            </tr>
            <?php foreach ($ldap_users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username'].$user['phone']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['place']. $user['group']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</div>
    <script>
    function sendRequest(action, username) {
    	console.log("發送請求", action, username); // 增加此行
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "user_action.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
        	console.log("響應狀態", this.readyState, this.status); // 增加此行
            if (this.readyState == 4 && this.status == 200) {
            	console.log("響應數據", this.responseText); // 增加此行
                // 請求完成後的處理，例如重新載入頁面或更新界面
                console.log(this.responseText);
                location.reload(); // 重新加載頁面以看到更新
            }
        };
        xhr.send("action=" + action + "&username=" + username);
    }

	function approveUser(username, phone) {
	    if (confirm("確定要通過用戶 " + username  + phone + " 的申請嗎？")) {
	        sendRequest("approve", username);
	    }
	}
	
	function rejectUser(username, phone) {
	    if (confirm("確定要拒絕用戶 " + username  + phone + " 的申請嗎？")) {
	        sendRequest("reject", username);
	    }
	}


    function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}
    </script>
</body>
</html>
