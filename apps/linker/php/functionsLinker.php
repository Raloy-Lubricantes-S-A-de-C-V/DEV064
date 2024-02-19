<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');

require_once "../../../php/conexion.php";

$MySQLerrors = [];

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function revisaPermisoEdicion() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!array_key_exists("sessionInfo", $_SESSION) || !in_array(13, explode(",", $_SESSION["sessionInfo"]["strIdsPerms"]))) {
        $edicion = 1;
    } else {
        $edicion = 0;
    }
    return $edicion;
}

function llenaOCs() {
    $cveProd = $_GET["cveProd"];
    $respuesta["errors"] = "";
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }
    $query = <<<SQL
        SELECT 
            FOC.NumPedido,
            LEFT(p.NomProvee,5) prov,
            FOC.FechElabo,
            FOC.CantiOrden qty,
            FOC.Moneda,
            art.unidad
        FROM
            FOC,
            FProveedor p,
            (
                SELECT 
                  MPNumArticulo AS cve,
                  MPDesc AS descripcion,
                  "mp" AS tabla,
                  InvMatPrima.MPUniMedida unidad 
                FROM
                  InvMatPrima 
            UNION
                SELECT 
                  PTNumArticulo AS cve,
                  PTDesc AS descripcion,
                  "pt" AS tabla,
                  InvProdTerm.PTUniMedida unidad 
                FROM
                  InvProdTerm
            ) art 
        WHERE FOC.Producto = art.cve  
            AND FOC.Proveedor = p.CveProvee 
            AND FOC.Producto = '$cveProd' 
        ORDER BY FOC.FechElabo DESC
SQL;

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows > 0) {
            //CON DATOS
            $options = "<option selected='selected'></option>";
            while ($row = $result->fetch_assoc()) {
                $options.= "<option value='" . $row["NumPedido"] . "'>" . $row["NumPedido"] . " :" . utf8_encode($row["prov"]) . " (" . utf8_encode($row["Moneda"]) . ") " . $row["FechElabo"] . " " . number_format($row["qty"], 2) . " " . $row["unidad"] . " " . "</option>";
            }
            $respuesta["options"] = $options;
        }
    } else {
        $respuesta["errors"] = "Error";
    }
    return json_encode($respuesta);
}

function getPOInfo() {
    $numArticulo = $_GET["material"];
    $numOrden = $_GET["PO"];
    $respuesta["mensaje"] = "";
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }
    $query = <<<SQL
        SELECT 
  FOC.NumPedido,
  FOC.FechElabo,
  FOC.CentroCosto,
  FOC.Proveedor,
  p.NomProvee,
  FOC.Producto,
  art.descripcion,
  FOC.CantiOrden,
  art.unidad,
  FOC.Usuario,
  FOC.Precio,
  (FOC.Precio * FOC.CantiOrden) total,
  FOC.Moneda,
  FOC.Observa,
  IF(FOC.OCCERRADA=1 AND FOC.CantiDada=0,'Cancelada','Vigente') statusOC,
  linker.idLink,
  linker.statusLinker, 
  linker.fechaZarpe
FROM
  FOC 
  LEFT JOIN
  (SELECT 
    numOC,
    numArtPrincipal,
    idLink,
    fechaZarpe,
    statusLinker 
  FROM
    z_linker_main 
  GROUP BY numOC,
    numArtPrincipal) linker 
  ON linker.numOC = FOC.NumPedido 
  AND linker.numArtPrincipal = FOC.Producto,
  FProveedor p,
  (SELECT 
    MPNumArticulo AS cve,
    MPDesc AS descripcion,
    "mp" AS tabla,
    InvMatPrima.MPUniMedida unidad 
  FROM
    InvMatPrima 
    UNION
    SELECT 
      PTNumArticulo AS cve,
      PTDesc AS descripcion,
      "pt" AS tabla,
      InvProdTerm.PTUniMedida unidad 
    FROM
      InvProdTerm) art 
  WHERE FOC.Producto = art.cve 
    AND FOC.Proveedor = p.CveProvee 
    AND Producto = '$numArticulo' 
    AND NumPedido = '$numOrden'  
