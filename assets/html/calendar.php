<?php
require_once('db.php');

class Calendar
{
    private $year = null;
    private $month = null;
    private $lastday = null;
    private $calendar = null;
    private $pdo = null;
    private $main = null;

    function __construct()
    {
        $db = new DB();
        $this->pdo = $db->getPDO();
        $this->main = new Main();
        $this->year = date('Y');
        setcookie('year', $this->year);
        $this->month = date('n');
        setcookie('month', $this->month);
        $this->lastday = date('j', mktime(0, 0, 0, $this->month + 1, 0, $this->year));
    }

    function makeCalender($stat)
    {
        if ($stat == 'p') {
            $month = $_COOKIE['month'] - 1;
            $year = $_COOKIE['year'];
            if ($month < 1) {
                $year = $_COOKIE['year'] - 1;
                $month = 12;
            }
            $this->month = $month;
            setcookie('month', $month);
            $this->year = $year;
            setcookie('year', $year);
            $lastday = date('j', mktime(0, 0, 0, $month + 1, 0, $year));
        } elseif ($stat == 'n') {
            $month = $_COOKIE['month'] + 1;
            $year = $_COOKIE['year'];
            if ($month > 12) {
                $year = $_COOKIE['year'] + 1;
                $month = 1;
            }
            $this->month = $month;
            setcookie('month', $month);
            $this->year = $year;
            setcookie('year', $year);
            $lastday = date('j', mktime(0, 0, 0, $month + 1, 0, $year));
        } else {
            $month = $this->getMonth();
            setcookie('month', $month);
            $year = $this->getYear();
            setcookie('year', $year);
            $lastday = $this->lastday;
        }

        $this->calendar = array();
        $j = 0;

        for ($i = 1; $i <  $lastday  + 1; $i++) {
            $week = date('w', mktime(0, 0, 0, $month, $i, $year));
            if ($i == 1) {
                for ($s = 1; $s <= $week; $s++) {
                    $this->calendar[$j]['day'] = '';
                    $j++;
                }
            }
            $this->calendar[$j]['day'] = $i;
            if ($this->checkDate($year, $month, $i)) {
                $this->calendar[$j]['check'] = true;
            }
            $j++;
            if ($i ==  $lastday) {
                for ($e = 1; $e <= 6 - $week; $e++) {
                    $this->calendar[$j]['day'] = '';
                    $j++;
                }
            }
        }
    }

    function checkDate($year, $month, $day)
    {
        $stmt =  $this->pdo->prepare("SELECT Date FROM Counter WHERE TaskNo = ?");
        $stmt->execute(array($this->main->getRow('TaskNo')));
        $Array = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $dateArray = array();
        for ($i = 0; $i < count($Array); $i++) {
            $splitData = explode('-', $Array[$i]);
            for ($j = 0; $j < count($splitData); $j++) {
                $dateArray[$i][$j] = $splitData[$j];
            }
        }
        for ($s = 0; $s < count($Array); $s++) {
            if ($dateArray[$s][0] == $year && $dateArray[$s][1] == $month && $dateArray[$s][2] == $day) {
                return true;
            }
        }
    }

    function getYear()
    {
        return $this->year;
    }
    function getMonth()
    {
        return $this->month;
    }
    function getCalendar()
    {
        return $this->calendar;
    }
}
