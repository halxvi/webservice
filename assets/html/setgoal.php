<?php
require_once("config.php");

class SetGoalController
{
  private $UserMessage = null;
  private $dsn = null;
  private $pdo = null;

  function __construct()
  {
    session_start();
    $this->dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', dbhostname, dbname);
    $this->pdo = new PDO($this->dsn, dbusername, dbpassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  }

  function sendData()
  {
    $Day = (int) date("d");
    $Term = (int) filter_input(INPUT_POST, "term");
    $Month = (int) date("m");
    try {
      $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Tasks WHERE TaskUserId = ? AND EndFlag = 0");
      $stmt->bindValue(1, $_SESSION["ID"], PDO::PARAM_STR);
      $stmt->execute();
      $Recode_Check = $stmt->fetch(PDO::FETCH_NUM);
      if ($Recode_Check[0] == 0) {
        $stmt = $this->pdo->prepare("INSERT INTO Tasks(TaskUserId, Goal, Task, Period, Day, Month) value(:id,:goal,:way,:term,:day,:month)");
        $stmt->bindValue(':id', $_SESSION["ID"], PDO::PARAM_STR);
        $stmt->bindValue(':goal', $_POST["goal"], PDO::PARAM_STR);
        $stmt->bindValue(':way', $_POST["way"], PDO::PARAM_STR);
        $stmt->bindValue(':term', $_POST["term"], PDO::PARAM_INT);
        $stmt->bindValue(':day', $Day, PDO::PARAM_INT);
        $stmt->bindValue(':month', $Month, PDO::PARAM_INT);
        $stmt->execute();
        $this->UserMessage = "目標を登録しました！";
      } else {
        $this->UserMessage = "既に目標があります 現在の目標を削除してください";
      }
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
  }

  function getPDO()
  {
    return $this->pdo;
  }

  function getUserMessage()
  {
    return $this->UserMessage;
  }

  function setUserMessage($str)
  {
    $this->UserMessage = $str;
  }

  function hsc($str)
  {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8", false);
  }
}

$setgoal = new SetGoalController();

if (filter_input(INPUT_POST, "senddata")) {
  $setgoal->sendData();
}

if (filter_input(INPUT_POST, "taskdelete")) {
  try {
    $pdo = $setgoal->getPDO();
    $stmt = $pdo->prepare("UPDATE Tasks SET EndFlag = 1 WHERE EndFlag = 0 AND TaskUserId = ? ");
    $stmt->execute(array($_SESSION["ID"]));
  } catch (PDOException $e) {
    $setgoal->setUserMessage($e->getmessage());
  }
  $setgoal->setUserMessage("目標を削除しました");
}

if (!isset($_SESSION["ID"])) {
  header("location:login.php");
}
?>
<html>

<head>
  <title>もくひょうくん</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/simple-sidebar.css" rel="stylesheet">
</head>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

<body>
  <?php if ($setgoal->getUserMessage()) {
    echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . $setgoal->hsc($setgoal->getUserMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
  } ?>

  <div class="d-flex" id="wrapper">
    <div id="page-content-wrapper">
      <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
            <li class="nav-item active">
              <a class="nav-link" href="main.php">ホーム <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="setgoal.php">目標設定</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="modal" data-target="#logoutModal">ログアウト</a>
            </li>
          </ul>
        </div>
      </nav>

      <div class="container-fluid">
        <form method="POST">
          <p class="m-2">目標</p><input type='text' class="m-1" name='goal'>
          <p class="m-2">達成期間</p>
          <select name="term" class="m-2">
            <option value="7">1週間</option>
            <option value="14">2週間</option>
            <option value="30">1ヶ月</option>
          </select>
          <p class="m-2">１日の量<br><span class="text-muted">例：○○の参考書を１０ページやる</span></p>
          <input type="text" class="m-2" name="way"><br>
          <input type="submit" name="senddata" class="btn btn-primary m-2" value="完了">
          <input type="submit" name="taskdelete" class="btn btn-secondary m-2" value="現在の目標を削除する">
        </form>

      </div>
    </div>
  </div>

  <div class="modal" id="logoutModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>ログアウトしますか？</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="logout()" data-dismiss="modal">はい</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
        </div>
      </div>
    </div>
  </div>

</body>

</html>