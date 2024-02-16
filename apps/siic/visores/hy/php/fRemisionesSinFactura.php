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
    $query = <<<SQL
SELECT 
    NumRemi Remision,
    IF(AlmacenR="","STG",AlmacenR) Almacen,
    FechElabo Fecha,
    STATUS,
    r.Cliente Cliente_Cve,
    c.NomCliente Cliente_Nombre,
    Producto Prod_Cve,
    Acabado Prod_Acab,
    DescArti Prod_Descr,
    Divisa Moneda,
    SUM(CantiDada * CDV) Litros,
    SUM(TotalSinIVA) Importe 
FROM
    FRemision r 
    LEFT JOIN
    InvProdTerm pt 
    ON r.Acabado = pt.PTTipo 
    AND r.Producto = pt.PTNumArticulo 
    LEFT JOIN
    FClientes c 
    ON r.Cliente = c.CveCliente  
WHERE STATUS NOT IN ("F", "C") 
GROUP BY NumRemi,
    AlmacenR,
    Cliente,
    Producto,
    Acabado,
    DescArti,
    Divisa 
ORDER BY fechElabo DESC 
SQL;

    $respuesta["status"] = 0;
    $respuesta["error"] = "";

    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["error"] = "Error de Conexión";
    }
    if ($result = $mysqli->query($query)) {
        $mysqli->close();
        $dataconn = dataconn("intranet");
        $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
        while ($row = $result->fetch_assoc()) {
            $remision = $row["Remision"];
            $buscaNumRecibo = "SELECT GROUP_CONCAT(DISTINCT(e.id_entrega)) folio,GROUP_CONCAT(DISTINCT(e.numReciboCliente)) numRecibo FROM smartRoad_pre_ruteo pr INNER JOIN smartRoad_entregas e ON pr.id_entrega=e.id_entrega WHERE pr.remisionZK='$remision'";
            $resNumRecibo=$mysqli->query($buscaNumRecibo);
            $rowRecibo=$resNumRecibo->fetch_assoc();
            $row["numRec"]=$rowRecibo["numRecibo"];
            $row["folioSmartRoad"]=$rowRecibo["folio"];
            $respuesta["jsondata"][] = $row;
        }
        $respuesta["status"] = 1;
    } else {
        $respuesta["error"] = $mysqli->error;
    };
    return json_encode($respuesta);
}
