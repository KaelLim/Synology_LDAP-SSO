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

# Synology SSO Server 設定

本文檔旨在指導開發者如何設定SSO 及 取得 app id

## 前提條件

- LDAP Server設定完成
- `網域/LDAP`連線狀態已連線 (Synology LDAP Server)

## 步驟 1：安裝 SSO Server

前往套件中心，在搜尋中輸入SSO Server (它沒有中文名稱)，點擊安裝套件，需同意的按鈕都給他按下去。

## 步驟 2：設定 SSO Server

![1](https://imagedelivery.net/JVmYbduioNVkRm0SvNGcew/689de628-34df-48f8-86a1-2340835e9c00/Desktop "結果圖示")
1. 前往SSO Server
2. 一般設定 > 登入設定中點擊`設定`
3. 在網頁服務中別名請自行設定 (範例為SSO-signin)
4. SSO Server 中，將帳行類別設定為`網域/LDAP`，`yourDSM/SSO-sign`設定在伺服器url

![2](https://imagedelivery.net/JVmYbduioNVkRm0SvNGcew/1c393114-efb9-4dc1-1b55-9a8c0d1fd800/Desktop "結果圖示")

5. 選擇 `Synology SSO` 並點擊`下一步`
6. 應用程式名稱可隨意，自己知道就好。重新導向URI以個人開發的註冊網址為主，並點擊`完成`
7. 新增完成後會提供應用程式ID (App ID)
8. 取得App ID 後就能進入下個章節

# 整合 Synology SSO 至您的應用

本文檔旨在指導開發者如何在他們的應用中整合 Synology 單一登入（SSO）服務，以便使用者能夠通過 Synology DSM 的認證來登入應用。

## 前提條件

- 已在您的網絡環境中安裝 Synology DiskStation Manager (DSM)。
- 在 DSM 中已安裝並啟用 SSO Server。

## 開始之前

在開始整合過程之前，請確保您已經在 DSM 上成功安裝了 SSO Server。安裝 SSO Server 後，您將自動擁有 JavaScript SDK，該 SDK 位於您的 DSM 服務器上，可通過以下 URL 訪問：https://yourDSM:5000/webman/sso/synoSSO-1.0.0.js

請將 `yourDSM` 替換成您的 DSM 服務器的地址。

## 步驟 1：初始化 SDK

在您的 HTML 文件或 Web 應用中引入 SDK：

```html
<script type="text/javascript" src="https://yourDSM:5000/webman/sso/synoSSO-1.0.0.js"></script>
```
## 步驟 2：處理登入
在初始化 SDK 之後，您可以使用 SYNOSSO.login 方法來觸發登入流程：
```javascript
SYNOSSO.login();
```
這將會打開一個新的窗口，引導用戶完成 DSM 的登入流程。


## 步驟 3：處理回調
登入成功或失敗後，SDK 將會呼叫您在初始化時提供的 authCallback 函數。您需要在這個函數中處理登入後的邏輯：

```javascript
function authCallback(response) {
    if(response.status === 'login') {
        // 處理登入成功的情況
        console.log('登入成功，Access Token:', response.access_token);
    } else {
        // 處理登入失敗或未登入的情況
        console.log('登入狀態:', response.status);
    }
}
```

## 結果
通過以上步驟，Synology SSO 服務整合到應用中，點擊SSO登入結果如下
![3](https://imagedelivery.net/JVmYbduioNVkRm0SvNGcew/8ff20108-065f-4e65-63e9-2d2488cc9500/Desktop "結果圖示")

## 注意事項
Synology SSO 服務使用`網域/LDAP`，所以登入帳號務必使用 `LDAP` 內，非Synology DSM 帳號。個人之前不斷使用DSM帳號登入，還以為SSO出bug了，一直不提供我權限😅:。

## 遇到的問題
目前還不清楚SSO SDK 中的redirect_uri是幹嘛 :disappointed_relieved:。若有任何大佬能提供教學會更好
