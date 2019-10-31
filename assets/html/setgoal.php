<?php
//require_once("config.php");

class SetGoalController
{
  private $UserMessage = null;
  private $dsn = null;
  private $pdo = null;

  function __construct()
  {
    session_start();
    $dbENV = parse_url($_SERVER["CLEARDB_DATABASE_URL"]);
    $dbENV['dbname'] = ltrim($dbENV['path'], '/');
    $this->dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $dbENV['host'], $dbENV['dbname']);
    $this->pdo = new PDO($this->dsn, $dbENV['user'], $dbENV['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  }

  function sendData()
  {
    $Day = (int) date("Ymd");
    try {
      $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Tasks WHERE TaskUserId = ? AND EndFlag = 0");
      $stmt->bindValue(1, $_SESSION["ID"], PDO::PARAM_STR);
      $stmt->execute();
      $Recode_Check = $stmt->fetch(PDO::FETCH_NUM);
      if ($Recode_Check[0] == 0) {
        $stmt = $this->pdo->prepare("INSERT INTO Tasks(TaskUserId, Goal, Task, Period) value(:id,:goal,:way,:term)");
        $stmt->bindValue(':id', $_SESSION["ID"], PDO::PARAM_STR);
        $stmt->bindValue(':goal', filter_input(INPUT_POST, "goal"), PDO::PARAM_STR);
        $stmt->bindValue(':way', filter_input(INPUT_POST, "way"), PDO::PARAM_STR);
        $stmt->bindValue(':term', filter_input(INPUT_POST, "term"), PDO::PARAM_INT);
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

if (!$_SESSION["ID"]) {
  header("location:login.php");
}
?>
<html>

<head>
  <title>もくひょうくん</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/simple-sidebar.css" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="js/main.js"></script>

<body>
  <?php if ($setgoal->getUserMessage()) {
    echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . $setgoal->hsc($setgoal->getUserMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
  } ?>

  <div class="d-flex" id="wrapper">
    <div id="page-content-wrapper" class="w-100">
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