<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
$check = session_check($_GET["t"]);
if ($check != 1) {
    die(json_encode(array("status" => "0", "error" => "Session: " . $check)));
}
if (!array_key_exists("fx", $_GET)) {
    die(json_encode(array("status" => "0", "error" => "Function not found")));
}

echo call_user_func($_GET["fx"]);

function queryResult($conn, $sql)
{
    $dataconn = dataconn($conn);
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        return json_encode(array("status" => "0", "error" => $mysqli->connect_error));
    }
    $result = $mysqli->query($sql);
    if ($mysqli->errno) {
        return json_encode(array("status" => "0", "error" => $mysqli->error));
    }
    $num_rows = $result->num_rows;
    if ($num_rows == 0) {
        return json_encode(array("status" => "0", "error" => "No data"));
    }
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $result->free();
    $mysqli->close();
    return array("status" => 1, "num_rows" => $num_rows, "data" => $data);
}
function fetchPTInventory()
{
    $sql = <<<SQL
        SELECT 
            pt.Marca,
            pt.Marca2 Envase,
            CONCAT(PTNumArticulo, " ", PTTipo) Clave,
            PTDesc Producto,
            PTCantidad Cantidad,
            PTUniMedida Unidad 
        FROM
            InvProdTerm pt 
        WHERE pt.PTCatalogo = "SKYBLUE" 
        ORDER BY pt.Marca,
            pt.Marca2,
            PTNumArticulo,
            PTDesc,
            PTTipo,
            PTUniMedida  
SQL;

    $respuesta = queryResult("scpzar", $sql);
    return json_encode($respuesta);
}
