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
