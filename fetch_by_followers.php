<?php
require __DIR__.'/lib/base.php';

$queue = array(
    'lwch'
);
$scaned = array();

do {
    $user = array_pop($queue);
    if (in_array($user, $scaned)) continue;
    echo $user, "\n";
    $scaned[] = $user;
    fetch_followers($user, $queue);
} while (!empty($queue));

function fetch_followers($user, &$queue) {
    $url = 'https://api.github.com/users/'.$user.'/followers';
    $a = json_decode(curl_get($url), true);
    if ($a === false) return;
    foreach ($a as $row) $queue[] = $row['login'];
}