SQL;

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows == 1) {
            //CON DATOS
            $row = $result->fetch_assoc();
            $respuesta["matInfo"]["infoNumOC"] = $row["NumPedido"];
            $respuesta["matInfo"]["infofecOC"] = $row["FechElabo"];
            $respuesta["matInfo"]["infoCC"] = utf8_encode($row["CentroCosto"]);
            $respuesta["matInfo"]["tdCveProveedor"] = utf8_encode($row["Proveedor"]);
            $respuesta["matInfo"]["tdProveedor"] = utf8_encode($row["NomProvee"]);
            $respuesta["matInfo"]["tdMaterial"] = utf8_encode($row["Producto"]);
            $respuesta["matInfo"]["tdMaterialDesc"] = utf8_encode($row["descripcion"]);
            $respuesta["matInfo"]["tdCantidad"] = number_format($row["CantiOrden"], 2);
            $respuesta["matInfo"]["tdUnidad"] = utf8_encode($row["unidad"]);
            $respuesta["matInfo"]["tdUsuario"] = utf8_encode($row["Usuario"]);
            $respuesta["matInfo"]["tdPrecio"] = number_format($row["Precio"], 2);
            $respuesta["matInfo"]["tdTotal"] = number_format($row["total"], 2);
            $respuesta["matInfo"]["tdMoneda"] = $row["Moneda"];
            $respuesta["matInfo"]["tdObs"] = utf8_encode($row["Observa"]);
            $respuesta["matInfo"]["statusOC"] = utf8_encode($row["statusOC"]);
            $respuesta["matInfo"]["idLink"] = $row["idLink"];
            $respuesta["matInfo"]["tdStatusLinker"] = utf8_encode($row["statusLinker"]);
            $respuesta["matInfo"]["fechaZarpe"] = $row["fechaZarpe"];
        } else {
            $respuesta["mensaje"] = "No existen coincidencias";
        }
    } else {
        $respuesta["mensaje"] = "Error";
    }
    return json_encode($respuesta);
}

function getPedimentos() {
    $numArticulo = $_GET["material"];
    $numOrden = $_GET["oc"];
    $respuesta["mensaje"] = "";
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        return $mysqli->connect_error; //Error
    }
    $query = "SELECT DISTINCT(pedimento) ped FROM z_linker_main l WHERE l.numOC='$numOrden' AND l.numArtPrincipal='$numArticulo' AND l.clase_pedimento='A1'";
    $options["a1"] = "<option value='A1'>A1</option>";
    $options["r1"] = "";
    $options["r2"] = "";
    $lastPed = "";
    if ($result = $mysqli->query($query)) {
        $numrows = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $options["r1"].="<option value='R1_" . $row["ped"] . "'>R1: " . $row["ped"] . "</option>";
            $options["r2"].="<option value='R2_" . $row["ped"] . "'>R2: " . $row["ped"] . "</option>";
            $lastPed = $row["ped"];
        }
        $respuesta["ped"] = explode(" ", $lastPed);
        $respuesta["options"] = implode("", $options);
    } else {
        $respuesta["mensaje"].=$mysqli->error;
    }
    return json_encode($respuesta);
}

