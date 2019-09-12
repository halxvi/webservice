<?php
require_once("config.php");

class SignupController
{
    private $UserMessage = null;

    private function numalphabetchecker($str)
    {
        $match = preg_match('/^[a-zA-Z0-9]+$/', $str);
        return  $match === 1  ? true : false;
    }

    private function strlengthchecker($str, $min, $max)
    {
        $str =  iconv_strlen($str);
        return ($str >= $min) and $str <= $max  ? true : false;
    }

    function signup()
    {
        $UserName = filter_input(INPUT_POST, 'UserName');
        $Password = filter_input(INPUT_POST, 'Password');
        $this->numalphabetchecker($UserName) && $this->strlengthchecker($UserName, 4, 10) ? $UserName = $UserName : $UserName = false;
        $this->numalphabetchecker($Password) && $this->strlengthchecker($Password, 8, 16) ? $Password = password_hash($Password, PASSWORD_DEFAULT) : $Password = false;
        $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', dbhostname, dbname);
        $pdo = new PDO($dsn, dbusername, dbpassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        if ($UserName && $Password) {
            try {
                $stmt = $pdo->prepare('INSERT INTO Users(UserName,UserPassword) VALUE(?,?)');
                $stmt->bindValue(1, $UserName, PDO::PARAM_STR);
                $stmt->bindValue(2, $Password, PDO::PARAM_STR);
                $stmt->execute();
                $this->UserMessage = "登録が完了しました";
            } catch (PDOException $e) {
                $messageflag = preg_match('/Duplicate/', $e->getmessage());
                $messageflag === 1 ? $this->UserMessage = "ユーザー名が既に存在します　別のユーザー名にして下さい" : false;
            }
        } else {
            $this->UserMessage = "ユーザー名もしくはパスワードが正しく入力されていません";
        }
    }

    function getBack()
    {
        header("Location: login.php");
        exit();
    }

    function getUserMessage()
    {
        return $this->UserMessage;
    }

    function hsc($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, "UTF-8", false);
    }
}

$signup = new SignupController();

if (filter_input(INPUT_POST, "Signup")) {
    $signup->signup();
}

if (filter_input(INPUT_POST, "getBack")) {
    $signup->getBack();
}
?>

<html>

<head>
    <title>もくひょうくん</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<body>
    <?php if ($signup->getUserMessage()) {
        echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . $signup->hsc($signup->getUserMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
    } ?>

    <div class="container-fluid bg-light mx-auto" style="width:100%; height:100%;">
        <div class="d-flex align-items-center justify-content-center" style="height:100%">
            <div class="border border-info rounded p-4">
                <div class="d-flex justify-content-center m-4">
                    <h3>サインアップ</h3>
                </div>
                <form id="SignupForm" method="POST">
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
                            <input type="Password" id="Password" class="border border-secondary rounded" name="Password">
                            <small class="form-text font-weight-light text-muted">英数字8字以上・16字以内</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                        <div class="form-group">
                            <input type="submit" name="Signup" class="btn btn-info m-3" value="登録">
                            <input type="submit" id="getBack" name="getBack" class="btn btn-outline-info m-3" value="戻る">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>