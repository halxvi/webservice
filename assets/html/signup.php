<?php
    session_start(); 
    $db['host'] = "192.168.99.100:13306";
    $db["user"] = "root";
    $db["pass"] = "root";
    $db["dbname"] = "my_system";

    $errorMessage = "";
    $signupMessage= "";

    if (isset($_POST["signup"])){
        
            $user_name = $_POST["user_name"];
            $password = Password_hash($_POST["password"],PASSWORD_DEFAULT);
            $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8',$db['host'],$db['dbname']);
            
            try{
               $pdo= new PDO($dsn,$db["user"], $db["pass"],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

               $stmt = $pdo->prepare('INSERT INTO Users(User_Name,User_Password) VALUE(?,?)');
               $stmt->execute(array($user_name,$password));

                $signupMessage = "登録が完了しました";
                
            } catch (PDOException $e){
                $errorMessage="データベースエラー";
                echo $e->getmessage();
            }
        }
        if(isset($_POST["get_back"])){
            header("Location: login.php");
        }
        
?>


<html>
<head>
    <title>サインアップ画面</title>
</head>
<body>
<?php echo htmlspecialchars( $errorMessage,ENT_QUOTES);
echo htmlspecialchars( $signupMessage,ENT_QUOTES);
 ?>
    <form id="signupForm" method="POST">
        <p>ユーザー名</p><input type="text" id="user_name" name="user_name" required>
        <p>password</p><input type="password" id="password" name="password" required>
        <input type="submit" name="signup" value="登録">
    </form>
    <form method="POST">
        <input type="submit" name="get_back" value="戻る">
    </form>
    
</body>

</html>
