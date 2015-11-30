<?php
ini_set('memory_limit', '1G');
require __DIR__.'/../lib/base.php';
date_default_timezone_set('America/Los_Angeles');

define('DEBUG', false);
define('MAX_PUBLISH_CNT', 1);

openlog('task.push.repos.php', LOG_NDELAY|LOG_PID, LOG_CRON);
list($cookie, $cookie_name) = login(WORDPRESS_USER, WORDPRESS_PASS);
syslog(LOG_INFO, 'now start task.push.repos.php');

$t = time();
$start = $t - 600;

$str_start = date('c', $start);
$pdo = pdo();

$published = 0;
$s = 0;
while ($published == 0) {
    $sql = "SELECT
            `id`,
            `forks_cnt`,
            `stars_cnt`,
            `watch_cnt`,
            COUNT(1) AS `cnt`
            FROM `repos_log`
            WHERE `pushed` >= '$str_start'
            GROUP BY `id`
            ORDER BY `cnt` DESC
            LIMIT $s, ".(MAX_PUBLISH_CNT * 100);
    $repos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    if (empty($repos)) break;
    $begin = time() - 7 * 24 * 3600;
    $ids = array_column($repos, 'id');
    $str_begin = date('c', $begin);
    $sql = "SELECT `src_id`, `pushed` FROM `pushed_log` WHERE `src_id` IN('".implode("','", $ids)."') AND `pushed` >= '$str_begin'";
    $have = array_column($pdo->query($sql)->fetchAll(PDO::FETCH_NUM), 0);
    foreach ($repos as $obj) {
        if (in_array($obj['id'], $have)) continue;
        append($obj, $cookie, $cookie_name);
        if (++$published >= MAX_PUBLISH_CNT) break;
    }
    syslog(LOG_INFO, "published $published");
    $s += count($repos);
}
syslog(LOG_INFO, "======= all done, published $published");

function output_repo(&$obj) {
    $pdo = pdo();
    $sql = "SELECT `description`, `uname`, `full_name`, `homepage`, `language`, `default_branch` FROM `repos_log` WHERE `id` = '${obj['id']}' ORDER BY `pushed` DESC LIMIT 1";
    $obj = array_merge($obj, $pdo->query($sql)->fetch(PDO::FETCH_ASSOC));
    $str = "# ${obj['full_name']}\n\n".
           "${obj['description']}\n\n".
           "## Link\n\n".
           "[https://github.com/${obj['full_name']}](https://github.com/${obj['full_name']})\n\n";
    if (!empty($obj['homepage']))
        $str .= "## Homepage\n\n".
                "[${obj['homepage']}](${obj['homepage']})\n\n";
    if (!empty($obj['language']))
        $str .= "## Language\n\n".
                "${obj['language']}\n\n";
    $str .= "## Rank\n\n".
            "forks: **${obj['forks_cnt']}** stars: **${obj['stars_cnt']}** watch: **${obj['watch_cnt']}** push: **${obj['cnt']}**\n\n";
    $str .= "## Description\n\n";
    return $str;
}
function output_description($obj) {
    do {
        $url = "https://raw.githubusercontent.com/${obj['full_name']}/".urlencode($obj['default_branch'])."/README.md";
        list($status, $header, $body) = curl_get($url);
    } while ($status != 404 and $status != 200);
    $ret = '';
    if ($status == 200) {
        if (empty($body)) return '';
        $content = $body;
        do {
            list($status, $header, $ret) = curl_post('https://api.github.com/markdown?access_token='.GITHUB_FETCH_REPOS_TOKEN, json_encode(array(
                'text' => $content,
                'mode' => 'gfm',
                'context' => $obj['full_name']
            )));
        } while ($status != 200 and $status != 400);
    }
    return $ret;
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
        case 'C#':
            $cat = 'c-3';
            break;
        case 'F#':
            $cat = 'f';
            break;
        case 'Visual Basic':
            $cat = 'visual-basic';
            break;
        case 'Common Lisp':
            $cat = 'common-lisp';
            break;
        case 'Emacs Lisp':
            $cat = 'emacs-lisp';
            break;
        case 'API Blueprint':
            $cat = 'api-blueprint';
            break;
        case 'Standard ML':
            $cat = 'standard-ml';
            break;
        case 'Pure Data':
            $cat = 'pure-data';
            break;
        case 'Web Ontology Language':
            $cat = 'web-ontology-language';
            break;
        case 'Game Maker Language':
            $cat = 'game-maker-language';
            break;
        case 'DIGITAL Command Language':
            $cat = 'digital-command-language';
            break;
        case 'OpenEdge ABL':
            $cat = 'openedge-abl';
            break;
        case 'Objective-C++':
            $cat = 'objective-c-2';
            break;
        case 'Propeller Spin':
            $cat = 'propeller-spin';
            break;
        case 'Protocol Buffer':
            $cat = 'protocol-buffer';
            break;
        case 'Grammatical Framework':
            $cat = 'grammatical-framework';
            break;
        case 'IGOR Pro':
            $cat = 'igor-pro';
            break;
        }
    } unset($cat);
    return $cats;
}
function create_post($obj, $cookie, $cookie_name, $title, $content, $categories) {
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
    if ($status != 200) {
        syslog(LOG_ERR, "${obj['full_name']} create_post failed");
        return false;
    }
    $body = json_decode($body, true);
    $id = $body['post']['id'];
    $cats = array_column($body['post']['categories'], 'title');
    if (count($categories) and count(array_diff($categories, $cats))) { # have uncatched category
        syslog(LOG_INFO, "${obj['full_name']} has uncatched category, no published");
        return true;
    }
    if (publish_post($cookie, $cookie_name, $id)) {
        syslog(LOG_INFO, "${obj['full_name']} has been published, ${obj['forks_cnt']}[forks] ${obj['stars_cnt']}[stars] ${obj['watch_cnt']}[watch] ${obj['cnt']}[push]");
        return true;
    } else {
        syslog(LOG_ERR, "${obj['full_name']} publish failed");
        return false;
    }
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
function append($row, $cookie, $cookie_name) {
    $str = output_repo($row);
    do {
        list($status, $header, $body) = curl_post('https://api.github.com/markdown?access_token='.GITHUB_FETCH_REPOS_TOKEN, json_encode(array(
            'text' => $str
        )));
    } while ($status != 200);
    $desc = output_description($row);
    if (!empty($desc)) {
        $body .= "<!--more-->\n".
                 "<blockquote>$desc</blockquote>";
    }
    create_post($row, $cookie, $cookie_name, $row['full_name'], $body, array($row['language']));
    syslog(LOG_INFO, "${row['full_name']} has been published");
    $obj = array(
        'src_id'    => $row['id'],
        'full_name' => $row['full_name'],
        'language'  => $row['language'],
        'forks_cnt' => $row['forks_cnt'],
        'stars_cnt' => $row['stars_cnt'],
        'watch_cnt' => $row['watch_cnt']
    );
    insert('pushed_log', $obj);
}
