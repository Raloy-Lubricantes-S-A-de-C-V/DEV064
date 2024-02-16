<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');

require_once 'connParam.php';

$MySQLerrors = [];

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function dimeGrupos() {
    $respuesta["errors"] = "";
    global $MySQLerrors,$dblinker;
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }
    $query = <<<SQL
        SELECT idTgl id,ordenMostrar,tipoGastoLink nombre,descripcion FROM $dblinker.z_linker_tipoGastos ORDER BY ordenMostrar,tipoGastoLink
SQL;

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows > 0) {
            //CON DATOS
            $table = "<table id='sabanaTable' class='display nowrap'>";
            $table.= "<thead><tr><th>ÍNDICE</th><th>NOMBRE</th><th>DESCRIPCIÓN</th><th></th><th></th></tr></thead>";
            $table.= "<tbody>";
            while ($row = $result->fetch_assoc()) {
                $table.= "<tr class='trGruposGastos' id='".$row["id"]."'>"
                        . "<td><input style='width:25px; text-align:center;' type='text' class='tdordengrupo' value='".$row["ordenMostrar"]."'/></td>"
                        . "<td><input type='text' class='tdnombregrupo' value='".utf8_encode($row["nombre"])."'/></td>"
                        . "<td><input type='text' class='tddescgrupo' value='".utf8_encode($row["descripcion"])."'/></td>"
                        . "<td class='edittr' idgrupo='".$row["id"]."'><i class='fa fa-save'></i></td>"
                        . "<td class='deletetr' idgrupo='".$row["id"]."'><i class='fa fa-trash'></i></td>"
                        . "</tr>";
            }
            $table.= "</tbody>";
            $table.= "</table>";
            $respuesta["table"] = $table;
            $respuesta["status"]=1;
        }
    } else {
        $respuesta["errors"] = "Error";
    }
    return json_encode($respuesta);
}
function updateGrupo() {
    $id = $_GET["id"];
    $orden = $_GET["orden"];
    $nombre = $_GET["nombre"];
    $descripcion = $_GET["descripcion"];
    
    $respuesta["errors"] = "";
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }
    $query = "UPDATE $dblinker.z_linker_tipoGastos SET ordenMostrar=".$orden.",tipoGastoLink='".utf8_decode($nombre)."',descripcion='".utf8_decode($descripcion)."' WHERE idTgl=".$id;

    //RESULTADOS DEL QUERY
    if ($mysqli->query($query)) {
        $respuesta["status"]=1;
    } else {
        $respuesta["errors"] = "Error";
    }
    return json_encode($respuesta);
}