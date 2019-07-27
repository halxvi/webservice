<html>
<meta charset="utf-8">
<script>
    var result = window.confirm('ログアウトしますか？');
    if (result) {
        <?php session_abort(); ?>
        location.href = "login.php";
    }
</script>

</html>