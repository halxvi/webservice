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
        $password = $_POST["password"];

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $row["UserPassword"])) {
                session_start();
                $_SESSION["Name"] = $row["UserName"];
                $_SESSION["ID"] = $row["UserId"];
                header("Location: main.php");
                exit();
            } else {
                $errorMessage = "ぱすわーどえらー";
            }
        } else {
            $errorMessage = "なんかまちがってます";
        }
    } catch (PDOException $e) {
        $errorMessage = "データベースエラー";
        echo htmlspecialchars($e->getmessage(), ENT_QUOTES);
    }
}
if (isset($_POST["signup"])) {
    header("Location: signup.php");
    exit();
}
?>

<html>

<head>
    <title>ログイン画面</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
</head>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<body>
    <div class="container">
        <h1>ログイン</h1>
        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?>
        <form id="loginForm" method="POST">
            <label class="font-weight-bold">ユーザー名:<input type="text" id="UserName" name="UserName" required></label>
            <br>
            <label class="font-weight-bold">password:<input type="password" id="password" name="password" required></label>
            <br><input type="submit" name="login" class="btn btn-primary" value="ログイン">
        </form>
        <form method="POST">
            <input type="submit" name="signup" class="btn btn-outline-primary" value="サインアップ">
        </form>
    </div>

</body>


</html>