$('#logout').click(function () {
    var result = window.confirm("ログアウトしますか？");
    if (result) {
        window.location.href = "logout.php";
    } else {
        window.location.href = "main.php";
    }
});

