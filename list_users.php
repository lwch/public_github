<?php
require __DIR__.'/config.php';
require __DIR__.'/lib/base.php';
define('RUN_FILE_PATH', __DIR__.'/run/list_users.list');
define('STATUS_FILE_PATH', __DIR__.'/status/list_users.lst');

openlog('list_users.php', LOG_ODELAY | LOG_PID, LOG_USER);
prepare_path();
$since = last_id(STATUS_FILE_PATH, 1);
$log = "==========================================================\n".date('Y-m-d H:i:s').":\nsince = $since\n";
log_run(RUN_FILE_PATH, $log);
##############################################################################
# for test

#$pdo = db();
#$sql = 'TRUNCATE TABLE `users`';
#$pdo->exec($sql);
#$sql = 'TRUNCATE TABLE `followers`';
#$pdo->exec($sql);

##############################################################################

$pdo = db();

$url = "https://api.github.com/users?since=$since&access_token=".GITHUB_TOKEN;
$i = 0; $total = 0;
$start = time();
do {
    list($status, $header, $body) = curl_get($url);
    if ($status != 200) continue;
    $body = json_decode($body, true);
    $total += parse($pdo, $body);
    syslog(LOG_INFO, $total.' users fetched');
    if (isset($header['Link'])) {
        $link = parse_link($header['Link']);
        if (!isset($link['next'])) break;
        $url = $link['next'];
    } else break;
    echo $url, "\n";
    $out = array();
    parse_str(parse_url($url, PHP_URL_QUERY), $out);
    if (isset($out['since'])) log_status(STATUS_FILE_PATH, $out['since']);
} while (1);
@unlink(__DIR__.'/status/list_users.lst', 1);
echo 'use of time: ', (time() - $start), "\n";

function parse($pdo, $body) {
    foreach ($body as $row) {
        $obj = array(
            'id' => $row['id'],
            'name' => $row['login']
        );
        if (insert_or_update($pdo, 'users', $obj) === false) {
            syslog(LOG_ERR, 'insert(update) user: '.$row['id'].'(id) '.$row['login'].'(name) failed');
            continue;
        }
    }
    return count($body);
}
