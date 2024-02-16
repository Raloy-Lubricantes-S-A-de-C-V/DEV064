<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
$check = session_check($_GET["t"]);
if ($check != 1) {
    echo json_encode(array("status" => "0", "error" => "Session: " . $check));
    return;
}
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;
function get_data()
{
    $sql = "SELECT 
                le.CodPT cve,
                le.TipoAcabado acabado,
                pt.PTDesc descr,
                ROUND(le.Cantidad,5) cant_req,
                IFNULL(
                    mp.MPUniMedida,
                    ptreq.PTUniMedida
                ) unidad_req,
                le.PTEnsamble cve_req,
                IFNULL(mp.MPDesc, ptreq.PTDesc) descr_req,
                IF(le.PToMP = 1, 'MP', 'PT') tipo_req 
            FROM
                FListaEnsamble le 
                LEFT JOIN
                InvProdTerm pt 
                ON le.CodPT = pt.PTNumArticulo 
                AND le.TipoAcabado = pt.PTTipo 
                LEFT JOIN
                InvMatPrima mp 
                ON le.PTEnsamble = mp.MPNumArticulo 
                AND le.PToMP = 1 
                LEFT JOIN
                InvProdTerm ptreq 
                ON le.PTEnsamble = ptreq.PTNumArticulo 
                AND le.PToMP <> 1;";

    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    $respuesta = array("status" => 0, "data" => "");
    
    if ($mysqli->connect_error) {
        return $mysqli->connect_error;
    }
    $result = $mysqli->query($sql);

    if ($mysqli->error) {
        $respuesta = array("status" => 0, "data" => "");
    } else {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $respuesta = array("status" => 1, "data" => $data);
    }
    return json_encode($respuesta);
}
