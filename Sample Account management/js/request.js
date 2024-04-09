function sendRequest(action, username, groupName) {
    console.log("發送請求", action, username, groupName);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "api/user_action.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("響應數據", this.responseText);
            if (action === "approve") {
                updatePageAfterApprove(username);
            } else if (action === "reject") {
                updatePageAfterReject(username);
            }
        }
    };
    xhr.send(`action=${action}&username=${username}&groupName=${groupName}`);
}

function updatePageAfterApprove(username) {
    var userRow = document.getElementById(`user_${username}`);
    if (userRow) {
        userRow.remove();
    }
}

function updatePageAfterReject(username) {
    var userRow = document.getElementById(`user_${username}`);
    if (userRow) {
        userRow.remove();
    }
}

function approveUser(username, phone) {
var groupName = document.querySelector(`input[name='group_${username}']:checked`).value;
if (confirm("確定要通過用戶 " + username  + phone + " 的申請嗎？")) {
    sendRequest("approve", username, groupName);
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

function deleteUser(userId) {
if (confirm('確定要刪除這個用戶嗎？')) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/delete_user.php?user_id=' + userId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                console.log('Response:', response);
                if (response.success) {
                    alert('用戶已成功刪除');
                    // 這裡可以添加刷新頁面或更新UI的代碼
                } else {
                    console.error('Error:', response.error);
                    alert('刪除用戶失敗: ' + response.error);
                }
            } catch (e) {
                console.error('Error parsing JSON response:', e);
            }
        } else {
            console.error('Request failed. Returned status of ' + xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Request failed due to network error.');
    };
    xhr.send();
}
}