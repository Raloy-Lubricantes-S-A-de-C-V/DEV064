<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("../../../php/conexion.php");
$fase = "";
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function getData() {
    $fec1=$_GET["f1"];
    $fec2=$_GET["f2"];
    $query = <<<SQL
    SELECT 
    mues.Referencia lote,
    mues.fechaProd,
    mues.planta,
    mues.tanque tq,
    mues.f1 fechaIngreso,
    mues.f2 fechaResultado,
    TIMEDIFF(mues.f2, mues.f1) lapso,
    mues.param,
    valMin valor_min,
    valMax valor_max,
    IF(mues.Valor="",0,mues.Valor) Resultado,
    IF(
        mues.Valor = "" 
        OR ISNULL(mues.Valor),
        "ND",
        IF(
            ISNULL(valMax) 
            AND mues.Valor = "1",
            "OK",
            IF(
                ISNULL(valMin),
                IF(valMax >= mues.valor, "OK", "ERR"),
                IF(
                    valMax >= mues.valor 
                    AND valMin <= mues.Valor,
                    "OK",
                    "ERR"
                )
            )
        )
    ) okErr 
FROM
    Skyblue_referenciaISO iso 
    INNER JOIN
    (SELECT 
        a.Pid,
        CONCAT(
            "20",
            SUBSTRING(m.Referencia, 5, 2),
            "-",
            SUBSTRING(m.Referencia, 3, 2),
            "-",
            LEFT(m.Referencia, 2)
        ) fechaProd,
        SUBSTRING(m.Referencia, 7, 1) planta,
        SUBSTRING(m.Referencia, 8, 1) Tanque,
        m.Referencia,
        m.idMuestra,
        a.PNombre param,
        IF(
            a.PValor REGEXP ('^[0-9]'),
            ROUND(a.PValor, 4),
            IF(a.PValor = "si", 1, a.PValor)
        ) valor,
        m.Fecha f1,
        m.respuesta_fecha f2,
        a.Analista an,
        m.Descripcion 
    FROM
        muestras m 
        LEFT JOIN
        analisisMuestras a 
        ON m.folio = a.idMuestra 
        AND m.YearFolio = a.YearMuestra 
    WHERE Pid IN (
            1032,
            1132,
            1078,
            1079,
            1080,
            1081,
            1082,
            1083,
            1084,
            1085,
            1086,
            1087,
            1088,
            1089,
            1090,
            1091,
            1092,
            1093,
            1094,
            322
        ) 
        AND m.Tipo = "ZK" 
        AND CONCAT(
            "20",
            SUBSTRING(m.Referencia, 5, 2),
            "-",
            SUBSTRING(m.Referencia, 3, 2),
            "-",
            LEFT(m.Referencia, 2)
        ) >= "$fec1" 
        AND CONCAT(
            "20",
            SUBSTRING(m.Referencia, 5, 2),
            "-",
            SUBSTRING(m.Referencia, 3, 2),
            "-",
            LEFT(m.Referencia, 2)
        ) <= "$fec2" 
    ORDER BY a.Pid) mues 
    ON mues.Pid = iso.Pid 
WHERE planta IN ('s', 'g', 'm','y','d','p','b','c','L') 
ORDER BY mues.fechaProd DESC,
    lote,
    iso.ordenEnNorma 
SQL;
//echo $query;
    $respuesta["status"] = 0;
    $dataconn=dataconn("laboratorio");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $array=[];
            foreach($row as $key=>$value){
                $array[$key]=$value;
            }
            $respuesta["jsondata"][] = $array;
        }
        $respuesta["status"] = 1;
        $respuesta["query"]=$query;
        $respuesta["numDatos"]=$result->num_rows;
        $result->free();
    }else{
        $respuesta["error"]=$mysqli->error;
    };
    
    $mysqli->close();
    return json_encode($respuesta);
}