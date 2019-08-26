<?php
require_once("config.php");

session_start();

class DBContoller
{
  private $UserMessage = null;
  private $task = null;
  private $goal = null;
  private $days = null;
  private $datenow  = null;
  private $koushin = null;
  private $dateout = null;
  private $dsn = null;
  private $pdo = null;
  private $row = null;

  function __construct()
  {
    $this->dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', dbhostname, dbname);
    $this->pdo = new PDO($this->dsn, dbusername, dbpassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    try {
      $stmt = $this->pdo->prepare("SELECT * FROM Users,Tasks WHERE UserId = ? AND Users.UserId = Tasks.TaskUserId AND Tasks.EndFlag =0");
      $stmt->execute(array($_SESSION["ID"]));
      $this->row = $stmt->fetch(PDO::FETCH_ASSOC);
      $this->datenow  = (int) date("Ymd");
      $this->koushin = $this->datenow  + 1 - $this->row["TaskCounter"] - $this->row["StartDate"];
      $this->dateout = $this->datenow  - $this->row["EndDate"];
      if (isset($this->row["Goal"])) {
        $this->goal = sprintf("現在の目標は%sです", $this->row["Goal"]);
        $this->days = sprintf("%s日継続中です", $this->row["TaskCounter"]);
      } else {
        $this->UserMessage = sprintf("ようこそ%sさん\n目標を作りましょう", $_SESSION["Name"]);
      }
      if (isset($this->row["Task"])) {
        preg_match("/[0-9０－９]+/", $this->row["Task"], $today_task_num);
        preg_match_all("/[^0-9]+/", $this->row["Task"], $today_task_stmt, PREG_SET_ORDER);
        $today_task_num[0] = ceil((int) $today_task_num[0] / (int) $this->row["Period"]);
        $this->task = sprintf("やるべきこと：%s", $today_task_stmt[0][0] . $today_task_num[0] . $today_task_stmt[1][0]);
      }
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
  }

  function endTask()
  {
    if ($this->dateout > 1) {
      $this->UserMessage = "目標達成予定日を過ぎてしまいました 新しい目標を設定してください";
      $this->deleteGoal();
    }
    if ($this->koushin > 1) {
      try {
        $AddCounter = $this->koushin;
        $stmt = $this->pdo->prepare("UPDATE Tasks SET TaskCounter =? WHERE EndFlag =0");
        $stmt->execute(array($AddCounter));
        $this->UserMessage = "予定より" . $this->koushin . "日遅れています";
      } catch (PDOException $e) {
        $this->UserMessage = $e->getmessage();
      }
    } elseif ($this->koushin == 1) {
      try {
        $AddCounter = $this->koushin;
        $stmt = $this->pdo->prepare("UPDATE Tasks SET TaskCounter =? WHERE EndFlag =0");
        $stmt->execute(array($AddCounter));
        $this->UserMessage = "今日もお疲れ様です！";
      } catch (PDOException $e) {
        $this->UserMessage = $e->getmessage();
      }
    } elseif ($this->koushin == 0) {
      $this->UserMessage = "今日の分は終わっています";
    }
  }

  function checkGoal()
  {
    try {
      $this->deleteGoal();
      $this->UserMessage = "おめでとうございます！目標を達成しました！";
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
  }

  function deleteGoal()
  {
    try {
      $stmt = $this->pdo->prepare("UPDATE Tasks SET EndFlag = 1 WHERE EndFlag = 0 AND TaskUserId = ?");
      $stmt->execute(array($_SESSION["ID"]));
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
  }

  function getUserMessage()
  {
    return $this->UserMessage;
  }

  function getGoal()
  {
    return $this->goal;
  }

  function getTask()
  {
    return $this->task;
  }

  function getDays()
  {
    return $this->days;
  }

  function getkoushin()
  {
    return $this->koushin;
  }

  function getRow($str)
  {
    return $this->row[$str];
  }

  function hsc($str)
  {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8", false);
  }
}

$db = new DBContoller();

if ($db->getRow("TaskNo")) {
  filter_input(INPUT_POST, "endTask") ? $db->endTask() : false;
}

if ($db->getRow("TaskCounter") == $db->getRow("Period") && $db->getkoushin() == 0) {
  $db->checkGoal();
}
?>

<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/simple-sidebar.css" rel="stylesheet">
</head>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

<body>
  <?php if ($db->getUserMessage()) {
    echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . $db->hsc($db->getUserMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
  }
  ?>

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
              <a class="nav-link" href="set_goal.php">目標作成</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="modal" data-target="#logoutModal">ログアウト</a>
            </li>
          </ul>
        </div>
        <div class="d-flex align-items-center justify-content-center m-1">
          <form class="m-0" method="POST">
            <input type="submit" name="endTask" class="btn btn-primary" value="今日の分終了！">
          </form>
        </div>
      </nav>
    </div>
  </div>

  <div id="carouselExampleControls" class="carousel slide" data-ride="carousel" style="height:100%">
    <div class="carousel-inner" style="height:100%">
      <?php
      if ($db->getGoal()) {
        echo "<div class='carousel-item active' style='height:100%'><div class='font-weight-light align-items-center justify-content-center' style='height:100%' alt='First slide'>" . $db->getGoal() . "</div></div>";
      }
      if ($db->getTask()) {
        echo "<div class='carousel-item align-items-center justify-content-center' style='height:100%'><div class='font-weight-light text-center d-block w-100' alt='Second slide'>" . $db->getTask() . "</div></div>";
      }
      if ($db->getDays()) {
        echo "<div class='carousel-item align-items-center justify-content-center' style='height:100%'><div class='font-weight-light text-center d-block w-100' alt='Second slide'>" . $db->getDays() . "</div></div>";
      }
      ?>
    </div>
    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
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