<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
$check = session_check($_GET["t"]);
if ($check != 1) {
    echo json_encode(array("status" => "0", "error" => "Session: " . $check));
    return;
}


$response = getData();

echo $response;

function getData()
{
    $f1=$_GET["f1"];
    $f2=$_GET["f2"];
    $query = <<<SQL
        SELECT 
            litrosSurtidos.loteEPT loteETP,
            round(if(tq.capacidad_l>0,litrosSurtidos.lts/tq.capacidad_l,0),2) porcentaje,
            litrosSurtidos.lts,
            litrosSurtidos.fechaLote,
            litrosSurtidos.planta,
            litrosSurtidos.tanque,
            tq.capacidad_l,
            tq.nombreTanque,
            tq.producto
        FROM
            (SELECT 
                SUBSTRING(loteEPT, 7, 1) planta,
                SUBSTRING(loteEPT, 8, 1) tanque,
                DATE(
                    CONCAT(
                        SUBSTRING(loteEPT, 5, 2),
                        "-",
                        SUBSTRING(loteEPT, 3, 2),
                        "-",
                        SUBSTRING(loteEPT, 1, 2)
                    )
                ) fechaLote,
                loteEPT,
                SUM(ltsSurtir) lts 
            FROM
                smartRoad_pre_ruteo pr 
            WHERE loteEPT IS NOT NULL 
            GROUP BY loteEPT) litrosSurtidos 
            LEFT JOIN
            today_tanques tq 
            ON litrosSurtidos.planta = tq.plantaEnLote 
            AND litrosSurtidos.tanque = tq.numTanquePlanta 
        WHERE 
            fechaLote >= "$f1" 
            AND fechaLote <= "$f2" 
SQL;

    $respuesta["status"] = 0;
    $respuesta["error"]="";

    $dataconn = dataconn("intranet");
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
        $respuesta["error"] = $mysqli->error;
    };
    return json_encode($respuesta);
}