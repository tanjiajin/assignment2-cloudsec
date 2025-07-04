<?php
$serverName = "host.docker.internal,1433";
$connectionInfo = array(
    "Database" => "BakeryOrderSystem",
    "UID" => "bakery_user",
    "PWD" => "StrongPassword123!",
    "TrustServerCertificate" => true,
    "Encrypt" => true
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
