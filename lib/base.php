<?php
function curl_get($url) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_USERAGENT      => 'made from https://github.com/lwch/public_github'
    ));
    $ret = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $header_len = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = array();
    foreach (explode("\r\n", substr($ret, 0, $header_len)) as $row) {
        if (strlen($row) == 0) continue;
        $pos = strpos($row, ':');
        if ($pos === false) continue;
        $key = substr($row, 0, $pos);
        $val = substr($row, $pos + 2);
        $header[$key] = $val;
    }
    $body = substr($ret, $header_len);
    curl_close($curl);
    return array($status, $header, $body);
}
function made_token($uid) {
    do {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => 'https://api.github.com/authorizations',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_USERAGENT      => 'made from https://github.com/lwch/public_github',
            CURLOPT_POSTFIELDS     => json_encode(array(
                'note'          => 'fortest',
                'client_id'     => GITHUB_CLIENT_ID,
                'client_secret' => GITHUB_CLIENT_SECRET,
                'fingerprint'   => 'u'.$uid
            )),
            CURLOPT_USERPWD        => GITHUB_USER.':'.GITHUB_PASS
        ));
        $ret = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($status != 201) continue;
        $ret = json_decode($ret, true);
        return $ret['token'];
    } while (1);
}
function parse_link($link) {
    $ret = array();
    foreach (explode(',', $link) as $row) {
        $row = trim($row);
        $row = explode(';', $row, 2);
        $row[0] = trim($row[0]);
        $row[1] = trim($row[1]);
        if (substr($row[0], 0, 1) == '<' and
            substr($row[0], -1) == '>' and
            substr($row[1], 0, 4) == 'rel=') {
            $key = substr($row[1], 5, -1);
            $val = substr($row[0], 1, -1);
            $ret[$key] = $val;
        }
    }
    return $ret;
}
function context() {
    static $context;
    if (empty($context)) $context = new Context();
    return $context;
}
function db() {
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
    } catch (PDOException $e) {
        echo 'ERR: can not connect to db', "\n";
        var_dump($e);
        exit;
    }
    return $pdo;
}
function insert_or_update($pdo, $table, $obj) {
    $sql = "INSERT INTO `$table`(`".implode('`,`', array_keys($obj))."`) VALUES(";
    $update = array();
    foreach ($obj as $k => $v) {
        $obj[$k] = $pdo->quote($v);
        $update[] = "`$k` = VALUES(`$k`)";
    }
    $sql .= implode(',', array_values($obj)).") ON DUPLICATE KEY UPDATE ".implode(',', $update);
    return $pdo->exec($sql);
}
function prepare_path() {
    if (!is_dir(__DIR__.'/../status')) mkdir(__DIR__.'/../status', 0774, true);
    if (!is_dir(__DIR__.'/../run')) mkdir(__DIR__.'/../run', 0774, true);
}
function last_id($path, $default) {
    if (!is_file($path)) {
        file_put_contents($path, $default);
        return $default;
    }
    return file_get_contents($path);
}
function log_status($path, $status) {
    file_put_contents($path, $status);
}
function log_run($path, $append) {
    file_put_contents($path, $append, FILE_APPEND);
}
require __DIR__.'/context.php';
require __DIR__.'/worker.php';
