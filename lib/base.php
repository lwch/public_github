<?php
require __DIR__.'/../conf/conf.php';
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
function pdo() {
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        ));
    } catch (PDOException $e) {
        echo 'ERR: can not connect to db', "\n";
        var_dump($e);
        exit;
    }
    return $pdo;
}
function insert($table, $obj) {
    $pdo = pdo();
    $sql = "INSERT INTO `$table`(`".implode('`,`', array_keys($obj)).'`)';
    foreach ($obj as &$v) {
        $v = $pdo->quote($v);
    } unset($v);
    $sql .= ' VALUES('.implode(',', $obj).')';
    return $pdo->exec($sql);
}
