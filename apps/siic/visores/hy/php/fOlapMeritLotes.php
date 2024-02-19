<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("../../../../../php/conexion.php");
$fase = "";
$fase = $_GET["fase"];

$response = getData();
echo $response;

function getData()
{
    $fec1 = $_GET["f1"];
    $fec2 = $_GET["f2"];
    $query = <<<SQL
        SELECT 
            e.id_entrega folio,
            e.numEnvioRaloy,
            e.planta_carga planta,
            e.fecha_carga,
            pr.eta,
            pr.remisionZK,
            pr.loteZK lPT,
            pr.loteEPT lPTP,
            pr.ltsSurtir litros 
        FROM
            smartRoad_pre_ruteo pr 
            INNER JOIN
            smartRoad_entregas e 
            ON pr.id_entrega = e.id_entrega 
        WHERE NOT ISNULL(pr.remisionZK) 
            AND DATE(e.fecha_carga) >= "$fec1" 
            AND DATE(e.fecha_carga) <= "$fec2" 
SQL;

    $respuesta["status"] = 0;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($result = $mysqli->query($query)) {
        
        while ($row = $result->fetch_assoc()) {
            $respuesta["jsondata"][] = $row;
        }
        $respuesta["status"] = 1;
    } else {
        $respuesta["error"] = $mysqli->error;
    };
    $mysqli->close();
    return json_encode($respuesta);
}