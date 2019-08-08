<?php
$db['host'] = "db:3306";
$db["user"] = "root";
$db["pass"] = "root";
$db["dbname"] = "my_system";
$errorMessage = null;
$signupMessage = null;

if (isset($_POST["signup"])) {
    $UserName = $_POST["UserName"];
    $Password = Password_hash($_POST["password"], PASSWORD_DEFAULT);
    $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
    try {
        $pdo = new PDO($dsn, $db["user"], $db["pass"], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $stmt = $pdo->prepare('INSERT INTO Users(UserName,UserPassword) VALUE(?,?)');
        $stmt->execute(array($UserName, $Password));
        $signupMessage = "登録が完了しました";
    } catch (PDOException $e) {
        $errorMessage = "データベースエラー";
        echo $e->getmessage();
    }
}
if (isset($_POST["get_back"])) {
    header("Location: login.php");
    exit();
}
?>

<html>

<head>
    <title>サインアップ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<body>
    <?php if (isset($signupMessage)) {
        echo "<div class='alert alert-primary' role='alert'>" . $signupMessage . "</div>";
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
                            <input type="text" id="UserName" name="UserName" maxlength="10">
                            <small class="form-text text-muted">[10文字以内]</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                        <div class="form-group">
                            <label class="font-weight-bold">パスワード</label>
                            <input type="password" id="password" name="password" minlength="8">
                            <small class="form-text text-muted">[8文字以上]</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                        <div class="form-group">
                            <input type="submit" name="signup" class="btn btn-info m-3" value="登録">
                            <input type="submit" name="get_back" class="btn btn-outline-info m-3" value=" 戻る">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>