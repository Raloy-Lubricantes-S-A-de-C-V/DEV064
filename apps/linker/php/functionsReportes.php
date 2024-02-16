<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
require_once("../../../php/conexion.php");

$MySQLerrors = [];

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;



function getSabana() {
    $numArtPrincipal = $_GET["material"];
    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];

    $respuesta["tabla"] = "";
    $respuesta["errors"] = "";
    $respuesta["status"] = 1;

    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["errors"] = $mysqli->connect_error; //Error
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }
    $query = "SELECT 
ligue.fechaZarpe,
ligue.idLink,
ligue.statusLinker,
ligue.numOC ,
ligue.numArtPrincipal,
  artPrin.totPrin cant,
  SUM(r.TotalSinIVA * det.apportion / det.tcToUSD) subtotUSD,
  SUM(r.TotalSinIVA * det.apportion / det.tcToUSD) / artPrin.totPrin unitUSD 
FROM
  z_linker_main ligue,
  z_linker_main_detalles det,
  FRemisionProveedor r,
  (SELECT 
    Pedido NumPedido,
    Producto,
    SUM(cantiRecibida) totPrin 
  FROM
    FRemisionProveedor WHERE Status<>'C'
  GROUP BY Pedido,
    Producto) artPrin 
WHERE det.Linea = r.Line 
  AND det.numFact = r.Factura 
  AND det.CveProvFact = r.Proveedor 
  AND ligue.idLink=det.idLink
  AND artPrin.NumPedido = ligue.numOC 
  AND artPrin.Producto = ligue.numArtPrincipal 
  AND det.cveMP = r.Producto 
  AND (
    det.numRecibo<=0 
    OR det.numRecibo = r.NumRecibo
  ) 
  AND r.STATUS <> 'C' 
  AND ligue.fechaZarpe BETWEEN '$fec1' AND '$fec2'
