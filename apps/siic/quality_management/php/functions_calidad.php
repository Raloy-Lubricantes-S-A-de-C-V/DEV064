<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
require_once("../../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function dimeResults() {
    $lote = $_GET["nl"];
    if ($lote == "") {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Proporcione un número de Lote válido";
        return json_encode($respuesta);
    }
    $respuesta["status"] = 1; //Status exitoso
    $respuesta["error"] = ""; //Sin errores

    $query = <<<SQL
    SELECT 
        a.Pid,
        a.PNombre param,
        IF(a.PValor REGEXP ('^[0-9]'),ROUND(a.PValor,4),a.PValor) valor,
        m.Fecha f1,
        m.FechaImpreso f2,
        a.Analista an
    FROM
        muestras m 
    LEFT JOIN
        analisisMuestras a 
    ON 
        m.folio = a.idMuestra 
        AND m.YearFolio = a.YearMuestra 
    WHERE 
        referencia = "$lote" 
SQL;

    //CONEXIÓN A MYSQL
    $dataconn=dataconn("laboratorio");
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
        if ($result->num_rows == 0) {
            $respuesta["status"] = 0;
            $respuesta["error"] = "No se han encontrado datos con el lote especificado";
            return json_encode($respuesta);
        }

        //CON DATOS
        $valores = [];
        while ($row = $result->fetch_assoc()) {
            switch ($row["Pid"]) {
                case "1089":
                    $valores["MgVal"] = $row["valor"];
                    break;
                case "1081":
                    $valores["insolVal"] = $row["valor"];
                    break;
                case "1091":
                    $valores["NiVal"] = $row["valor"];
                    break;
                case "1088":
                    $valores["KVal"] = $row["valor"];
                    break;
                case "1090":
                    $valores["NaVal"] = $row["valor"];
                    break;
                case "322":
                    $valores["identidadVal"] = str_replace("Identico", "Idéntico", $row["valor"]);
                    break;
                case "1083":
                    $valores["PO4Val"] = $row["valor"];
                    break;
                case "1080":
                    $valores["IrVal"] = $row["valor"];
                    break;
                case "1087":
                    $valores["HeVal"] = $row["valor"];
                    break;
                case "1085":
                    $valores["CrVal"] = $row["valor"];
                    break;
                case "1032":
                    $valores["densVal"] = $row["valor"] * 1000;
                    break;
                case "1093":
                    $valores["ureaVal"] = $row["valor"];
                    break;
                case "1086":
                    $valores["CuVal"] = $row["valor"];
                    break;
                case "1092":
                    $valores["ZVal"] = $row["valor"];
                    break;
                case "1084":
                    $valores["CaVal"] = $row["valor"];
                    break;
                case "1079":
                    $valores["biuretVal"] = $row["valor"];
                    break;
                case "1094":
                    $valores["AlVal"] = $row["valor"];
                    break;
                case "1082":
                    $valores["aldVal"] = $row["valor"];
                    break;
                case "1078":
                    $valores["NH3Val"] = $row["valor"];
                    break;

                default:

                    break;
            }
            $valores["f1"] = $row["f1"];
            $valores["f2"] = $row["f2"];
            $valores["an"] = $row["an"];
        }

        $respuesta["valores"] = $valores;
        return json_encode($respuesta);
    }
}
