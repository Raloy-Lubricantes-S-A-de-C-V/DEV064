<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
require_once("../../../php/conexion.php");

$MySQLerrors = [];

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function getSabana() {
    $numArtPrincipal = $_GET["material"];
    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];

    
    $respuesta["errors"] = "";
    $respuesta["status"] = 1;
    
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    
    if ($mysqli->connect_errno) {
        $respuesta["errors"] = $mysqli->connect_error; //Error
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }
    
    $query = "SELECT 
  Producto,
  NumPedido,
  Proveedor,
  FechElabo,
  FechaEntre,
  FechaTermino,
  CantiOrden,
  CantiDada,
  FOC.Usuario,
  FOC.Observa 
FROM
  FOC 
WHERE FOC.Producto = '$numArtPrincipal' 
  AND FOC.NumPedido NOT IN 
  (SELECT DISTINCT 
    (numOC) 
  FROM
    z_linker_main) 
  AND FOC.STATUS <> 'C' 
  AND FOC.FechElabo >= '$fec1' 
  AND FOC.FechElabo <= '$fec2' 
  AND (CantiDada > 0 
    OR FOC.OCCERRADA = 0)";

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
    $respuesta["tabla"].="<th>Producto</th>";//0
    $respuesta["tabla"].="<th>OC</th>";//1
    $respuesta["tabla"].="<th>Proveedor</th>";//2
    $respuesta["tabla"].="<th>Fecha Elab.</th>";//3
    $respuesta["tabla"].="<th>Fecha Entrega</th>";//4
    $respuesta["tabla"].="<th>Fecha Term.</th>";//5
    $respuesta["tabla"].="<th>Cant. Orden</th>";//6
    $respuesta["tabla"].="<th>Cant. Entrega</th>";//7
    $respuesta["tabla"].="<th>Usuario</th>";//8
    $respuesta["tabla"].="<th>Observaciones</th>";//9
    $respuesta["tabla"].="</tr>";
    $respuesta["tabla"].="</thead>";
    $respuesta["tabla"].="<tbody>";
    while ($row = $result->fetch_array()) {
        $respuesta["tabla"].="<tr>";
        $respuesta["tabla"].="<td>" . $row[0] . "</td>";
        $respuesta["tabla"].="<td>" . $row[1] . "</td>";
        $respuesta["tabla"].="<td>" . $row[2] . "</td>";
        $respuesta["tabla"].="<td>" . $row[3] . "</td>";
        $respuesta["tabla"].="<td>" . $row[4] . "</td>";
        $respuesta["tabla"].="<td>" . $row[5] . "</td>";
        $respuesta["tabla"].="<td class='righted'>" . round($row[6],2) . "</td>";
        $respuesta["tabla"].="<td class='righted'>" . round($row[7],2) . "</td>";
        $respuesta["tabla"].="<td>" . utf8_decode($row[8]) . "</td>";
        $respuesta["tabla"].="<td>" . utf8_decode($row[9]) . "</td>";
        $respuesta["tabla"].="</tr>";
    }
    $respuesta["tabla"].="</tbody>";
    return json_encode($respuesta);
}
