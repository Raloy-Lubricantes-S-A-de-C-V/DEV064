<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');



require_once("../../../../../php/conexion.php");
$fase = $_GET["fx"];
$response = call_user_func($fase);
echo $response;
function createReport()
{
    $response = [];
    $response["thisYear"] = Date("Y");
    $response["today"] = Date("j");
    $response["thisMonth"] = Date("m");
    $response["daysInMonth"] = cal_days_in_month(CAL_GREGORIAN, $response["thisMonth"], $response["thisYear"]);

    $dateEnd = "Y-m-" . $response["daysInMonth"];
    $from = date("Y-m-01");
    $to = Date("Y-m-d");
    $response["weekendDaysMonth"] = weekendsInRange(strtotime($from), strtotime(date($dateEnd)));
    $response["weekendDaysTodate"] = weekendsInRange(strtotime($from), strtotime(date($to)));
    $response["wdMonth"] = $response["daysInMonth"] - $response["weekendDaysMonth"];
    $response["wdToDate"] = $response["today"] - $response["weekendDaysTodate"]-1;
    $response["factor"] = 1 / ($response["wdToDate"] / $response["wdMonth"]);
    $data["Raloy"] = getDataRaloy($from, $to, $response["factor"]);
    $data["ZarKruse"] = getDataZK($from, $to, $response["factor"]);
    // return json_encode($data["ZarKruse"]);
    $consolidado = array_merge($data["Raloy"]["data"], $data["ZarKruse"]["data"]);
    $response2["totals"] = totals($consolidado, array("litros", "cantidad", "projection"));
    echo "<b>Real</b> $from $to <br/>";
    showReport($response2["totals"]["litros"]);
    echo "<br/>";
    echo "<b>Proyección al Cierre</b>  $from $to <br/>";
    showReport($response2["totals"]["projection"]);
    echo "<br/>";
    // return json_encode($response2);
    return "--End--";
}
function showReport($data)
{
    $total = 0;
    foreach ($data as $key => $value) {
        echo $key . ":", number_format($value, 2), "L <br/>";
        $total = $total + $value;
    }
    echo "<b>Total:", number_format($total, 2), "L </b><br/>";
}
function totals($data, $fieldsToAgg)
{
    $totals = array();
    foreach ($data as $d) {
        foreach ($fieldsToAgg as $fieldToAgg) {
            $key = $d["marca"];
            $total[$fieldToAgg][$key] = $totals[$fieldToAgg][$key] + $d[$fieldToAgg];
            $totals[$fieldToAgg][$key] = round($total[$fieldToAgg][$key], 2);
        }
    }
    return $totals;
}
function weekendsInRange($start, $end)
{
    // $start in timestamp
    // $end in timestamp


    $iter = 24 * 60 * 60; // whole day in seconds
    $count = 0; // keep a count of Sats & Suns

    for ($i = $start; $i <= $end; $i = $i + $iter) {
        if (Date('D', $i) == 'Sat' || Date('D', $i) == 'Sun') {
            $count++;
        }
    }
    return $count;
}
function getDataRaloy($from, $to, $factor)
{
    $url = "https://odoo-bi.raloy.com.mx/services/total_cliente_determinante_adblue.php?from=$from&to=$to";
    $respuesta = createArray("Raloy", $url, $factor);
    return $respuesta;
}
function getDataZK($from, $to, $factor)
{
    $url = "http://zar-kruse.com/intranet/ws/ventaszk.php?fec1=$from&fec2=$to";
    $marcas = array();
    $respuesta = createArray("ZarKruse", $url, $factor, $marcas);
    return $respuesta;
}
function findBrand($row, $fuente)
{
    $marca = $fuente;
    if ($fuente == "Raloy") {
        if (stripos($row["descripcion"], "MBADBLUE") !== false) {
            $arrmarca = array("marca" => "MB");
            $marca = "MB";
        } else if (stripos($row["descripcion"], "TRP ") !== false) {
            $arrmarca = array("marca" => "PACCAR");
            $marca = "PACCAR";
        } else if (stripos($row["descripcion"], "FLRT") !== false) {
            $arrmarca = array("marca" => "INTERNATIONAL");
            $marca = "INTERNATIONAL";
        } else if (stripos($row["descripcion"], "CUMMINS") !== false) {
            $arrmarca = array("marca" => "CUMMINS");
            $marca = "CUMMINS";
        } else if (stripos($row["cliente"], "DAIMLER TRUCKS") !== false) {
            $arrmarca = array("marca" => "MB FF");
            $marca = "MB FF";
        } else if (stripos($row["cliente"], "AUDI") !== false) {
            $arrmarca = array("marca" => "AUDI FF");
            $marca = "AUDI FF";
        } else if (stripos($row["cliente"], "Volvo") !== false) {
            $marca = "VOLVO";
            $arrmarca = array("marca" => "VOLVO");
        } else {
            $marca = "VD SKYBLUE";
            $arrmarca = array("marca" => "VENTA DIRECTA SKYBLUE");
        }
    } else if ($fuente == "ZarKruse") {
        if (stripos($row["descripcion"], "SKYBLUE") !== false) {
            $marca = "DISTRIBUIDORES SKYBLUE";
        } else {
            $marca = "ND";
        }
    }
    return $marca;
}
function createArray($fuente, $file, $factor = 1)
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
    $respuesta["data"] = array();
    if (count($data) > 0) {
        foreach ($data as $row) {
            $respuesta["originalRow"][] = $row;
            // echo json_encode($row);
            $marca = findBrand($row, $fuente);
            $addToData = (array("projection" => $row["litros"] * $factor, "marca" => $marca, "fuente" => $fuente));
            $newData = array_merge($row, $addToData);
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
