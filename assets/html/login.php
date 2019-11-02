<?php
require_once("db.php");

class Login
{
    private $UserMessage = null;

    function Login()
    {
        $UserName = filter_input(INPUT_POST, "UserName");
        $db = new DB();
        $pdo = $db->getPDO();
        try {
            $stmt = $pdo->prepare('SELECT * FROM Users WHERE UserName = ?');
            $stmt->execute(array($UserName));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $Password = filter_input(INPUT_POST, "Password");
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
            $this->UserMessage = "データベースエラー :" . $e->getMessage();
        }
    }

    function Signup()
    {
        header("Location: signup.php");
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

$Login = new Login();

if (filter_input(INPUT_POST, "Login")) {
    $Login->Login();
}

if (filter_input(INPUT_POST, "goSignup")) {
    $Login->Signup();
}
?>

<html>

<head>
    <title>もくひょうくん</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- <link href="css/color.scss" type="text/scss" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<body>

    <?php if ($Login->getUserMessage()) {
        echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . $Login->hsc($Login->getUserMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
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
                            <input type="submit" name="goSignup" class="btn btn-outline-info m-3" value="サインアップ">
                        </div>
                    </div>
            </div>
        </div>
    </div>
</body>

</html>