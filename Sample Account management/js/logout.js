document.addEventListener("DOMContentLoaded", function() {
    var logoutButton = document.getElementById("logout-button");
    logoutButton.addEventListener('click', function() {
        if (confirm("您確定要登出嗎？")) {
            logoutAndClear();
        }
    });

    function logoutAndClear() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'api/logout.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert('您已成功登出，將跳轉回登入頁面。');
                window.location.href = 'index.php';
            } else {
                alert('登出失敗，請重試。');
            }
        };
        xhr.onerror = function() {
            alert('網路錯誤，登出失敗。');
        };
        xhr.send();
    }
});