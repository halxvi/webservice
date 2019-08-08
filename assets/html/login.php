<?php
$db['host'] = "db:3306";
$db["user"] = "root";
$db["pass"] = "root";
$db["dbname"] = "my_system";
$errorMessage = null;

if (isset($_POST["login"])) {
    $UserName = $_POST["UserName"];
    $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
    try {
        $pdo = new PDO($dsn, $db["user"], $db["pass"], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $stmt = $pdo->prepare('SELECT * FROM Users WHERE UserName = ?');
        $stmt->execute(array($UserName));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $password = $_POST["password"];

        if (password_verify($password, $row["UserPassword"])) {
            session_start();
            $_SESSION["Name"] = $row["UserName"];
            $_SESSION["ID"] = $row["UserId"];
            header("Location: main.php");
            exit();
        } else {
            $errorMessage = "ぱすわーどえらー";
        }
    } catch (PDOException $e) {
        $errorMessage = "データベースエラー";
        echo htmlspecialchars($e->getmessage(), ENT_QUOTES);
        exit();
    }
}
if (isset($_POST["signup"])) {
    header("Location: signup.php");
    exit();
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
    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?>
    <div class="container-fluid bg-light mx-auto" style="width:100%; height:100%;">
        <div class="d-flex align-items-center justify-content-center" style="height:100%">
            <div class="border border-info rounded p-4">
                <div class="d-flex justify-content-center m-3">
                    <h3>ログイン<h3>
                </div>
                <form id="loginForm" method="POST">
                    <label class="font-weight-bold m-2">ユーザー名</label>
                    <input type="text" id="UserName" name="UserName">
                    <br>
                    <label class="font-weight-bold m-2">password</label>
                    <input type="password" id="password" name="password">
                    <br>
                    <input type="submit" name="login" class="btn btn-info m-3" value="ログイン">
                    <input type="submit" name="signup" class="btn btn-outline-info m-3" value="サインアップ">
                </form>
            </div>
        </div>
    </div>
    </div>
</body>


</html>