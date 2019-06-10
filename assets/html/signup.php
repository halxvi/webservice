<?php
session_start();
$db['host'] = "192.168.99.100:13306";
$db["user"] = "root";
$db["pass"] = "root";
$db["dbname"] = "my_system";

$errorMessage = "";
$signupMessage = "";

if (isset($_POST["signup"])) {
    $user_name = $_POST["user_name"];
    $password = Password_hash($_POST["password"], PASSWORD_DEFAULT);
    $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);

    try {
        $pdo = new PDO($dsn, $db["user"], $db["pass"], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $stmt = $pdo->prepare('INSERT INTO Users(User_Name,User_Password) VALUE(?,?)');
        $stmt->execute(array($user_name, $password));
        $signupMessage = "登録が完了しました";
    } catch (PDOException $e) {
        $errorMessage = "データベースエラー";
        echo $e->getmessage();
    }
}
if (isset($_POST["get_back"])) {
    header("Location: login.php");
}

?>

<html>

<head>
    <title>サインアップ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>

<body>
    <h1>サインアップ</h1>
    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES);
    echo htmlspecialchars($signupMessage, ENT_QUOTES);
    ?>
    <form id="signupForm" method="POST">
        <p>ユーザー名(10字以内)</p><input type="text" id="user_name" name="user_name" maxlength="10" required>
        <p>password</p><input type="password" id="password" name="password" required>
        <br>
        <input type="submit" name="signup" class="btn btn-primary" value="登録">
    </form>
    <form method="POST">
        <input type="submit" name="get_back" class="btn btn-outline-primary" value="戻る">
    </form>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>