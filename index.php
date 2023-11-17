<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>註冊</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="form-container">
    <form class="fr" action="register.php" method="post">
      <h1 class="elem">社區網2.0<br>LDAP 帳號註冊</h1>
  
      <div class="username-container">
        <label for='username'>用戶名:</label>
        <input type="text" id="username" name="username" class="elem" required>
      </div>

      <div class='phone-container'>
        <label for='phone'>電話號碼:</label>
        <input type="text" id="phone" name="phone" class="elem" required>
      </div>
      
      <div class='email-container'>
        <label for='email'>電子郵件:</label>
        <input type="email" id="email" name="email" class="elem" required>
      </div>

      <div class='group-container'>
        <label for='group'>群組:</label>
        <select id="group" name="group" class="elem">
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
        </select>
      </div>

      <input type="submit" value="註冊帳號" class="submit-btn elem">
    </form>
    <img src="https://img.freepik.com/free-vector/fingerprint-concept-illustration_114360-3630.jpg?w=740&t=st=1690655121~exp=1690655721~hmac=a5de1b1e50d0513d9af30d378c665483c904dd89a6ca2eaae62986b10e3b5c85">
  </div>
  <script src="myscripts.js"></script>
</body>
</html>