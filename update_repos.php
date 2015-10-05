<?php
require __DIR__.'/config.php';
require __DIR__.'/lib/base.php';
define('RUN_FILE_PATH', __DIR__.'/run/update_repos.list');
define('END_FILE_PATH', __DIR__.'/status/update_repos.end');
define('STATUS_FILE_PATH', __DIR__.'/status/update_repos.lst');
define('UNIT', 1000);

openlog('update_repos.php', LOG_ODELAY | LOG_PID, LOG_USER);
prepare_path();
$end = last_id(END_FILE_PATH, 0);
$since = last_id(STATUS_FILE_PATH, 0);
$log = "==========================================================\n".date('Y-m-d H:i:s').":\n";
if ($end != 0)
    $log .= "end = $end\n";
else
    $log .= "first do\n";
$log .= "since = $since\n";
log_run(RUN_FILE_PATH, $log);

$pdo = db();

$start = time();
$total = 0;
do {
    $sql = "SELECT `name` FROM `users` WHERE `id` BETWEEN $since AND $end ORDER BY `id` LIMIT ".UNIT;
    foreach ($pdo->query($sql)->fetchAll() as $row) {
        $total += update($pdo, $row['name']);
        ++$since;
        log_status(STATUS_FILE_PATH, $since);
    }
    $end = update_end($pdo);
    syslog(LOG_INFO, $total.' repos fetched');
} while (1);
echo 'use of time: ', (time() - $start), "\n";

function update($pdo, $name) {
    $sql = "DELETE FROM `repos` WHERE `uname` = '$name'";
    $pdo->exec($sql);
    $url = "https://api.github.com/users/$name/repos?access_token=".GITHUB_UPDATE_REPOS_TOKEN;
    $total = 0;
    do {
        list($status, $header, $body) = curl_get($url);
        if ($status != 200) continue;
        $body = json_decode($body, true);
        foreach ($body as $row) {
            $obj = array(
                'id' => $row['id'],
                'uid' => $row['owner']['id'],
                'uname' => $row['owner']['login'],
                'created' => $row['created_at'],
                'updated' => $row['updated_at'],
                'pushed' => $row['pushed_at'],
                'name' => $row['name'],
                'full_name' => $row['full_name'],
                'description' => $row['description'],
                'private' => $row['private'],
                'fork' => $row['fork'],
                'homepage' => $row['homepage'],
                'language' => $row['language'],
                'forks_cnt' => $row['forks_count'],
                'stars_cnt' => $row['stargazers_count'],
                'watch_cnt' => $row['watchers_count']
            );
            if (insert($pdo, 'repos', $obj) === false) {
                syslog(LOG_ERR, 'insert repo: '.$row['id'].'(id) '.$row['full_name'].'(full_name) failed');
                continue;
            }
            ++$total;
        }
        if (isset($header['Link'])) {
            $link = parse_link($header['Link']);
            if (!isset($link['next'])) break;
            $url = $link['next'];
        } else break;
    } while (1);
}
function update_end($pdo) {
    $sql = "SELECT MAX(`id`) FROM `users`";
    list($ret) = $pdo->query($sql)->fetch(PDO::FETCH_NUM);
    log_status(END_FILE_PATH, $ret);
    return $ret;
}
