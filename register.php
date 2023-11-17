<?php
$message = ''; // 用於儲存錯誤訊息或成功訊息

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 從 POST 請求中獲取用戶信息
    $new_user = array(
        "username" => $_POST['username'],
        "phone" => $_POST['phone'],
        "email" => $_POST['email'],
        "group" => $_POST['group']
    );

    // 讀取現有的 data.json 檔案
    $data_json = file_exists('data.json') ? json_decode(file_get_contents('data.json'), true) : array();
    $ldap_json = file_exists('ldap.json') ? json_decode(file_get_contents('ldap.json'), true) : array();

    // 檢查用戶名是否已存在
    $exists_in_data = array_filter($data_json, function($user) use ($new_user) {
        return $user['username'] == $new_user['username'];
    });
    $exists_in_ldap = array_filter($ldap_json, function($user) use ($new_user) {
        return $user['username'] == $new_user['username'];
    });

    if (!empty($exists_in_data) || !empty($exists_in_ldap)) {
        $message = "用戶名已存在。";
    } else {
        // 將新用戶添加到數據中
        array_push($data_json, $new_user);

        // 將更新後的數據寫回 data.json 檔案
        file_put_contents('data.json', json_encode($data_json, JSON_PRETTY_PRINT));

        $message = "註冊成功！";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>註冊成功</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="form-container">
    <form class="fr">
      <div class="success-container">
        <?php if ($message): ?>
            <h1><?php echo $message; ?></h1>
        <?php endif; ?>
      </div> 
      
    </form>
    <img src="https://img.freepik.com/free-vector/fingerprint-concept-illustration_114360-3630.jpg?w=740&t=st=1690655121~exp=1690655721~hmac=a5de1b1e50d0513d9af30d378c665483c904dd89a6ca2eaae62986b10e3b5c85">
  </div>
</body>
</html>
?>