function getLinkedInvoices() {
    $numArticulo = $_GET["material"];
    $numOrden = $_GET["oc"];
    $respuesta["msj"] = "";
   $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    if ($mysqli->connect_errno) {
        $respuesta["msj"] = $mysqli->connect_error; //Error
        return json_encode($respuesta);
    }

    $query = "SELECT 
  clasif.tipoGastoLink categ,
  CONCAT('[',det.cveMP, '] ', art.descripcion) matPrima,
  CONCAT('[',
    det.CveProvFact,
    '] ',
    pr.NomProvee
  ) proveedor,
  det.numFact fact,
  IF(r.NumRecibo>0 AND r.Producto=artPrin.Producto,r.CantiRecibida,artPrin.totPrin) cant,
  SUM(r.TotalSinIVA * det.apportion / det.tcToUSD) subtotUSD,
  SUM(r.TotalSinIVA * det.apportion / det.tcToUSD) / IF(r.NumRecibo>0 AND r.Producto=artPrin.Producto,r.CantiRecibida,artPrin.totPrin) unitUSD ,
 det.tcToUSD tc,
 CONCAT('$ ',CONVERT(FORMAT(TotalSinIVA, 2) USING utf8),' ',r.Moneda) montoOrig,
 IF(r.StatusPago='','NP',r.StatusPago) pago,
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
  AND ligue.numOC = '$numOrden' 
  AND ligue.numArtPrincipal = '$numArticulo' 
  AND ligue.idLink=det.idLink
  AND artPrin.NumPedido = ligue.numOC 
  AND artPrin.Producto = ligue.numArtPrincipal 
  AND det.cveMP = r.Producto 
  AND (
    det.numRecibo<=0  
    OR det.numRecibo = r.NumRecibo
  ) 
  AND r.STATUS <> 'C' 
GROUP BY clasif.idTgl,
  det.cveMP,
  det.CveProvFact,
  det.numFact,idLinkDetails";
    //RESULTADOS DEL QUERY
    $respuesta["trs"] = "";
    $respuesta["query"] = $query;
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows > 0) {
            //CON DATOS
            while ($row = $result->fetch_assoc()) {
                $respuesta["trs"].="<tr>";
                $respuesta["trs"].="<td>" . utf8_encode($row["categ"]) . "</td>";
                $respuesta["trs"].="<td>" . utf8_encode($row["matPrima"]) . "</td>";
                $respuesta["trs"].="<td>" . utf8_encode($row["proveedor"]) . "</td>";
                $respuesta["trs"].="<td>" . $row["fact"] . "</td>";
                $respuesta["trs"].="<td class='numeric'>" . round($row["cant"], 2) . "</td>";
                $respuesta["trs"].="<td class='currency'>" . round($row["subtotUSD"], 2) . "</td>";
                $respuesta["trs"].="<td class='currency'>" . round($row["unitUSD"], 2) . "</td>";
                $respuesta["trs"].="<td class='currency'>" . round($row["tc"], 4) . "</td>";
                $respuesta["trs"].="<td class='perc'>" . round($row["apportion"], 2) . "</td>";
                $respuesta["trs"].="<td style='text-align:right;'>" . $row["montoOrig"] . "</td>";
                $respuesta["trs"].="<td style='text-align:center;'>" . $row["pago"] . "</td>";
                $respuesta["trs"].="<td style='text-align:right;'>" . $row["idLinkDetails"] . "</td>";
                $respuesta["trs"].="<td class='onlyEditUsers clickable unlink clickableSH' idLink='" . $row["idLinkDetails"] . "'><i class='fa fa-unlink'></i> Desenlazar</td>";
                $respuesta["trs"].="</tr>";
            }
        } else {
            $respuesta["trs"] = "Sin Datos";
        }
    } else {
        $respuesta["msj"] = $result->error . " $query";
    }
    return json_encode($respuesta);
}

function getSuppliers() {
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }
    $query = "SELECT NomProvee nombre,CveProvee cve FROM FProveedor WHERE NomProvee<>'' AND NomProvee NOT LIKE '%NO USAR%' ORDER BY CveProvee";
    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        //CON DATOS
        $options = "<option selected='selected'></option>";
        while ($row = $result->fetch_assoc()) {
            $options.="<option value='" . $row["cve"] . "'>" . $row["cve"] . " - " . $row["nombre"] . "</option>";
        }
    }
    return $options;
}

function getInvoices() {
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }

    $fact = $_GET["fact"];
    $query = "SELECT 
  Factura NumFactura,
  r.Proveedor cveProv,
  p.NomProvee,
  SUM(TotalSinIVA) subtot,
  Moneda 
FROM
  FRemisionProveedor r,
  FProveedor p
WHERE r.Proveedor = p.CveProvee 
  AND FechElabo >= '2015-09-01' 
  AND Factura LIKE '%$fact%' 
  AND STATUS <> 'C' 
GROUP BY Factura,
  Proveedor 
