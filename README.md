# PHP LDAP用戶管理系統教學

這份教學旨在解釋如何使用PHP腳本進行LDAP用戶管理，包括審批和拒絕用戶的操作。這個示例特別適合用於學習目的，並非生產環境的最佳實踐。

## 功能概述

`user_action.php`腳本處理兩種操作：`approve`（審批）和`reject`（拒絕）。審批操作會將用戶從待定列表移至LDAP系統，而拒絕操作則從列表中移除用戶。

### 腳本流程

1. 首先檢查是否為POST請求。
2. 根據POST數據中的`action`判斷是進行審批還是拒絕操作。
3. 對於審批操作，生成新的`user_id`，將用戶添加到LDAP系統，並更新`ldap.json`。
4. 對於拒絕操作，直接從`data.json`中移除該用戶。
5. 最後，更新`data.json`和`ldap.json`文件。

```php
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 省略具體實現...
}

function generateUserId() {
    // 省略具體實現...
}

function addUserToLDAP($user) {
    // 省略具體實現...
}

function addUserToGroup($ldapconn, $userDn, $groupDn, $uniqueUsername) {
    // 省略具體實現...
}
?>
```

## 重要函數說明

1. generateUserId(): 生成一個唯一的用戶ID。
2. addUserToLDAP($user): 將用戶添加到LDAP系統。
3. addUserToGroup($ldapconn, $userDn, $groupDn, $uniqueUsername): 將用戶添加到LDAP中的特定群組。

## 安全與改進建議
1. 在處理用戶輸入時，應進行嚴格的數據驗證。
2. 使用安全的連接（如LDAPS）與LDAP服務器通訊。
3. 密碼應進行加密處理，避免以明文形式存儲或傳輸。

## LDAP用戶信息陣列解釋

在這段PHP代碼中，我們創建了一個名為`$info`的陣列，用於存儲將要添加到LDAP目錄中的用戶信息。這個陣列包含了多個關鍵字段，這些字段對於LDAP中的用戶來說是必須的或者推薦的。以下是這些字段的具體解釋：

- `cn`: Common Name，這裡使用唯一的用戶名。
- `uid`: User ID，同樣使用唯一的用戶名。
- `sn`: Surname，使用用戶的用戶名。
- `uidNumber`: 用戶的唯一ID號碼。
- `gidNumber`: Group ID Number，這裡設置為一個固定值"1000001"。
- `loginShell`: 用戶的登錄shell，這裡設置為`/bin/sh`。
- `homeDirectory`: 用戶的家目錄路徑，根據唯一用戶名動態生成。
- `mail`: 用戶的電子郵件地址。
- `userPassword`: 用戶的密碼，這裡使用SHA-1加密算法進行加密，並且以`{SHA}`作為前綴。
- `objectclass`: 定義了用戶在LDAP中的對象類別，包括多個類別以支持廣泛的屬性和行為。
- `shadowLastChange`: 密碼最後一次更改的時間，使用自1970年1月1日以來的天數表示。
- `shadowMin`: 密碼最小壽命，設為0表示無限制。
- `shadowMax`: 密碼最大壽命，這裡設置為99999天。
- `shadowWarning`: 密碼到期前的警告天數，設置為7天。
- `shadowExpire`: 账户过期时间，-1表示永不过期。
- `shadowInactive`: 账户在密码过期后仍可用的天数，0表示立即失效。
- `shadowFlag`: 用于控制影子密码的各种标志，这里设置为0。

這個陣列通常用於與`ldap_add`函數一起，將新用戶添加到LDAP目錄中。這些信息幫助LDAP服務器理解和存儲有關用戶的關鍵細節，如身份認證、聯繫方式和帳戶政策等。
