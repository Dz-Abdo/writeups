<?php
function get_db(): SQLite3 {
    static $db = null;
    if ($db === null) {
        $db = new SQLite3('/data/diver.db');
        $db->enableExceptions(true);
        $db->exec('PRAGMA journal_mode=WAL');
        $db->exec('PRAGMA foreign_keys=ON');
    }
    return $db;
}
