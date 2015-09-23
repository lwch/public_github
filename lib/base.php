<?php
function curl_get($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'made from https://github.com/lwch/public_github');
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
