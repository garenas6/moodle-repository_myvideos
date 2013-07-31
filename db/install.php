<?php

function xmldb_repository_myvideos_install() {
    global $CFG, $DB;
    $result = true;

    if (!$DB->table_exists('myvideos_video')) {
        debugging('myvideos repository does not work if block_myvideos database tables are not present');
        return false;
    }

    return $result;
}
