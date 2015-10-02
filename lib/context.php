<?php
require __DIR__.'/task.php';
class Context {
    public $tasks;
    public $users_count;
    public $repos_count;

    public function __construct() {
        $this->tasks = array();
        $this->users_count = 0;
        $this->repos_count = 0;
    }

    public function push_task($task) {
        $this->tasks[] = $task;
    }

    public function pop_task() {
        return array_shift($this->tasks);
    }

    public function task_count() {
        return count($this->tasks);
    }

    public function run_task($task) {
        if ($task === null) return true;
        if (!$task->run() and !$task->err_callback()) return false;
        return true;
    }

    public function keep_task_count() {
        if (!KEEP_TASKQUEUE_LENGTH) return;
        if ($this->task_count() > TASKQUEUE_MAX_LENGTH) {
            $i = 0; $pre = floor(TASKQUEUE_MAX_LENGTH / 20);
            while ($this->task_count() > TASKQUEUE_MAX_LENGTH) {
                $task = $this->pop_task();
                $this->run_task($task);
                if (++$i % $pre == 0) echo '  now length: ', $this->task_count(), "\n";
            }
        }
    }
}
