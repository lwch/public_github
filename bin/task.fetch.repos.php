<?php
require __DIR__.'/../lib/base.php';
date_default_timezone_set('America/Los_Angeles');
define('BEFORE', 75);

$t = time();
$start = $t - BEFORE;
$end   = $t;
$repos = array();
$users = array();
fetch($start, $end);

function fetch($start, $end) {
    $str_start = date('c', $start);
    $str_end   = date('c', $end);
    do {
        $url = 'https://api.github.com/search/repositories?q=pushed:'.$str_start.'..'.$str_end.'&access_token='.GITHUB_FETCH_REPOS_TOKEN;
        list($status, $header, $body) = curl_get($url);
        if ($status == 403) continue;
        $body = json_decode($body, true);
        if ($body['total_count'] > 1000) {
            $tmp = ($start >> 1) + ($end >> 1);
            fetch($start, $tmp);
            fetch($tmp + 1, $end);
            break;
        } elseif ($body['total_count'] == 0) {
            break;
        } else {
            parse($header, $body);
            break;
        }
    } while (1);
}
function parse($header, $body) {
    global $repos, $users;
    $pdo = pdo();
    foreach ($body['items'] as $item) {
        $sql = "SELECT COUNT(1) FROM `repos_log` WHERE `id` = '${item['id']}' AND UNIX_TIMESTAMP(`pushed`) = UNIX_TIMESTAMP('${item['pushed_at']}')";
        list($cnt) = $pdo->query($sql)->fetch(PDO::FETCH_NUM);
        if ($cnt) continue; # skip
        $repo = array(
            'id'             => $item['id'],
            'uid'            => $item['owner']['id'],
            'uname'          => $item['owner']['login'],
            'name'           => $item['name'],
            'description'    => $item['description'],
            'full_name'      => $item['full_name'],
            'private'        => $item['private'],
            'fork'           => $item['fork'],
            'created'        => $item['created_at'],
            'updated'        => $item['updated_at'],
            'pushed'         => $item['pushed_at'],
            'homepage'       => $item['homepage'],
            'stars_cnt'      => $item['stargazers_count'],
            'watch_cnt'      => $item['watchers_count'],
            'forks_cnt'      => $item['forks_count'],
            'language'       => $item['language'],
            'default_branch' => $item['default_branch']
        );
        insert('repos_log', $repo);
        $user = array(
            'id'          => $item['owner']['id'],
            'name'        => $item['owner']['login']
        );
        insert_or_update('users', $user);
    }
    if (isset($header['Link'])) {
        $link = parse_link($header['Link']);
        if (!isset($link['next'])) return;
        do {
            list($status, $header, $body) = curl_get($link['next']);
            if ($status == 403) continue;
            parse($header, json_decode($body, true));
            break;
        } while (1);
    }
}
