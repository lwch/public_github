<?php
require __DIR__.'/config.php';
require __DIR__.'/lib/base.php';

date_default_timezone_set('America/Los_Angeles');

$start = 0;
$end   = time();
$users = array();

fetch($start, $end);

function fetch($start, $end) {
    global $users;
    $s = date('Y-m-d\TH:i:sO', $start);
    $e = date('Y-m-d\TH:i:sO', $end);
    $save = $end;
    $url = "https://api.github.com/search/users?q=created:$s..$e&sort=joined&access_token=".GITHUB_TOKEN;
    $redo = false;
    do {
        list($status, $header, $body) = curl_get($url);
        if ($status != 200) continue;
        $body = json_decode($body, true);
        if ($body['total_count'] > 1000) {
            $end = ($start + $end) >> 1;
            $e = date('Y-m-d\TH:i:sO', $end);
            $url = "https://api.github.com/search/users?q=created:$s..$e&sort=joined&access_token=".GITHUB_TOKEN;
            $redo = true;
            echo 'redo: ', $s, '..', $e, "\n";
            continue;
        }
        parse($body, $users);
        $link = parse_link($header['Link']);
        if (!isset($link['next'])) break;
        $url = $link['next'];
        echo $url, "\n";
    } while (1);
    echo 'count: ', count($users), "\n";
    if ($redo) fetch($end, $save);
}

sort($users);
var_dump($users);

function parse($body, &$users) {
    $users = array_merge($users, array_column($body['items'], 'login'));
}