ORDER BY Factura,
  Proveedor,
  FechElabo DESC ";
    $table = "";
    //RESULTADOS DEL QUERY
    $respuesta["errors"] = "";
    if ($result = $mysqli->query($query)) {
//        //CON DATOS
        while ($row = $result->fetch_assoc()) {
            $table.="<tr>";
            $table.="<td><button class='getInvoiceInfoBtn' cveProv='" . $row["cveProv"] . "' fact='" . $row["NumFactura"] . "'>Mostrar</button></td>";
            $table.="<td>" . $row["NumFactura"] . "</td>";
            $table.="<td>" . $row["cveProv"] . " " . utf8_encode($row["NomProvee"]) . "</td>";
            $table.="<td class='currency'>" . $row["subtot"] . "</td>";
            $table.="<td>" . $row["Moneda"] . "</td>";
            $table.="</tr>";
        }
    } else {
        $respuesta["errors"] = $mysqli->error;
    }
    $respuesta["numRows"] = $result->num_rows;
    $respuesta["table"] = $table;
    $respuesta["query"] = $query;
    return json_encode($respuesta);
}

function getInvoiceInfo() {
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }
    $cveProveedor = $_GET["cve"];
    $factura = $_GET["fact"];
    $query = "SELECT 
  Proveedor,
  Factura,
  Line,
  Producto,
  DescArti,
  CantiRecibida,
  TotalSinIVA,
  Usuario,
  if(StatusPago='','NP',StatusPago) pago,
  Moneda,
  TipoCamR,
  numRecibo recibo
FROM
  FRemisionProveedor 
WHERE Factura = '$factura'
  AND Proveedor = '$cveProveedor' 
  AND STATUS <> 'C'
  AND CONCAT(Proveedor,' ',Factura,' ',Line, ' ',numRecibo) NOT IN (SELECT DISTINCT(concatenado) FROM 
(SELECT CONCAT(CveProvFact,' ',numFact,' ',Linea,' ',numRecibo) concatenado,SUM(apportion) sumApportion FROM z_linker_main_detalles GROUP BY CONCAT(CveProvFact,' ',numFact,' ',Linea,' ',numRecibo)) todo
WHERE todo.sumApportion>=1)
ORDER BY Producto,TotalSinIVA Desc";
    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        //CON DATOS
        $table = "<table>";
        $table.="<thead>"
                . "<tr><th colspan='12'>Selección de partidas de factura</th></tr>"
                . "<tr><th colspan='2'></th><th>% Ap</th><th>Factura</th><th>Cve Art</th></th><th>Desc Art</th><th>Cant.</th><th>SubTotal</th><th>$</th><th>Pago</th><th>T.C.</th><th>Usuario</th><th>Recibo</th></tr>"
                . "</thead>";
        $table.="<tbody>";
        while ($row = $result->fetch_assoc()) {
            $table.="<tr  prov='" . $row["Proveedor"] . "' fact='" . $row["Factura"] . "' line='" . $row["Line"] . "' cveMP='" . $row["Producto"] . "' numRecibo='" . $row["recibo"] . "'><td><input type='checkbox'/></td>"
                    . "<td><select class='clasifGasto'></select></td>"
                    . "<td><input class='apportion' type='number' style='width:40px;' value='100' min='1' max='100'/>%</td>"
                    . "<td>" . $row["Factura"] . "</td>"
                    . "<td>" . $row["Producto"] . "</td>"
                    . "<td>" . $row["DescArti"] . "</td>"
                    . "<td class='numeric'>" . $row["CantiRecibida"] . "</td>"
                    . "<td class='currency'>" . $row["TotalSinIVA"] . "</td>"
                    . "<td>" . $row["Moneda"] . "</td>"
                    . "<td>" . $row["pago"] . "</td>"
                    . "<td class='currency'>" . $row["TipoCamR"] . "</td>"
                    . "<td>" . $row["Usuario"] . "</td>"
                    . "<td>" . $row["recibo"] . "</td>"
                    . "</tr>";
        }
        $table.="</tbody>";
        $table.="</table>";
    } else {
        return $mysqli->error;
    }
    return $table;
}

function checkCabecera() {
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }
    //Obteniendo valores recibidos
    $numOC = $_GET["numOC"];
    $cveArt = $_GET["cveArt"];
    $fechaZarpe = $_GET["fechaZarpe"];

    //mensaje vacío por default
    $respuesta["msj"] = "";

    //Revisando si existe ya la cabecera y obteniendo el idLink, si no existe, se inserta.
    $queryCheckCabecera = <<<SQL
        SELECT 
            idLink
        FROM
            z_linker_main 
        WHERE
            numOC='$numOC'
            AND numArtPrincipal='$cveArt'
