<?php
require_once("config.php");
class LoginController
{
    public $UserMessage = null;

    function h($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
    }

    function Login()
    {
        $UserName = null;
        $Password = null;
        $UserName = $_POST["UserName"];
        $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', dbhostname, dbname);
        try {
            $pdo = new PDO($dsn, dbusername, dbusername, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $stmt = $pdo->prepare('SELECT * FROM Users WHERE UserName = ?');
            $stmt->execute(array($UserName));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $Password = $_POST["Password"];

            if ($UserName = $row["UserName"] && password_verify($Password, $row["UserPassword"])) {
                session_start();
                $_SESSION["Name"] = $row["UserName"];
                $_SESSION["ID"] = $row["UserId"];
                header("Location: main.php");
                exit();
            } else {
                $this->UserMessage = "ユーザー名またはパスワードが間違っています";
            }
        } catch (PDOException $e) {
            $this->UserMessage = "サーバーエラー" . $e;
        }
    }
    function Signup()
    {
        header("Location: signup.php");
        exit();
    }
}

$Login = new LoginController();
if (isset($_POST["Login"])) {
    $Login->Login();
}
if (isset($_POST["Signup"])) {
    $Login->Signup();
}
?>

<html>

<head>
    <title>ログイン</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="vendor/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="css/color.scss" type="text/scss" rel="stylesheet">
</head>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<body>

    <?php if (isset($Login->UserMessage)) {
        echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . $Login->h($Login->UserMessage) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
    } ?>


    <div class="container-fluid bg-light" style="height:100%;">
        <div class="d-flex align-items-center justify-content-center" style="height:100%">
            <div class="border border-info rounded p-4">
                <div class="d-flex justify-content-center m-4">
                    <h3>ログイン<h3>
                </div>
                <form id="LoginForm" method="POST">
                    <div class="d-flex justify-content-center m-3">
                        <div class="form-group">
                            <label class="font-weight-bold">ユーザー名</label>
                            <input type="text" id="UserName" class="border border-secondary rounded" name="UserName">
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                        <div class="form-group">
                            <label class="font-weight-bold">パスワード</label>
                            <input type="Password" id="Password" class="border border-secondary rounded" name="Password">
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                        <div class="form-group">
                            <input type="submit" name="Login" class="btn btn-info m-3" value="ログイン">
                            <input type="submit" name="Signup" class="btn btn-outline-info m-3" value="サインアップ">
                        </div>
                    </div>
            </div>
        </div>
    </div>
</body>

</html>