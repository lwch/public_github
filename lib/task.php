<?php
abstract class Task {
    protected $err_no;

    static $err_crashed = -1;
    static $err_403 = 1;

    public function __construct() {
        $this->err_no = 0;
    }

    abstract public function type();
    abstract public function run();

    function append_token($url) {
        $pos = strpos($url, '?');
        if ($pos !== false and $pos != strlen($url) - 1)
            return $url.'&access_token='.GITHUB_TOKEN;
        else
            return $url.'?access_token='.GITHUB_TOKEN;
    }

    function err_callback() {
        if ($this->err_no == static::$err_403) {
            $this->err_no = 0;
            context()->push_task($this); # retry
            return true;
        }
        return false;
    }
}
class TaskUserRepos extends Task {
    public function type() {
        return 'TaskUserRepos';
    }

    public function run() {
        echo 'run TaskUerRepos', "\n";
        return true;
    }

    public function err_callback() {
    }
}
require __DIR__.'/task/user_detail.php';
require __DIR__.'/task/user_followers.php';
