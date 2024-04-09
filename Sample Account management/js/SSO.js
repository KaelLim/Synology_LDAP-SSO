document.addEventListener("DOMContentLoaded", function() {
    SYNOSSO.init({
        oauthserver_url: 'https://taipei-3in1-nas.synology.me:6322',
        app_id: '5dc136ee532e4adb3314c6a9f9e5e7ea',
        redirect_uri: 'http://localhost:8000/form.php', // 確保這個 URI 能夠正確重定向到你的服務器
        callback: authCallback
    });

    var loginButton = document.getElementById("login-button");
    loginButton.addEventListener('click', function() {
        SYNOSSO.login();
    });

    function authCallback(response) {
        if('login' === response.status) {
            getUserInfo(response.access_token);
        }
    }

    function getUserInfo(accessToken) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'api/login_backend.php?accesstoken=' + accessToken, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var userInfo = JSON.parse(xhr.responseText);
                    if (userInfo && userInfo.data && userInfo.data.user_id && userInfo.data.user_name) {
                        checkAccess(userInfo.data.user_id, userInfo.data.user_name);
                    } else {
                        alert("未能正確獲取用戶信息。");
                    }
                } catch (e) {
                    console.error('Error parsing user info response: ', e);
                    alert('Error processing user info response.');
                }
            } else {
                alert('User info request failed. Returned status of ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            alert('User info request failed due to network error.');
        };
        xhr.send();
    }

    function checkAccess(userId, userName) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'api/get_access.php?user_id=' + encodeURIComponent(userId) + '&username=' + encodeURIComponent(userName), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var accessInfo = JSON.parse(xhr.responseText);
                    if (accessInfo.groups && accessInfo.groups.length > 0) {
                        var canReview = accessInfo.groups.some(group => group.includes("合心管理"));
                        if (canReview) {
                            window.location.href = 'http://localhost:8000/form.php'; // 轉到審核頁面
                        } else {
                            alert("您不屬於合心管理群組，無法訪問此頁面。");
                            SYNOSSO.logout();
                        }
                    } else {
                        alert("您沒有所屬的群組信息。");
                        SYNOSSO.logout();
                    }
                } catch (e) {
                    console.error('Error parsing access check response: ', e);
                    alert('Error processing access check response.');
                }
            } else {
                alert('Access check request failed. Returned status of ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            alert('Access check request failed due to network error.');
        };
        xhr.send();
    }
});
