<?php
class Worker {
    protected $main_queue;
    protected $req_queue;
    protected $rep_queue;
    protected $main;

    public function __construct($main_queue, $req_queue, $rep_queue, $main) {
        $this->main_queue = $main_queue;
        $this->req_queue = $req_queue;
        $this->rep_queue = $rep_queue;
        $this->main = $main;
    }

    public function append_task($task) {
        if ($this->main) return false; # only worker can do this
        return msg_send($this->main_queue, 1, $task);
    }

    public function get_task_req() {
        if ($this->main) return false; # only worker can do this
        return msg_send($this->req_queue, 1, '1');
    }

    public function get_task_rep($task) {
        if (!$this->main) return false; # only main can do this
        return msg_send($this->rep_queue, 1, $task);
    }

    function do_recieve($queue) {
        $len = 1024;
        do {
            $ret = msg_receive($queue, 0, $t, $len, $msg, true, MSG_IPC_NOWAIT, $err_no);
            if ($ret) return $msg;
            else if ($err_no == MSG_ENOMSG) return null;
            $len <<= 1;
        } while (1);
    }

    public function fetch_task() {
        if ($this->main) {
            return $this->do_recieve($this->main_queue);
        } else {
            # fetch from append_task first
            $ret = $this->do_recieve($this->main_queue);
            if ($ret) return $ret;
            return $this->do_recieve($this->rep_queue);
        }
    }

    public function reply_task() {
        if (!$this->main) return false; # only main can do this
        return $this->do_recieve($this->req_queue);
    }

    public function fetch_task_rep() { # redo tasks
        return $this->do_recieve($this->rep_queue);
    }
}
