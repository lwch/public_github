<?php
require __DIR__.'/config.php';
require __DIR__.'/lib/base.php';

define('FORK', 2);

date_default_timezone_set('America/Los_Angeles');

openlog('public_github', LOG_ODELAY | LOG_PID, LOG_LOCAL7);

$start = 0;
$end   = time();
$users_path = __DIR__.'/users';

@unlink($users_path);
$start_time = time();
fetch($start, $end);
echo 'use: ', (time() - $start), "\n";

function rg($start, $end) {
    do {
        $s = date('Y-m-d\TH:i:sO', $start);
        $e = date('Y-m-d\TH:i:sO', $end);
        echo 'scan: ', $s, ' - ', $e, "\n";
        $url = "https://api.github.com/search/users?q=created:$s..$e&sort=joined&per_page=1&access_token=".GITHUB_TOKEN;
        list($status, $header, $body) = curl_get($url);
        if ($status != 200) {
            echo 'status: ', $status, "\n";
            continue;
        }
        $body = json_decode($body, true);
        echo '  cnt: ', $body['total_count'], "\n";
        if ($body['total_count'] <= 1000) return array($end, $body['total_count']);
        $end = ($start + $end) >> 1;
        if ($end <= $start) return array(null, 0);
    } while (1);
}

function fetch($start, $end) {
    $finished = false;
    $total = 0;
    while (!$finished) {
        $tasks = array();
        do {
            list($e, $cnt) = rg($start, $end);
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
                fetch_range($s, $e);
                exit;
            } else $pids[] = $pid;
        }
        foreach ($pids as $pid) if ($pid) pcntl_waitpid($pid, $status);
        syslog(LOG_INFO, $total.' users fetched');
    }
}

function fetch_range($start, $end) {
    $s = date('Y-m-d\TH:i:sO', $start);
    $e = date('Y-m-d\TH:i:sO', $end);
    $url = "https://api.github.com/search/users?q=created:$s..$e&sort=joined&per_page=100&access_token=".GITHUB_TOKEN;
    do {
        list($status, $header, $body) = curl_get($url);
        if ($status != 200) continue;
        $body = json_decode($body, true);
        parse($body);
        if (isset($header['Link'])) {
            $link = parse_link($header['Link']);
            if (!isset($link['next'])) break;
            $url = $link['next'];
            syslog(LOG_INFO, "next: $url");
        } else break;
    } while (1);
}

/*
function fetch($start, $end) {
    $s = date('Y-m-d\TH:i:sO', $start);
    $e = date('Y-m-d\TH:i:sO', $end);
    $save = $end;
    $url = "https://api.github.com/search/users?q=created:$s..$e&sort=joined&access_token=".GITHUB_TOKEN;
    $redo = false;
    $cnt = 0;
    do {
        list($status, $header, $body) = curl_get($url);
        if ($status != 200) continue;
        $body = json_decode($body, true);
        if ($body['total_count'] > 1000) {
            $end = ($start + $end) >> 1;
            $e = date('Y-m-d\TH:i:sO', $end);
            $url = "https://api.github.com/search/users?q=created:$s..$e&sort=joined&access_token=".GITHUB_TOKEN;
            $redo = true;
            syslog(LOG_INFO, "redo: $s..$e");
            continue;
        }
        $cnt += parse($body);
        if (isset($header['Link'])) {
            $link = parse_link($header['Link']);
            if (!isset($link['next'])) break;
            $url = $link['next'];
            syslog(LOG_INFO, "next: $url");
        } else break;
    } while (1);
    syslog(LOG_INFO, "count: $cnt");
    if ($redo) fetch($end, $save);
}
*/

function parse($body) {
    global $users_path;
    foreach ($body['items'] as $row) {
        file_put_contents($users_path, $row['login']."\n", FILE_APPEND);
    }
}
