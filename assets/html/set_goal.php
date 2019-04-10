<?php
    session_start();
    $db['host'] = "192.168.99.100:13306";
    $db["user"] = "root";
    $db["pass"] = "root";
    $db["dbname"] = "my_system";

    $errorMessage = "";
    $userMessage = "";

    $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8',$db['host'],$db['dbname']);

    if(isset($_POST["back_page"])){
        header("Location: main.php");
    }

    if(isset($_POST["ok"])){
        if(isset($_POST["object"])){
            $object_html ='value="'.htmlspecialchars($_POST["object"]).'"';
        }
        if(isset($_POST["term"])){
             if($_POST["term"] == "7"){     //数字は日数を意味する一週間なら7日
                $Check_1w = "selected";
             } 
             elseif($_POST["term"] == "14"){
                $Check_2w = "selected";
             }
             elseif($_POST["term"] == "31"){
                $Check_1m = "selected";
             }
        }
        if(isset($_POST["quantitiy"])){
            $quantitiy_html ='value="'.htmlspecialchars($_POST["quantitiy"]).'"';
        }
        if(isset($_POST["way"])){
            $way_html ='value="'.htmlspecialchars($_POST["way"]).'"';
        }
    }

    if(isset($_POST["send_data"])){
        try{
            $Start_Date = date("Y-m-d H:i:s");

            $pdo = new PDO($dsn,$db['user'],$db['pass'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
            $stmt = $pdo->prepare("INSERT INTO Tasks(Task_Id, Goal, Task,Way, Period, Start_Date) value(?,?,?,?,?,?)");
            $stmt->execute(array($_SESSION["ID"],$_POST["object"],$_POST["quantitiy"],$_POST["way"],$_POST["term"],$Start_Date));
            $userMessage = "登録しました！";
            echo htmlspecialchars($userMessage,ENT_QUOTES);

        }catch(PDOException $e){
            $errorMessage = $e->getmessage();
            echo htmlspecialchars($errorMessage,ENT_QUOTES);
        }
    }
    
?>

<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <form method="POST">
        <input type='text' name='object' <?php echo $object_html?>>
        <select name="term">
        <option value="7" <?php echo $Check_1w?>>1週間</option>
        <option value="14" <?php echo $Check_2w?>>2週間</option>
        <option value="31" <?php echo $Check_1m?>>1ヶ月</option>
        </select>
        <input type="text" name="quantitiy" <?php echo $quantitiy_html?>>
        <input type="text" name="way" <?php echo $way_html?>>
        <input type="submit" name="ok" value="OK!">
        <input type="submit" name="back" value="前に戻る">
        <input type="submit" name="send_data" value="完了">
    </form>
    <input type="button" id="back_page" value="トップページに戻る">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        var Button_Clicked = document.getElementById("back_page");
        function go_page(){
            location.replace("main.php");
        };
        Button_Clicked.addEventListener("click",go_page);
    </script>
</body>
</html>