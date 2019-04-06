<?php
    session_start(); 
    $db['host'] = "192.168.99.100:13306";
    $db["user"] = "root";
    $db["pass"] = "root";
    $db["dbname"] = "my_system";

    $errorMessage = "";

    if (isset($_POST["login"])){
        //idチェック
        if(empty($_POST["user_name"])){
            $errorMessage = 'ユーザー名が未入力です';
        }else if(empty($_POST["password"])){
            $errorMessage = 'パスワードが未入力です';
        }
        else{
            $user_name = $_POST["user_name"];

            $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8',$db['host'],$db['dbname']);
            //ユーザ情報チェック
            
            try{
               $pdo= new PDO($dsn,$db["user"], $db["pass"],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

               $stmt = $pdo->prepare('SELECT * FROM Users WHERE User_Name = ?');
               $stmt->execute(array($user_name));

               $password = $_POST["password"];

                    if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        if($password = $row["User_Password"]){
                        session_regenerate_id(true);
                        $_SESSION["NAME"] = $row["User_name"];
                        header("Location: main.php");
                        exit();
                        }else{
                            $errorMessage="えらー";
                        }
                    }
                    else{
                            $errorMessage="なんかまちがってます";
                        }
            } catch (PDOException $e){
                $errorMessage="データベースエラー";
                echo $e->getmessage();
            }
        }
        
    }
?>


<html>
<head>
    <title>ログイン画面</title>
</head>
<body>
<?php echo $errorMessage ?>
    <form id="loginForm" method="POST">
        <p>ユーザー名</p><input type="text" id="user_name" name="user_name">
        <p>password</p><input type="password" id="password" name="password">
        <input type="submit" name="login" value="ログイン">
        <input type="button" value="サインアップ">
    </form>
    
</body>

</html>