GROUP BY ligue.fechaZarpe,
ligue.idLink,
ligue.statusLinker,
ligue.numOC ,
ligue.numArtPrincipal ORDER BY ligue.fechaZarpe DESC";
// $query = "SELECT 
//ligue.idLink,
//  ligue.numOC,
// ligue.numArtPrincipal,
// ligue.fechaZarpe,
//  artPrin.totPrin cant,
//  SUM(r.TotalSinIVA * det.apportion / det.tcToUSD) subtotUSD,
//  SUM(r.TotalSinIVA * det.apportion / det.tcToUSD) / artPrin.totPrin unitUSD 
//FROM
//  z_linker_main ligue,
//  z_linker_main_detalles det,
//  z_linker_tipoGastos clasif,
//  FRemisionProveedor r,
//  (SELECT 
//    NumPedido,
//    Producto,
//    SUM(FOC.CantiDada) totPrin 
//  FROM
//    FOC 
//  GROUP BY NumPedido,
//    Producto) artPrin 
//WHERE det.idTipoGasto = clasif.idTgl 
//  AND det.Linea = r.Line 
//  AND det.numFact = r.Factura 
//  AND det.CveProvFact = r.Proveedor 
//  AND ligue.idLink=det.idLink
//  AND artPrin.NumPedido = ligue.numOC 
//  AND artPrin.Producto = ligue.numArtPrincipal 
//  AND det.cveMP = r.Producto 
//  AND (
//    det.numRecibo<=0 
//    OR det.numRecibo = r.NumRecibo
//  ) 
//  AND r.STATUS <> 'C' 
//GROUP BY ligue.idLink
//ORDER BY ligue.fechaZarpe DESC";
    $result = $mysqli->query($query);
    if ($mysqli->errno) {
        $respuesta["errors"] = $mysqli->error;
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }
    if($result->num_rows==0){
        $respuesta["errors"] = "No hay datos con el filtro seleccionado";
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }
    $respuesta["tabla"].="<thead>";
    $respuesta["tabla"].="<tr>";
    $respuesta["tabla"].="<th>Fecha Zarpe</th>";
    $respuesta["tabla"].="<th>OC</th>";
    $respuesta["tabla"].="<th>Id Linker</th>";
    $respuesta["tabla"].="<th>Status Linker</th>";
    $respuesta["tabla"].="<th>Articulo</th>";
    $respuesta["tabla"].="<th>Cantidad</th>";
    $respuesta["tabla"].="<th>USD</th>";
    $respuesta["tabla"].="<th>USD Unitario</th>";
    $respuesta["tabla"].="</tr>";
    $respuesta["tabla"].="</thead>";
    $respuesta["tabla"].="<tbody>";
    while ($row = $result->fetch_array()) {
        $respuesta["tabla"].="<tr>";
        $respuesta["tabla"].="<td>" . $row[0] . "</td>";
        $respuesta["tabla"].="<td>" . $row[3] . "</td>";
        $respuesta["tabla"].="<td>" . $row[1] . "</td>";
        $respuesta["tabla"].="<td>" . $row[2] . "</td>";
        $respuesta["tabla"].="<td>" . $row[4] . "</td>";
        $respuesta["tabla"].="<td class='numeric'>" . $row[5] . "</td>";
        $respuesta["tabla"].="<td class='currency'>" . $row[6] . "</td>";
        $respuesta["tabla"].="<td class='currency'>" . $row[7] . "</td>";
        $respuesta["tabla"].="</tr>";
    }
    $respuesta["tabla"].="</tbody>";
    return json_encode($respuesta);
}
Function getLinkedCN() {//Notas de Crédito
    $numArticulo = $_GET["material"];
    $numOrden = $_GET["oc"];

    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["msj"] = "";

    if ($mysqli->connect_errno) {
        $respuesta["msj"] = $mysqli->connect_error; //Error
        return json_encode($respuesta);
    }

    $query = "SELECT 
  pp.NumCheque NumNC,
  factsLinker.numOC,
  factsLinker.proveedor,
  factsLinker.fact,
  factsLinker.cant,
  factsLinker.apportion,
  pp.Importe * factsLinker.apportion / factsLinker.tc USD,
  pp.Importe * factsLinker.apportion / factsLinker.tc / factsLinker.cant USD_UNIT 
FROM
  FPagosProveedor pp,
  (SELECT 
    det.idLink,
    ligue.numOC,
    clasif.tipoGastoLink categ,
    CONCAT(
      '[',
      det.cveMP,
      '] ',
      art.descripcion
    ) matPrima,
    det.CveProvFact cveProv,
    CONCAT(
      '[',
      det.CveProvFact,
      '] ',
      pr.NomProvee
    ) proveedor,
    det.numFact fact,";
//    IF(
//      r.NumRecibo > 0 AND r.Producto=artPrin.Producto,
//      r.CantiRecibida,
$query.="artPrin.totPrin cant,";
//    ) cant,
$query.="SUM(
      r.TotalSinIVA * det.apportion / det.tcToUSD
    ) subtotUSD,
    SUM(
      r.TotalSinIVA * det.apportion / det.tcToUSD
    ) / IF(
      r.NumRecibo > 0 AND r.Producto=artPrin.Producto,
      r.CantiRecibida,
      artPrin.totPrin
    ) unitUSD,
    det.tcToUSD tc,
    CONCAT(
      '$ ',
      CONVERT(FORMAT(TotalSinIVA, 2) USING utf8),
      ' ',
      r.Moneda
    ) montoOrig,
    IF(
      r.StatusPago = '',
      'NP',
      r.StatusPago
    ) pago,
    det.idLinkDetails,
    det.apportion 
  FROM
    z_linker_main_detalles det,
    z_linker_main ligue,
    z_linker_tipoGastos clasif,
    (SELECT 
      MPNumArticulo AS cve,
      MPDesc AS descripcion,
      'mp' AS tabla,
      InvMatPrima.MPUniMedida unidad 
    FROM
      InvMatPrima 
      UNION
      SELECT 
        PTNumArticulo AS cve,
        PTDesc AS descripcion,
        'pt' AS tabla,
        InvProdTerm.PTUniMedida unidad 
      FROM
        InvProdTerm) art,
      FProveedor pr,
      FRemisionProveedor r,
      (SELECT 
    Pedido NumPedido,
    Producto,
    SUM(cantiRecibida) totPrin 
  FROM
    FRemisionProveedor WHERE Status<>'C'
  GROUP BY Pedido,
    Producto) artPrin 
    WHERE det.idTipoGasto = clasif.idTgl 
      AND art.cve = det.cveMP 
      AND pr.CveProvee = det.CveProvFact 
      AND det.Linea = r.Line 
      AND det.numFact = r.Factura 
      AND det.CveProvFact = r.Proveedor 
      AND ligue.idLink = det.idLink 
      AND artPrin.NumPedido = ligue.numOC 
      AND artPrin.Producto = ligue.numArtPrincipal 
      AND det.cveMP = r.Producto 
      AND (
        det.numRecibo <= 0 
        OR det.numRecibo = r.NumRecibo
      ) 
      AND r.STATUS <> 'C' 
    GROUP BY clasif.idTgl,
      det.cveMP,
      det.CveProvFact,
      det.numFact,
      idLinkDetails) factsLinker 
  WHERE pp.Factura = factsLinker.fact 
    AND pp.Proveedor = factsLinker.cveProv 
    AND pp.TipoPago = 3 
 GROUP BY numOC,NumNC";

    $trs = "";
    $totUsd = 0;
    $totUsdUnit = 0;
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $trs.="<tr><td>" . $row["NumNC"] . "</td><td>" . $row["proveedor"] . "</td><td>" . $row["fact"] . "</td><td class='numeric'>" . $row["cant"] . "</td><td>" . $row["apportion"] . "</td><td class='currency'>" . $row["USD"] . "</td><td class='currency'>" . $row["USD_UNIT"] . "</td></tr>";

            $totUsd+=$row["USD"];
            $totUsdUnit+=$row["USD_UNIT"];
        }
        $respuesta["trs"] = $trs;
        $respuesta["usd"] = $totUsd;
        $respuesta["usdUnit"] = $totUsdUnit;
    } else {
        $respuesta["msj"] = $result->error . $query;
    }

    return json_encode($respuesta);
}