SQL;
    $result = $mysqli->query($queryCheckCabecera);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $respuesta["id"] = $row["idLink"];
        return json_encode($respuesta);
    } else {
        $queryInsertCabecera = <<<SQL
            INSERT INTO 
                z_linker_main 
                (
                    numOC,
                    numArtPrincipal,
                    statusLinker,
                    fechaZarpe
                ) 
            VALUES
                (
                    "$numOC",
                    "$cveArt",
                    "Editando",
                    "$fechaZarpe"
                ) 
SQL;
        if ($mysqli->query($queryInsertCabecera)) {
            $respuesta["id"] = $mysqli->insert_id;
            return json_encode($respuesta);
        } else {
            $respuesta["msj"] = array("error" => $mysqli->error, "query" => $queryInsertCabecera);
            return json_encode($respuesta);
        }
    }
}

function saveInvoices() {
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }

    //Obteniendo valores recibidos
    $rows = $_GET["rows"];
    $values=[];
    foreach($rows as $row){
        $values[]="(".$row["idLink"].",'".$row["numFact"]."','".$row["CveProvFact"]."',".$row["Linea"].",'".$row["cveMP"]."',".$row["numRecibo"].",".$row["tcToUSD"].",".$row["idTipoGasto"].",".$row["apportion"].",'".utf8_decode($row["usuarioLigue"])."')";
    }
    $strValues=implode(",",$values);
    $query = <<<SQL
            INSERT INTO 
                z_linker_main_detalles 
                    (
                        idLink,
                        numFact,
                        CveProvFact,
                        Linea,
                        cveMP,
                        numRecibo,
                        tcToUSD,
                        idTipoGasto,
                        apportion,
                        usuarioLigue
                    ) 
            VALUES 
                    $strValues
SQL;
    if ($mysqli->query($query)) {
        $respuesta["msj"] = "Updated";
    } else {
        $MySQLerrors[] = array("type" => "query execution", "error" => $mysqli->error, "query" => $query); //Error
        $respuesta["msj"] = $MySQLerrors;
    }
    return json_encode($respuesta);
}

function unlinkInvoices() {
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }
    $idLink = $_GET["idLink"];
    $query = "DELETE FROM z_linker_main_detalles where idLinkDetails=$idLink";
    if ($mysqli->query($query)) {
        $respuesta["msj"] = "Listo";
    } else {
        $MySQLerrors[] = array("type" => "query execution", "error" => $mysqli->error, "query" => $query); //Error
        $respuesta["errors"] = $MySQLerrors;
    }
    return json_encode($respuesta);
}

function edit_saveStatus() {
    $numArticulo = $_GET["material"];
    $numOrden = $_GET["oc"];
    $stat = $_GET["stat"];
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["errors"] = "";
    if ($mysqli->connect_errno) {
        $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
        $respuesta["errors"] = $MySQLerrors;
        return json_encode($respuesta);
    }
    $query = "UPDATE z_linker_main SET statusLinker='$stat' WHERE numOC='$numOrden' AND numArtPrincipal='$numArticulo';";
    if (!$mysqli->query($query)) {
        $MySQLerrors[] = array("type" => "query execution", "error" => $mysqli->error, "query" => $query); //Error
        $respuesta["errors"] = $MySQLerrors;
    }
    return json_encode($respuesta);
}

function getOptionsClasif() {
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["errors"] = $mysqli->connect_error; //Error
        return json_encode($respuesta);
    }
    $query = "SELECT idTgl,tipoGastoLink tgl FROM z_linker_tipoGastos ORDER BY idTgl";
    //RESULTADOS DEL QUERY
    $opts = "<option value=''>--Categoría--</option>";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $opts.="<option value='" . $row["idTgl"] . "'>" . utf8_encode($row["tgl"]) . "</option>";
        }
        $respuesta["opts"] = $opts;
    } else {
        $respuesta["errors"] = $result->error . $query;
    }
    return json_encode($respuesta);
}

