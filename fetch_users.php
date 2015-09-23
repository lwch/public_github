<?php
require __DIR__.'/lib/base.php';
var_dump(curl_get('https://api.github.com/search/users?q=repos:%3E0&page=1'));
