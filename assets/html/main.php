<?php
require_once("db.php");
require_once("calendar.php");

class Main
{
  private $UserMessage = null;
  private $task = null;
  private $goal = null;
  private $days = null;
  private $date  = null;
  private $pdo = null;
  private $row = null;

  function __construct()
  {
    session_start();
    $db = new DB();
    $this->pdo = $db->getPDO();
    $this->date  = date("Y-n-d");
    $this->getTable();
    if (isset($this->row["Goal"])) {
      $this->goal = sprintf("現在の目標は%sです", $this->row["Goal"]);
      if ($this->row["TaskCounter"] != 0) {
        $this->days = sprintf("%s日継続中です", $this->row["TaskCounter"]);
      }
    } else {
      $this->UserMessage = sprintf("ようこそ%sさん\n目標を作りましょう", $_SESSION["Name"]);
    }
    if ($this->row["Task"] != '') {
      $this->task = sprintf("今日やること：%s", $this->row["Task"]);
    }
  }

  function endTask()
  {
    $stmt = $this->pdo->prepare("SELECT * FROM Counter WHERE TaskNo = ?");
    $stmt->execute(array($this->row['TaskNo']));
    $counter = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo $counter;
    // for ($i = 0; $i < length($counter); $i++) {
    //   if ($counter[$i] != $this->date) {
    //     echo  1;
    //   } else {
    //     echo  0;
    //   }
    // }
    // if ($flag == 1) {
    //   $this->commitTask();
    // } else {
    //   $this->UserMessage = "今日の分は終わっています";
    // }
  }

  private function commitTask()
  {
    $this->UserMessage = "今日もお疲れ様です！";
    $this->setCounter();
    $this->getTable();
    $this->days = sprintf("%s日継続中です", $this->row["TaskCounter"]);
  }

  function checkGoal()
  {
    if ($this->getRow("TaskCounter") === $this->getRow("Period")) {
      $this->deleteGoal();
      $this->UserMessage = "おめでとうございます！目標を達成しました！";
    }
  }

  private function deleteGoal()
  {
    try {
      $stmt = $this->pdo->prepare("UPDATE Tasks SET EndFlag = 1 WHERE EndFlag = 0 AND TaskUserId = ?");
      $stmt->execute(array($_SESSION["ID"]));
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
  }

  private function getTable()
  {
    try {
      $stmt = $this->pdo->prepare("SELECT * FROM Users,Tasks WHERE Users.UserId = ? AND Users.UserId = Tasks.TaskUserId AND Tasks.EndFlag = 0");
      $stmt->execute(array($_SESSION["ID"]));
      $this->row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
  }

  private function setCounter()
  {
    $stmt = $this->pdo->prepare("INSERT INTO Counter(UserId,TaskNo,Date) value(:UserId,:TaskNo,:Date)");
    $stmt->bindValue(1, $_SESSION["ID"], PDO::PARAM_INT);
    $stmt->bindValue(2, $this->row["TaskNo"], PDO::PARAM_INT);
    $stmt->bindValue(3, date("Y-n-d"));
    $stmt->execute();
    // $AddCounter = $this->row["TaskCounter"] + 1;
    // $stmt = $this->pdo->prepare("UPDATE Tasks SET TaskCounter = ? WHERE EndFlag = 0");
    // $stmt->bindValue(1, $AddCounter, PDO::PARAM_INT);
    // $stmt->execute();
    // $stmt = $this->pdo->prepare("UPDATE Tasks SET LastAccessDay = ? WHERE EndFlag = 0");
    // $stmt->bindValue(1, $this->day, PDO::PARAM_INT);
    // $stmt->execute();
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

  function getRow($str)
  {
    return $this->row[$str];
  }

  function getProgress()
  {
    $this->getTable();
    $per =  $this->row["TaskCounter"] / $this->row["Period"] * 100;
    return ceil($per);
  }

  function hsc($str)
  {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8", false);
  }
}

$main = new Main();

if ($main->getRow("TaskNo")) {
  filter_input(INPUT_POST, "endTask") ? $main->endTask() : false;
}

if ($main->getRow("TaskCounter") != null && $main->getRow("Period") != null) {
  $main->checkGoal();
}

if (!isset($_SESSION["ID"])) {
  header("location:login.php");
}

$calendar = new Calendar();

$calendar->makeCalender(null);

if (isset($_REQUEST['previousMonth'])) {
  $calendar->makeCalender('p');
}

if (isset($_REQUEST['nextMonth'])) {
  $calendar->makeCalender('n');
}

?>

<html>

<head>
  <title>もくひょうくん</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="../css/simple-sidebar.css" rel="stylesheet">
  <link href="../css/calendar-style.css" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../js/main.js"></script>

<body>
  <?php if ($main->getUserMessage()) {
    echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . $main->hsc($main->getUserMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
  }
  ?>

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
        <div class="d-flex align-items-center justify-content-center m-1">
          <form class="m-0" method="POST">
            <input type="submit" name="endTask" class="btn btn-primary" value="今日の分終了！">
          </form>
        </div>
      </nav>
    </div>
  </div>

  <?php
  if ($main->getGoal()) {
    echo "<div><label class='font-weight-light goaltxt'>" . $main->hsc($main->getGoal()) . "</label></div>";
  }
  if ($main->getTask()) {
    echo "<div><label class='font-weight-light tasktxt'>" . $main->hsc($main->getTask()) . "</label></div>";
  }
  if ($main->getDays()) {
    echo "<div><label class='font-weight-light daystxt'>" . $main->hsc($main->getDays()) . "</label><br><div class='progress w-50'><div class='progress-bar' role='progressbar' style='width:" . $main->hsc($main->getProgress()) . "%' aria-valuenow='" . $main->hsc($main->getProgress()) . "' aria-valuemin='0' aria-valuemax='100'>" . $main->hsc($main->getProgress()) . "%</div></div></div>";
  }
  ?>
  <?php echo $main->hsc($calendar->getYear()) . '年' . $main->hsc($calendar->getMonth()) . '月' ?>
  <form class="m-0" method="POST">
    <input type="submit" name="previousMonth" class="btn btn-secondary" value="<">
    <input type="submit" name="nextMonth" class="btn btn-secondary" value=">">
  </form>
  <br>

  <table>
    <tr>
      <th>日</th>
      <th>月</th>
      <th>火</th>
      <th>水</th>
      <th>木</th>
      <th>金</th>
      <th>土</th>
    </tr>
    <tr>
      <?php $cend = 0; ?>
      <?php foreach ($calendar->getCalendar() as $key => $value) : ?>
        <td>
          <?php $cend++;
            echo $main->hsc($value['day']);
            ?>
        </td>
        <?php if ($cend == 7) : ?>
    </tr>
    <tr>
      <?php $cend = 0; ?>
    <?php endif; ?>

  <?php endforeach; ?>
    </tr>
  </table>

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