<?php

function db_connect() {

        // Define connection as a static variable, to avoid connecting more than once
    static $db;

        // Try and connect to the database, if a connection has not been established yet
    if(!isset($db)) {

        ini_set ('error_reporting', E_ALL);
        ini_set ('display_errors', '1');
        error_reporting (E_ALL|E_STRICT);

        $db = mysqli_init();
        mysqli_options ($db, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
        $url = getenv('JAWSDB_URL');
        $dbparts = parse_url($url);
        $hostname = $dbparts['host'];
        $username = $dbparts['user'];
        $password = $dbparts['pass'];
        $database = ltrim($dbparts['path'],'/');

        $db->ssl_set(NULL, NULL, './include/ca-cert.pem', NULL, NULL);
        $link = mysqli_real_connect ($db, $hostname, $username, $password, $database, 3306, NULL, MYSQLI_CLIENT_SSL);
        if (!$link)
        {
            die ('Connect error (' . mysqli_connect_errno() . '): ' . mysqli_connect_error() . "\n");
        } else {

            return $db;
        }

    }

}

// Connect to the database
$db = db_connect();

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>
