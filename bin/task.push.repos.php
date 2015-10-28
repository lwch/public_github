<?php
ini_set('memory_limit', '2G');
require __DIR__.'/../lib/base.php';
date_default_timezone_set('America/Los_Angeles');

list($cookie, $cookie_name) = login(WORDPRESS_USER, WORDPRESS_PASS);

$t = time();
$start = $t - 3600;

$str_start = date('c', $start);
$pdo = pdo();

$sql = "SELECT
        MAX(`forks_cnt`) AS `forks_cnt`,
        MAX(`stars_cnt`) AS `stars_cnt`,
        MAX(`watch_cnt`) AS `watch_cnt`,
        COUNT(1) AS `cnt`
        FROM `repos_log`
        WHERE `pushed` >= '$start'
        GROUP BY `id`";
$max_forks = $max_stars = $max_watch = $max_cnt = 0;
foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
    if ($row['forks_cnt'] > $max_forks) $max_forks = $row['forks_cnt'];
    if ($row['stars_cnt'] > $max_stars) $max_stars = $row['stars_cnt'];
    if ($row['watch_cnt'] > $max_watch) $max_watch = $row['watch_cnt'];
    if ($row['cnt']       > $max_cnt)   $max_cnt   = $row['cnt'];
}

$sql = "SELECT
        `id`, `description`,
        MAX(`forks_cnt`) AS `forks_cnt`,
        MAX(`stars_cnt`) AS `stars_cnt`,
        MAX(`watch_cnt`) AS `watch_cnt`,
        COUNT(1) AS `cnt`
        FROM `repos_log`
        WHERE `pushed` >= '$start'
        GROUP BY `id`";
$repos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$filter = array();
foreach ($repos as $row) {
    $row['rank'] = rank($row, $max_forks, $max_stars, $max_watch, $max_cnt);
    $row['rank'] = sprintf('%.06f', $row['rank']);
    if ($row['rank'] >= 0.2) $filter[] = $row;
}
usort($filter, function($a, $b) {
    return $a['rank'] < $b['rank'] ? 1 : -1;
});
foreach ($filter as $row) {
    $str = output_repo($row);
    do {
        list($status, $header, $body) = curl_post('https://api.github.com/markdown?access_token='.GITHUB_FETCH_REPOS_TOKEN, json_encode(array(
            'text' => $str
        )));
    } while ($status != 200);
    create_post($cookie, $cookie_name, $row['full_name'], $body, array($row['language']));
}

function rank($obj, $max_forks, $max_stars, $max_watch, $max_cnt) {
    $pfx = 0;
    if (strlen($obj['description']) > 20) $pfx = 0.1;
    return $obj['forks_cnt'] / $max_forks * 0.2 +
           $obj['stars_cnt'] / $max_stars * 0.2 +
           $obj['watch_cnt'] / $max_watch * 0.2 +
           $obj['cnt']       / $max_cnt   * 0.3 + $pfx;
}
function output_repo(&$obj) {
    $pdo = pdo();
    $sql = "SELECT `uname`, `full_name`, `homepage`, `language`, `default_branch` FROM `repos_log` WHERE `id` = '${obj['id']}' ORDER BY `pushed` DESC LIMIT 1";
    $obj = array_merge($obj, $pdo->query($sql)->fetch(PDO::FETCH_ASSOC));
    $str = "# url\n\n".
           "https://github.com/${obj['full_name']}\n\n";
    if (!empty($obj['homepage']))
        $str .= "# homepage\n\n".
                "[${obj['homepage']}](${obj['homepage']})\n\n";
    if (!empty($obj['language']))
        $str .= "#language\n\n".
                "${obj['language']}\n\n";
    $str .= "# Rank\n\n".
            "forks: **${obj['forks_cnt']}** stars: **${obj['stars_cnt']}** watch: **${obj['watch_cnt']}** rank: **${obj['rank']}**\n\n";
    $str .= "# description\n\n".
            $obj['description']."\n\n";
    # TODO: append README.md from default_branch
    return $str;
}
function login($user, $pass) {
    list($status, $header, $body) = curl_get(WORDPRESS_HOST.'/?json=get_nonce&controller=user&method=generate_auth_cookie');
    if ($status != 200) return false;
    $body = json_decode($body, true);
    $nonce = $body['nonce'];
    list($status, $header, $body) = curl_get(WORDPRESS_HOST."/?json=user/generate_auth_cookie&username=$user&password=$pass");
    if ($status != 200) return false;
    $body = json_decode($body, true);
    return array($body['cookie'], $body['cookie_name']);
}
function categories_convert($cats) {
    foreach ($cats as &$cat) {
        switch ($cat) {
        case 'C++':
            $cat = 'c-2';
            break;
        }
    } unset($cat);
    return $cats;
}
function create_post($cookie, $cookie_name, $title, $content, $categories) {
    list($status, $header, $body) = curl_get(WORDPRESS_HOST."/?json=get_nonce&controller=posts&method=create_post&cookie=$cookie", array($cookie_name.'='.$cookie));
    if ($status != 200) return false;
    $body = json_decode($body, true);
    $nonce = $body['nonce'];
    $post = array(
        'json' => 'posts/create_post',
        'nonce' => $nonce,
        'cookie' => $cookie,
        'title' => $title,
        'content' => $content
    );
    $categories = array_filter($categories);
    if (count($categories))
        $post['categories'] = urlencode(implode(',', categories_convert($categories)));
    list($status, $header, $body) = curl_post(WORDPRESS_HOST, $post, array($cookie_name.'='.$cookie));
    if ($status != 200) return false;
    $body = json_decode($body, true);
    $id = $body['post']['id'];
    $cats = array_column($body['post']['categories'], 'title');
    if (count($categories) and count(array_diff($categories, $cats))) # have uncatched category
        return true;
    return publish_post($cookie, $cookie_name, $id);
}
function publish_post($cookie, $cookie_name, $id) {
    list($status, $header, $body) = curl_get(WORDPRESS_HOST."/?json=get_nonce&controller=posts&method=update_post&cookie=$cookie", array($cookie_name.'='.$cookie));
    if ($status != 200) return false;
    $body = json_decode($body, true);
    $nonce = $body['nonce'];
    list($status, $header, $body) = curl_post(WORDPRESS_HOST, array(
        'json' => 'posts/update_post',
        'nonce' => $nonce,
        'cookie' => $cookie,
        'id' => $id,
        'status' => 'publish'
    ), array($cookie_name.'='.$cookie));
    return $status == 200;
}
