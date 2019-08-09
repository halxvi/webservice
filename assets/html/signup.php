<?php
require_once("config.php");
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
};
function numalphabetchecker($str)
{
    $match = preg_match('/^[a-zA-Z0-9]+$/', $str);
    return  $match == 1  ? true : false;
}
function strlengthchecker($str, $min, $max)
{
    $str =  iconv_strlen($str);
    return ($str >= $min) and $str <= $max  ? true : false;
}
$signupMessage = null;

if (isset($_POST["signup"])) {
    numalphabetchecker($_POST['UserName']) && strlengthchecker($_POST['UserName'], 4, 10) ? $UserName = $_POST['UserName'] : $UserName = false;
    numalphabetchecker($_POST['password']) && strlengthchecker($_POST['password'], 8, 16) ? $Password = Password_hash($_POST["password"], PASSWORD_DEFAULT) : $Password = false;

    if ($UserName && $Password) {
        try {
            $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', dbhostname, dbname);
            $pdo = new PDO($dsn, dbusername, dbpassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $stmt = $pdo->prepare('INSERT INTO Users(UserName,UserPassword) VALUE(?,?)');
            $stmt->execute(array($UserName, $Password));
            $signupMessage = "登録が完了しました";
        } catch (PDOException $e) {
            $message = preg_match('/Duplicate/', $e->getmessage());
            $message == 1 ? $signupMessage = "ユーザー名が既に存在します　別のユーザー名にして下さい" : false;
            echo $e->getmessage();
        }
    } else {
        $signupMessage = "ユーザー名もしくはパスワードが正しく入力されていません";
    }
}
if (isset($_POST["get_back"])) {
    header("Location: login.php");
    exit();
}
?>

<html lang="ja">

<head>
    <title>サインアップ</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<body>
    <?php if (isset($signupMessage)) {
        echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . h($signupMessage) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
    } ?>

    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?>

    <div class="container-fluid bg-light mx-auto" style="width:100%; height:100%;">
        <div class="d-flex align-items-center justify-content-center" style="height:100%">
            <div class="border border-info rounded p-4">
                <div class="d-flex justify-content-center m-4">
                    <h3>サインアップ</h3>
                </div>
                <form id="signupForm" method="POST">
                    <div class="d-flex justify-content-center m-3">
                        <div class="form-group">
                            <label class="font-weight-bold">ユーザー名</label>
                            <input type="text" id="UserName" class="border border-secondary rounded" name="UserName">
                            <small class="form-text font-weight-light text-muted">英数字4字以上・10字以内</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                        <div class="form-group">
                            <label class="font-weight-bold">パスワード</label>
                            <input type="password" id="password" class="border border-secondary rounded" name="password">
                            <small class="form-text font-weight-light text-muted">英数字8字以上・16字以内</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                        <div class="form-group">
                            <input type="submit" name="signup" class="btn btn-info m-3" value="登録">
                            <input type="submit" id="get_back" name="get_back" class="btn btn-outline-info m-3" value="戻る">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>