<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
require_once("../../../../php/conexion.php");

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function dimeLotes() {

    $respuesta["status"] = 1; //Status exitoso
    $respuesta["error"] = ""; //Sin errores

    $query = <<<SQL
    SELECT 
        m.Referencia Lote,
        m.Fecha,
        m.Folio 
    FROM 
        muestras m 
    WHERE 
        m.Descripcion='SKY BLUE' 
        AND YearFolio>=2017 
    GROUP BY m.Folio,m.Referencia,m.Fecha 
    ORDER BY m.Fecha DESC 
SQL;
    $respuesta["query"] = $query;
    //CONEXIÓN A MYSQL
    $dataconn = dataconn("laboratorio");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }
        //CON DATOS
        $respuesta["options"] = "<option value=''></option>";

        while ($row = $result->fetch_assoc()) {
            $respuesta["options"] .= "<option value='" . $row["Lote"] . "'>" . $row["Lote"] . " (" . $row["Fecha"] . ")</option>";
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    return json_encode($respuesta);
}
