<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
$check = session_check($_GET["t"]);
if ($check != 1) {
    echo json_encode(array("status" => "0", "error" => "Sesión expirada"));
    return;
}

require_once("../../../../../php/conexion.php");
$fase = $_GET["fx"];
$response = call_user_func($fase);
echo $response;

function getAll(){
    $sql=<<<SQL
    
SQL;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $result = $mysqli->query($query);
    while ($row = $result->fetch_assoc()) {
        $arrEdoMpio[$row["id_det"]] = array(
            "edoCor" => utf8_encode($row["edoMed"]),
            "mpio" => utf8_encode($row["mpio"]),
            "planta" => $row["planta"]
        );
    }
    $mysqli->close();
    return $arrEdoMpio;
}