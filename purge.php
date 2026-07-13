<?php
require_once __DIR__ . "/wp-load.php";
if (class_exists("LiteSpeed\Purge")) {
    LiteSpeed\Purge::purge_all();
    echo "PURGED";
} else {
    echo "NOT_AVAILABLE";
}