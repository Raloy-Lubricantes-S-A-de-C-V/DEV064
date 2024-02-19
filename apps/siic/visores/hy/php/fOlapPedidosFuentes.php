<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
$check = session_check($_GET["t"]);
if ($check != 1) {
    echo json_encode(array("status" => "0", "error" => "Sesión expirada"));
    return;
}

$f = $_GET["f"];
$response = call_user_func($f);
echo $response;

function getData()
{
    $json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/intranet/ws/fuentes.json");

    $data = json_decode($json, true);
    $respuesta = [];
    $array=[];
    foreach ($data["pedidos"] as $fuente => $datos) {
        $respuesta=obtenerDatos($fuente, $datos,$respuesta);
        
    }
    return json_encode(array("status"=>1,"jsondata"=>$respuesta));
}

function obtenerDatos($fuente, $datos,$respuesta)
{

    $datetime = date("Y-m-d H:i:s");
    //get new data from source
    $ip = gethostbyname($datos["url"]);
    $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
    $content = file_get_contents($ip, false, $context);
    $data = json_decode($content, true);
    $fullData=[];
    foreach($data as $row){
        $row["fuente"]=$fuente;
        $row["litros"]=str_replace(",","",$row["litros"]);
        $fullData[]=$row;
    }
    $r=array_merge($fullData,$respuesta);
    return $r;
}

function escape_values($v)
{
    if (is_numeric($v)) {
        $str = "'" . $v . "'";
    } else {
        $v = str_replace("'", " ", $v);
        $pos = strpos($v, "/");
        if ($pos == true) {
            $datePart = substr($v, 0, 10);
            if (strlen($v) > 10) {
                $timePart = substr($v, -8, 8);
            } else {
                $timePart = "";
            }

            $y = substr($datePart, -4, 4);
            $m = substr($datePart, -7, 2);
            $d = substr($datePart, -10, 2);
            $v = $y . "-" . $m . "-" . $d . " " . $timePart;
        }
        $str = "'" . $v . "'";
    }
    return $str;
}