function getSummary() {
    $numArticulo = $_GET["material"];
    $numOrden = $_GET["PO"];
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["errors"] = "";
    if ($mysqli->connect_errno) {
        $respuesta["errors"] = $mysqli->connect_error; //Error
        return json_encode($respuesta);
    }
//    $query = "SELECT 
//  clasif.tipoGastoLink categ,
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
//  AND ligue.numOC = '$numOrden' 
//  AND ligue.numArtPrincipal = '$numArticulo'
//  AND ligue.idLink=det.idLink
//  AND artPrin.NumPedido = ligue.numOC 
//  AND artPrin.Producto = ligue.numArtPrincipal 
//  AND det.cveMP = r.Producto 
//  AND (
//    det.numRecibo<=0 
//    OR det.numRecibo = r.NumRecibo
//  ) 
//  AND r.STATUS <> 'C' 
//GROUP BY clasif.idTgl";
$query="SELECT 
    clasif.tipoGastoLink categ,
    cantrecibidaArtPrin.cant cant,
    SUM(
        r.TotalSinIVA * det.apportion / det.tcToUSD
    ) subtotUSD,
    SUM(
        r.TotalSinIVA * det.apportion / det.tcToUSD
    ) / cantrecibidaArtPrin.cant unitUSD 
FROM
    z_linker_main ligue,
    z_linker_main_detalles det,
    z_linker_tipoGastos clasif,
    FRemisionProveedor r,
    (SELECT 
        SUM(cantiRecibida) cant 
    FROM
        FRemisionProveedor 
    WHERE Pedido = '$numOrden' 
        AND Producto = '$numArticulo' AND status<>'C') cantrecibidaArtPrin 
WHERE det.idTipoGasto = clasif.idTgl 
    AND det.Linea = r.Line 
    AND det.numFact = r.Factura 
    AND det.CveProvFact = r.Proveedor 
    AND ligue.numOC = '$numOrden' 
    AND ligue.numArtPrincipal = '$numArticulo' 
    AND ligue.idLink = det.idLink 
    AND det.cveMP = r.Producto 
    AND (
        det.numRecibo <= 0 
        OR det.numRecibo = r.NumRecibo
    ) 
    AND r.STATUS <> 'C' 
GROUP BY clasif.idTgl ";
    //RESULTADOS DEL QUERY
    $subtot = 0;
    $subtotUnit = 0;
    $table = "<table>";
    $table.= "<thead>";
    $table.= "<tr><th colspan='3'>Resumen de Costo (USD/<span id='unitSummary'></span>)</th></tr>";
    $table.= "<tr><th>Categoría</th><th>Subtotal</th><th>Unitario</th></tr>";
    $table.= "</thead>";
    $table.= "<tbody>";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $table.="<tr>"
                    . "<td>" . utf8_encode($row["categ"]) . "</td>"
                    . "<td class='currency'>" . round($row["subtotUSD"], 2) . "</td>"
                    . "<td class='currency'>" . round($row["unitUSD"], 2) . "</td>"
                    . "</tr>";
            $subtot = $subtot + $row["subtotUSD"];
            $subtotUnit = $subtotUnit + $row["unitUSD"];
        }
    } else {
        $respuesta["errors"] = $result->error . $query;
    }
    $table.= "</tbody>";
    $table.= "<tfoot>";
    $table.="<tr><th><button id='showLinked'>Ver Detalles</button></th><th class='currency' id='subtotGral'>" . round($subtot, 2) . "</th><th class='currency' id='subTotUnitGral'>" . round($subtotUnit, 2) . "</th></tr>";
    $table.= "</tfoot>";
    $table.= "</table>";
    $respuesta["table"] = $table;
    return json_encode($respuesta);
}

function updateFechaZarpe() {
    $numArticulo = $_GET["material"];
    $numOrden = $_GET["oc"];
    $fechaZarpe = $_GET["fechaZarpe"];
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["errors"] = "";
    if ($mysqli->connect_errno) {
        $respuesta["errors"] = $mysqli->connect_error; //Error
        return json_encode($respuesta);
    }
    $query = "update z_linker_main set fechaZarpe='$fechaZarpe' where numOC=$numOrden and numArtPrincipal='$numArticulo'";
    if (!$mysqli->query($query)) {
        $respuesta["errors"] = $mysqli->error;
    }
    $respuesta["query"] = $query;
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
      AND ligue.numOC = '$numOrden' 
      AND ligue.numArtPrincipal = '$numArticulo' 
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
 GROUP BY NumNC";

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
