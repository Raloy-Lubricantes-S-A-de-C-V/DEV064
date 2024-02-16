<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');

require_once '../../../php/conexion.php';
$MySQLerrors = [];

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function getSesiones() {
    $respuesta["errors"] = "";
    $respuesta["status"] = 1;
    
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    
    if ($mysqli->connect_errno) {
        $respuesta["errors"] = $mysqli->connect_error; //Error
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }
    
    $query = "SELECT dateSesion,nomUsuario FROM z_linker_sesiones GROUP BY DATE_FORMAT(dateSesion,'%Y-%m-%d') order by dateSesion DESC";

    $result = $mysqli->query($query);
    if ($mysqli->errno) {
        $respuesta["errors"] = $mysqli->error;
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }
    if($result->num_rows==0){
        $respuesta["errors"] = "No hay datos con el filtro seleccionado";
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }
    
    $respuesta["tabla"] = "";
    $respuesta["tabla"].="<thead>";
    $respuesta["tabla"].="<tr>";
    $respuesta["tabla"].="<th>Fecha</th>";//0
    $respuesta["tabla"].="<th>Usuario</th>";//1
    $respuesta["tabla"].="</tr>";
    $respuesta["tabla"].="</thead>";
    $respuesta["tabla"].="<tbody>";
    while ($row = $result->fetch_array()) {
        $respuesta["tabla"].="<tr>";
        $respuesta["tabla"].="<td>" . $row[0] . "</td>";
        $respuesta["tabla"].="<td>" . utf8_encode($row[1]) . "</td>";
        $respuesta["tabla"].="</tr>";
    }
    $respuesta["tabla"].="</tbody>";
    return json_encode($respuesta);
}
