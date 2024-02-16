<?php

//header('Content-Type: text/event-stream');
header('Content-Type: text/event-stream, charset=UTF-8');
header('Cache-Control: no-cache');
date_default_timezone_set('America/Mexico_City');

require_once ("../../../php/conexion.php");
echo "data:".dimeCargas().PHP_EOL;
//echo "data:hola".PHP_EOL;
echo PHP_EOL;
echo "retry: 36000000\n";
ob_flush();
flush();

function dimeCargas() {
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $query = <<<SQL
    SELECT 
      GROUP_CONCAT(c.id_entrega) ids,
     COUNT(DISTINCT c.id_entrega) conteocargas
    FROM
      smartRoad_entregas c 
    WHERE c.STATUS IN ("carga")
SQL;
    if ($result = $mysqli->query($query)) {
        //CON DATOS
        $respuesta["datos"] = $result->fetch_assoc();
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $respuesta["datos"]="";
        return json_encode($respuesta);
    }
    return json_encode($respuesta);
}
?>
