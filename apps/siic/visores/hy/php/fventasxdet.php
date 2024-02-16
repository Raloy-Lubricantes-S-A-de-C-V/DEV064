<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
$check = session_check($_GET["t"]);
if ($check != 1) {
    echo json_encode(array("status" => "0", "error" => "Sesión expirada"));
    return;
}

require_once("../../../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function getData()
{
    $from = $_GET["from"];
    $to = $_GET["to"];
    $json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/intranet/ws/fuentes.json");
    $fuentes = json_decode($json, true);
    $fuentes = array(
        "Raloy" => array(
            "url" => "https://odoo-bi.raloy.com.mx/services/total_cliente_determinante_adblue.php?from=$from&to=$to",
            "keys" => array()
        ),
        "ZarKruse" => array(
            "url" => "http://suministroconfiable.com/intranet/ws/ventaszk.php?fec1=$from&fec2=$to",
            "keys" => array()
        )
    );
    // $fuentes = array(
    //     "Raloy" => array(
    //         "url" => "https://odoo-bi.raloy.com.mx/services/total_cliente_determinante_adblue.php?from=$from&to=$to",
    //         "keys" => array()
    //     )
    // );
    $respuesta = [];
    $respuesta["error"] = "";
    $respuesta["elapsedTime"] = [];
    $respuesta["data"] = [];
    $respuesta["status"] = 0;
    foreach ($fuentes as $key => $fuente) {
        $url = $fuente["url"];
        // if(array_key_exists("data",$fuente)){
        //     if(array_key_exists("from",$fuente["data"]));
        //     $getQuery[$fuente["data"]["from"]]=$from;
        //     $getQuery[$fuente["data"]["to"]]=$to;

        // }
        // if(count($getQuery)>0){
        //     $fuente.="?".implode("&",$getQuery);
        // }
        $data = createArray($key, $url);
        $respuesta["elapsedTime"][$key] = $data["elapsedTime"];
        $evalStatus = [];
        if ($data["status"] == 1 && count($data["data"]) > 0) {
            $evalStatus[] = 1;
            $respuesta["status"] = 1;
            $respuesta["data"] = array_merge($respuesta["data"], $data["data"]);
        } else {
            $evalStatus[] = $data["status"];
            $respuesta["error"] .= $data["error"];
        }
        if (in_array(1, $evalStatus)) {
            $respuesta["status"] = 1;
        }
    }
    return json_encode($respuesta);
}

function createArray($datakey, $file)
{
    $ctx = stream_context_create(array(
        'http' =>
        array(
            'timeout' => 1200,  //1200 Seconds is 20 Minutes
        )
    ));
    $iniTime = date("Y-m-d H:i:s");
    if (!$array = file($file, false, $ctx)) {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Host Desconocido";
        return $respuesta;
    }
    $data = json_decode($array[0], True);
    $arrayStdEdoMpio = dimeEdoMpio();
    if (count($data) > 0) {
        foreach ($data as $row) {
            $respuesta["originalRow"][] = $row;
            // echo json_encode($row);
            if (stripos($row["descripcion"], "MBADBLUE") !== false) {
                $arrmarca = array("marca" => "MB");
            } else if (stripos($row["descripcion"], "TRP ") !== false) {
                $arrmarca = array("marca" => "PACCAR");
            } else if (stripos($row["descripcion"], "FLRT") !== false) {
                $arrmarca = array("marca" => "INTERNATIONAL");
            } else if (stripos($row["descripcion"], "CUMMINS") !== false) {
                $arrmarca = array("marca" => "CUMMINS");
            } else if (stripos($row["cliente"], "DAIMLER TRUCKS") !== false) {
                $arrmarca = array("marca" => "MB FF");
            } else if (stripos($row["cliente"], "AUDI") !== false) {
                $arrmarca = array("marca" => "AUDI FF");
            } else if (stripos($row["cliente"], "Volvo") !== false) {
                $arrmarca = array("marca" => "VOLVO");
            } else {
                $arrmarca = array("marca" => "SKYBLUE");
            }
            $datawithmarca = array_merge($row, $arrmarca);
            if (array_key_exists($row["id_enviado"], $arrayStdEdoMpio)) {
                $arrEdoMpio = $arrayStdEdoMpio[$row["id_enviado"]];
            } else {
                $arrEdoMpio = array("edoCor" => "ND", "mpio" => "ND", "planta" => "ND");
            }
            $newarr = array_merge($datawithmarca, $arrEdoMpio);
            $arrayfuente = array("fuente" => $datakey);
            $newData = array_merge($newarr, $arrayfuente);
            $respuesta["data"][] = $newData;
        }
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
    }
    $endTime = date("Y-m-d H:i:s");
    $elapsedTime = (array) date_diff(date_create($endTime), date_create($iniTime));
    $respuesta["elapsedTime"] = $elapsedTime["h"] . "h:" . $elapsedTime["i"] . "m:" . $elapsedTime["s"] . "s";
    return $respuesta;
}

function dimeEdoMpio()
{
    $query = <<<SQL
        SELECT 
            d.id_det_origen id_det,
            edoMed,
            mpio,
            plantaZK2020 planta
        FROM
            smartRoad_stdEdosMpios em 
            INNER JOIN
            smartRoad_stdDet d 
            ON em.id = d.id_relEdoMpio 
        GROUP BY d.id_det_origen   
SQL;

    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $result = $mysqli->query($query);
    while ($row = $result->fetch_assoc()) {
        $arrEdoMpio[$row["id_det"]] = array(
            "edoCor" => utf8_encode($row["edoMed"]),
            "mpio" => utf8_encode($row["mpio"]),
            "planta" => $row["planta"]
        );
    }
    $mysqli->close();
    return $arrEdoMpio;
}

function reestandarizar_Determinantes()
{
    $from = $_GET["f1"];
    $to = $_GET["f2"];
    $fuente = "https://odoo-bi.raloy.com.mx/services/total_cliente_determinante_adblue.php?from=" . $from . "&to=" . $to;
    $array = file($fuente);
    $data = json_decode($array[0], True);
    return json_encode($data);
    // $arrValues = [];
    // $dataconn = dataconn("intranet");
    // $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    // $affected = 0;
    // $errors = [];
    // if (count($data) > 0) {
    //     foreach ($data as $row) {
    //         $values = [];
    //         $values[] = $row["id_enviado"];
    //         $values[] = "'" . str_replace(array('"', "'"), "", $row["cliente"]) . "'";
    //         $values[] = "'" . str_replace(array('"', "'"), "",$row["enviado"]) . "'";
    //         $values[] = "'" . str_replace(array('"', "'"), "",$row["geo_municipio"]) . "'";
    //         $values[] = "'" . str_replace(array('"', "'"), "",$row["geo_estado"]) . "'";
    //         $arrValues[] = "(" . implode(",", $values) . ",'Odoo Raloy')";
    //     }
    //     $strValues = implode(",", $arrValues);
    //     $ciudad="'" . str_replace(array('"', "'"), "",$row["geo_municipio"]) . "'";
    //     $iddet=$row["id_enviado"];
    //     $query = " UPDATE smartRoad_stdDet SET ciudad='$ciudad' WHERE id_det_origen='$iddet'";
    //     if (!$mysqli->query($query)) {
    //         $errors[] = $mysqli->error;
    //     } else {
    //         $affected = $affected + 1;
    //     }
    // }
    // $mysqli->close();
    // return array("errors" => $errors, "affectedRows" => $affected);
}
