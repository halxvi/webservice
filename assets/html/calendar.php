<?php
class Calendar
{
    private $year = null;
    private $month = null;
    private $lastday = null;
    private $calendar = null;

    function __construct()
    {
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
            $week = date('w', mktime(0, 0, 0, $month, $i, $this->year));
            if ($i == 1) {
                for ($s = 1; $s <= $week; $s++) {
                    $this->calendar[$j]['day'] = '';
                    $j++;
                }
            }
            $this->calendar[$j]['day'] = $i;
            $j++;

            if ($i ==  $lastday) {
                for ($e = 1; $e <= 6 - $week; $e++) {
                    $this->calendar[$j]['day'] = '';
                    $j++;
                }
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
