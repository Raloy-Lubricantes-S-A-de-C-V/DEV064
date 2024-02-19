<?php

header('Access-Control-Allow-Origin: http://www.zar-kruse.com');
date_default_timezone_set('America/Mexico_City');

//$host = "dbp.raloy.com.mx";
//$user = "adblue";
//$pass = "Veoos133";
//$db = "adblue_scp";
//$port = "3385";
require("../php/conexion.php");
$fec1 = $_GET["fec1"];
$fec2 = $_GET["fec2"];

$dataconn = dataconn("scpzar");
$mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

if ($mysqli->connect_errno) {
    echo "Error de conexión:" . $mysqli->connect_errno;
    exit();
}
$query = <<<SQL
        SELECT 
            r.NumRemi AS albaran,
            r.CantiDada AS cantidad,
            CONCAT(r.Producto, " ", r.Acabado) AS clave,
            CONCAT(c.CveCliente, ' ', c.NomCliente) AS cliente,
            r.DescArti AS descripcion,
            pt.Marca2 empaque,
            e.Determinante enviado,
            e.Sucursal,
            IF(c.Vendedor2="","Zar Kruse",c.Vendedor2) AS equipo,
            CONCAT(r.Factura, r.SerieF) AS factura,
            pt.PTCatalogo AS familia,
            r.FechElabo AS fecha,
            r.FechFactura AS fechafactura,
            r.totalSinIVA AS subtotal,
            r.totalSinIVA/r.CantiDada price_unit,
            p.FechElabo AS fechaso,
            "Mexico" AS geo_country,
            IFNULL(e.Estado, c.Estado) geo_estado,
            IFNULL(e.Ciudad, c.Municipio) geo_municipio,
            CONCAT(r.Cliente, "@@", r.Enviado) id_enviado,
            (r.CantiDada * pt.CDV) litros,
            p.NumRemi origin,
            p.PedCli AS pedidocliente,
            pt.PTUniMedida udm,
            r.Divisa Moneda
        FROM
            FRemision r 
            INNER JOIN
            InvProdTerm pt 
            ON r.Producto = pt.PTNumArticulo 
            AND r.Acabado = pt.PTTipo 
            INNER JOIN
            FClientes c 
            ON r.Cliente = c.CveCliente 
            LEFT JOIN
            FClienteEnvio e 
            ON r.Cliente = e.Cliente 
            AND r.Enviado = e.Determinante 
            LEFT JOIN
            FPedidos p 
            ON r.PedidoSist = p.NumPedido 
            AND r.Cliente = p.Cliente 
            AND r.Pedido = p.PedCli 
            AND r.Producto = p.Producto 
        WHERE r.FechElabo >= "$fec1" 
            AND r.FechElabo <= "$fec2" 
            AND pt.PTCatalogo = "SKYBLUE" 
            AND r.STATUS <> "C"
            AND r.Cliente NOT IN ("1","1P")
SQL;
if (!$mysqli->set_charset("utf8")) {
    echo "Error loading character set utf8: %s\n" . $mysqli->error;
    exit();
}
$result = $mysqli->query($query);
if ($mysqli->error) {
    echo "Error en query:" . $mysqli->error;
    exit();
}
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
