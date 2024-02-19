<?php
echo "hola";
return;
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
// $check = session_check($_GET["t"]);
// if ($check != 1) {
//     echo json_encode(array("status" => "0", "error" => "Session: " . $check));
//     return;
// }


$response = getData();

echo $response;

function getData()
{
    $query = $_GET["sql"];
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if($mysqli->connect_errno){
        $respuesta["error"]="Error de Conexión";
    }
    if ($result = $mysqli->query($query)) {
       
        while ($row = $result->fetch_assoc()) {
            $respuesta["jsondata"][]=$row;
        }
        
        $respuesta["status"] = 1;
    } else {
        $respuesta["error"] ="Error en query". $mysqli->error;
    };
    $mysqli->close();
    return json_encode($respuesta);
}