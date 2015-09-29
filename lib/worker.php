<?php
class Worker {
    protected $queue;
    protected $main;

    static $append_task  = 1;
    static $get_task_req = 2;
    static $get_task_rep = 3;

    public function __construct($queue, $main) {
        $this->queue = $queue;
        $this->main = $main;
    }

    public function append_task($task) {
        if ($this->main) return false; # only worker can do this
        return msg_send($this->queue, static::$append_task, $task);
    }

    public function get_task_req() {
        if ($this->main) return false; # only worker can do this
        return msg_send($this->queue, static::$get_task_req, '1');
    }

    public function get_task_rep($task) {
        if (!$this->main) return false; # only main can do this
        return msg_send($this->queue, static::$get_task_rep, $task);
    }

    function do_recieve($type) {
        $len = 1024;
        do {
            $ret = msg_receive($this->queue, 0, $type, $len, $msg, true, MSG_IPC_NOWAIT, $err_no);
            if ($ret) return $msg;
            else if ($err_no == MSG_ENOMSG) return null;
            $len <<= 1;
        } while (1);
    }

    public function fetch_task() {
        if ($this->main) {
            return $this->do_recieve(static::$append_task);
        } else {
            # fetch from append_task first
            $ret = $this->do_recieve(static::$append_task);
            if ($ret) return $ret;
            return $this->do_recieve(static::$get_task_rep);
        }
    }

    public function reply_task() {
        if (!$this->main) return false; # only main can do this
        return $this->do_recieve(static::$get_task_req);
    }

    public function fetch_task_rep() { # redo tasks
        return $this->do_recieve(static::$get_task_rep);
    }
}
