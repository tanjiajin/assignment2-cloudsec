<?php
$serverName = "host.docker.internal,1433"; // connect from container to MSSQL on host
$connectionInfo = array(
    "Database" => "BakeryOrderSystem",
    "UID" => "bakery_user",
    "PWD" => "StrongPassword123!",
    "TrustServerCertificate" => true // avoid SSL issues
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
