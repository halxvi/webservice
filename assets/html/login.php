<?php
    session_start(); 
    $db['host'] = "192.168.99.100:13306";
    $db["user"] = "root";
    $db["pass"] = "root";
    $db["dbname"] = "my_system";

    $errorMessage = "";

    if (isset($_POST["login"])){
        //idチェック
            $user_name = $_POST["user_name"];

            $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8',$db['host'],$db['dbname']);
            //ユーザ情報チェック
            
            try{
               $pdo= new PDO($dsn,$db["user"], $db["pass"],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

               $stmt = $pdo->prepare('SELECT * FROM Users,Tasks WHERE User_Name = ?');
               $stmt->execute(array($user_name));
               $password = $_POST["password"];

                    if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        if(password_verify($password, $row["User_Password"])){
                        session_regenerate_id(true);
                        $_SESSION["NAME"] = $row["User_name"];
                        $_SESSION["ID"]=$row["User_Id"];
                        header("Location: main.php");
                        exit();
                        }else{
                            $errorMessage="ぱすわーどえらー";
                        }
                    }
                    else{
                            $errorMessage="なんかまちがってます";
                        }
            } catch (PDOException $e){
                $errorMessage="データベースエラー";
                echo htmlspecialchars($e->getmessage(),ENT_QUOTES);
            }
        
    }
    if(isset($_POST["signup"])){
        header("Location: signup.php");
        exit();
    }
?>


<html>
<head>
    <title>ログイン画面</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>

<body>
<?php echo htmlspecialchars( $errorMessage,ENT_QUOTES); ?>
    <form id="loginForm" method="POST">
        <p>ユーザー名</p><input type="text" id="user_name" name="user_name" required>
        <p>password</p><input type="password" id="password" name="password" required>
        <input type="submit" name="login" class="btn btn-primary" value="ログイン">
    </form>
    <form method="POST"><input type="submit" name="signup" class="btn btn-outline-primary" value="サインアップ"></form>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>


</html>
