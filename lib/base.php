<?php
function curl_get($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_USERAGENT, 'made from https://github.com/lwch/public_github');
    $ret = curl_exec($curl);
    curl_close($curl);
    return $ret;
}
