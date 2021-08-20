<?php

foreach ($_GET as $key => $value) {
    header("$key: $value");
}
