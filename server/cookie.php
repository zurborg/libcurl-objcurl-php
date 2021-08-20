<?php

foreach ($_COOKIE as $key => $value) {
    [$value, $expires, $path, $domain, $secure, $httponly] = \json_decode($value);
    setcookie($key, $value, $expires, $path, $domain, $secure, $httponly);
}
