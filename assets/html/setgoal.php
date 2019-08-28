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
    $StartDate = (int) date("Ymd");
    $AlterTerm = (int) $_POST["term"];
    $EndDate = time() + ($AlterTerm * 24 * 60 * 60);
    $EndDate = (int) date("Ymd", $EndDate);
    try {
      $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Tasks WHERE TaskUserId = ? AND EndFlag = 0");
      $stmt->execute(array($_SESSION["ID"]));
      $Recode_Check = $stmt->fetch(PDO::FETCH_NUM);
      if ($Recode_Check[0] == 0) {
        $stmt = $this->pdo->prepare("INSERT INTO Tasks(TaskUserId, Goal, Task,Way, Period, StartDate, EndDate) value(?,?,?,?,?,?,?)");
        $stmt->execute(array($_SESSION["ID"], $_POST["object"], $_POST["quantitiy"], $_POST["way"], $_POST["term"], $StartDate, $EndDate));
        $this->UserMessage = "登録しました！";
      } else {
        $this->UserMessage = "既に目標があります 現在の目標を削除してください";
      }
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
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

if (filter_input(INPUT_POST, "TaskDeleteFlag") === 1) {
  try {
    $stmt = $pdo->prepare("UPDATE Tasks SET EndFlag = 1 WHERE EndFlag = 0 AND TaskUserId = ? ");
    $stmt->execute(array($_SESSION["ID"]));
  } catch (PDOException $e) {
    $this->UserMessage = $e->getmessage();
  }
  $setgoal->setUserMessage("目標を削除しました");
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
              <a class="nav-link" href="setgoal.php">目標作成</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="modal" data-target="#logoutModal">ログアウト</a>
            </li>
          </ul>
        </div>
      </nav>

      <div class="container-fluid">
        <form method="POST">
          <p>目標</p><input type='text' name='object' required>
          <p>達成期間</p>
          <select name="term">
            <option value="7">1週間</option>
            <option value="14">2週間</option>
            <option value="30">1ヶ月</option>
          </select>
          <p>達成手段<br><span class="text-muted">具体的な数字を含めるようにしてください　例：テキストを１００ページやる</span></p>
          <input type="text" name="quantitiy">
          <p>達成手順</p><input type="text" name="way">
          <input type="submit" name="senddata" class="btn btn-primary" value="完了">
        </form>
        <input type="submit" name="taskdelete" class="btn btn-secondary" data-toggle="modal" data-target="#taskdeleteModal" value="現在の目標を削除する">
      </div>
    </div>
  </div>

  <div class="modal" id="taskdeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>現在の目標を削除しますか？</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="taskDelete()">はい</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
        </div>
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
          <button type="button" class="btn btn-primary" onclick="logout()">はい</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
        </div>
      </div>
    </div>
  </div>

</body>

</html>