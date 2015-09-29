<?php
ini_set('memory_limit', -1);
require __DIR__.'/config.php';
require __DIR__.'/lib/base.php';

define('FORK', 5);

date_default_timezone_set('America/Los_Angeles');

openlog('public_github', LOG_ODELAY | LOG_PID, LOG_LOCAL7);

$start = 0;
$end   = time();
$users_path = __DIR__.'/users';
$main_queue = msg_get_queue(ftok(__FILE__, 'a') + 1, 0666); # tell main worker task
$req_queue  = msg_get_queue(ftok(__FILE__, 'a') + 2, 0666); # request for a task
$rep_queue  = msg_get_queue(ftok(__FILE__, 'a') + 3, 0666); # reply a task

##############################################################################
# for test

$pdo = db();
$sql = 'TRUNCATE TABLE `users`';
$pdo->exec($sql);
$sql = 'TRUNCATE TABLE `followers`';
$pdo->exec($sql);

##############################################################################

@unlink($users_path);
$start_time = time();
fetch($start, $end);
echo 'use: ', (time() - $start_time), "\n";

function pre_do($worker) {
    $task = $worker->fetch_task();
    if ($task !== null) { # one task recved from worker
        context()->push_task($task);
    }
    if ($worker->reply_task()) { # want task
        $worker->get_task_rep(context()->pop_task());
    }
}

function rg($worker, $start, $end) {
    do {
        if ($start == $end) return array(null, 0);
        $s = date('Y-m-d\TH:i:sO', $start);
        $e = date('Y-m-d\TH:i:sO', $end);
        echo 'scan: ', $s, ' - ', $e, "\n";
        $url = "https://api.github.com/search/users?q=created:$s..$e&sort=joined&per_page=1&access_token=".GITHUB_TOKEN;
        list($status, $header, $body) = curl_get($url);
        switch ($status) {
        case 200:
            break;
        case 403:
            echo 'do task', "\n";
            context()->run_task(context()->pop_task());
            continue 2;
        default:
            echo 'status: ', $status, "\n";
            continue 2;
        }
        $body = json_decode($body, true);
        echo '  cnt: ', $body['total_count'], "\n";
        if ($body['total_count'] <= 1000) return array($end, $body['total_count']);
        $end = (($end - $start) >> 1) + $start;
        if ($end <= $start) return array(null, 0);
        pre_do($worker);
        context()->keep_task_count();
    } while (1);
}

function fetch($start, $end) {
    $finished = false;
    $total = 0;
    global $main_queue, $req_queue, $rep_queue;
    $worker = new Worker($main_queue, $req_queue, $rep_queue, true);
    while (!$finished) {
        $tasks = array();
        do {
            list($e, $cnt) = rg($worker, $start, $end);
            if ($e === null) {
                $finished = true;
                break;
            }
            $tasks[] = array($start, $e);
            $start = $e;
            $total += $cnt;
        } while (count($tasks) < FORK);
        $pids = array();
        for ($i = 0; $i < count($tasks); ++$i) {
            list($s, $e) = $tasks[$i];
            echo 'do: ', date('Y-m-d H:i:s', $s), ' - ', date('Y-m-d H:i:s', $e), "\n";
            $pid = pcntl_fork();
            if ($pid == 0) {
                fetch_range($main_queue, $req_queue, $rep_queue, $s, $e);
                exit;
            } else $pids[] = $pid;
        }
        while (count($pids)) {
            $pid = array_shift($pids);
            if ($pid) {
                $rc = pcntl_waitpid($pid, $statusi, WNOHANG | WUNTRACED);
                if ($rc == 0) $pids[] = $pid;
                pre_do($worker);
                context()->keep_task_count();
            }
        }
        syslog(LOG_INFO, $total.' users fetched');
    }
    syslog(LOG_INFO, 'redo task rep');
    while ($task = $worker->fetch_task_rep()) {
        context()->run_task($task);
    }
    syslog(LOG_INFO, 'finish task queue');
    while (context()->task_count()) {
        context()->run_task(context()->pop_task());
    }
}

function fetch_range($main_queue, $req_queue, $rep_queue, $start, $end) {
    $worker = new Worker($main_queue, $req_queue, $rep_queue, false);
    $s = date('Y-m-d\TH:i:sO', $start);
    $e = date('Y-m-d\TH:i:sO', $end);
    $url = "https://api.github.com/search/users?q=created:$s..$e&sort=joined&per_page=100&access_token=".GITHUB_TOKEN;
    echo '=============================== starting [', $s, ' - ', $e, '] ==========================', "\n";
    do {
        list($status, $header, $body) = curl_get($url);
        switch ($status) {
        case 200:
            break;
        case 403:
            echo 'do task', "\n";
            $task = context()->pop_task();
            if ($task) { # if ther is some task in queue do that
                context()->run_task($task);
                continue 2;
            }
            $worker->get_task_req();
            $task = $worker->fetch_task();
            if ($task) context()->run_task($task);
            continue 2;
        default:
            echo '  status: ', $status, "\n";
            continue 2;
        }
        $body = json_decode($body, true);
        parse($worker, $body);
        if (isset($header['Link'])) {
            $link = parse_link($header['Link']);
            if (!isset($link['next'])) break;
            $url = $link['next'];
        } else break;
    } while (1);
    while ($task = context()->pop_task()) {
        $worker->append_task($task);
    }
}

function parse($worker, $body) {
    global $users_path;
    foreach ($body['items'] as $row) {
        echo '.';
        $task = new TaskUserDetail($row['id'], $row['login'], $row['url'], $row['followers_url'], $row['repos_url']);
        $worker->append_task($task);
    }
    echo "\n";
}
