function editUserInfo(button) {
    console.log("编辑用户信息按钮被点击");
    // 獲取用戶數據
    var userId = button.getAttribute('data-user-id');
    var username = button.getAttribute('data-username');
    var phone = button.getAttribute('data-phone');
    var email = button.getAttribute('data-email');
    var place = button.getAttribute('data-place');
    var group = button.getAttribute('data-group');
    var oldUsername = button.getAttribute('data-old-username');
    var oldPhone = button.getAttribute('data-old-phone');

    // 將獲取的數據填充到模態框中的表單
    document.getElementById('userInfoUserId').value = userId;
    document.getElementById('userInfoUserName').value = username;
    document.getElementById('userInfoUserPhone').value = phone;
    document.getElementById('userInfoUserEmail').value = email;
    document.getElementById('userInfoUserPlace').value = place; 
    document.getElementById('userInfoUserGroup').value = group; 
    document.getElementById('oldUsername').value = oldUsername;
    document.getElementById('oldPhone').value = oldPhone;

    var modal = document.getElementById('editUserInfoModal'); // 使用對應用戶信息編輯的模態框ID
    modal.style.display = "block";
}

// 用户群组编辑
function editUserGroup(button) {
    var userId = button.getAttribute('data-user-id');
    var place = button.getAttribute('data-place');
    var groupOld = button.getAttribute('data-group-old');
    var group = button.getAttribute('data-group');

    document.getElementById('userGroupUserId').value = userId;
    document.getElementById('userGroup').value = group;
    document.getElementById('userGroupOld').value = groupOld;
    document.getElementById('userGroupUserPlace').value = place;

    var modal = document.getElementById('editUserGroupModal');
    modal.style.display = "block";
}

var closeButtons = document.querySelectorAll('.close');
closeButtons.forEach(function(button) {
    button.onclick = function() {
        var modal = button.closest('.modal');
        modal.style.display = "none";
    };
});

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
    }
};

document.getElementById('editUserInfoForm').onsubmit = function(event) {
    event.preventDefault(); // 防止表單通過標準表單提交過程提交
    let formData = new FormData(this);

    fetch('api/edit_user_info.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('用户信息更新成功');
            document.getElementById('editUserInfoModal').style.display = "none";
        } else {
            throw new Error('更新用户信息失败: ' + data.error);
        }
    })
    .catch(error => {
        console.error('请求失败或解析错误:', error);
    });
};

document.getElementById('editUserGroupForm').onsubmit = function(event) {
    event.preventDefault(); // 防止表單通過標準表單提交過程提交
    let formData = new FormData(this);

    fetch('api/edit_user_group.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('用户群组更新成功');
            document.getElementById('editUserGroupModal').style.display = "none";
        } else {
            throw new Error('更新用户群组失败: ' + data.error);
        }
    })
    .catch(error => {
        console.error('请求失败或解析错误:', error);
    });
};

