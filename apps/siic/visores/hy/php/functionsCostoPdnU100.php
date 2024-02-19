<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');

$host = "siic.raloy.com.mx";
$user = "hyescas";
$pass = "dub+but";
$db = "scp9000";
$port = "3306";

$hostZK = "siic.raloy.com.mx";
$userZK = "adblue";
$passZK = "Veoos133";
$dbZK = "adblue_scp";
$portZK = "3385";

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function creaQueries() {
    set_time_limit(180);

    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $tcE = $_GET["tcE"];
    $tcP = $_GET["tcP"];


    global $hostZK, $userZK, $passZK, $dbZK, $portZK;
    $mysqli = new mysqli($hostZK, $userZK, $passZK, $dbZK, $portZK);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    $respuesta["status"] = 1;
    unset($queries);
    $queries[] = "DROP TABLE zk_compras_importacion;";
    $queries[] = "CREATE TABLE IF NOT EXISTS zk_compras_importacion "
            . "(id_import INT (11) AUTO_INCREMENT PRIMARY KEY,"
            . "Pedimento VARCHAR (255),Nivel INT(11),"
            . "Fecha DATE,"
            . "NumRecibo VARCHAR(255),"
            . "Fecha_Recibo DATE,"
            . "Factura VARCHAR(255),"
            . "Fecha_Factura DATE,"
            . "Planta VARCHAR(255),"
            . "PlantaDesc VARCHAR(255),"
            . "Proveedor VARCHAR(255),"
            . "ProveedorDesc VARCHAR(255),"
            . "TipoU VARCHAR(255),"
            . "Moneda VARCHAR(255),"
            . "TC_SCP DOUBLE(10,5),"
            . "TC_EUR DECIMAL(10,5),"
            . "TC_MXN DECIMAL(10,5),"
            . "Producto VARCHAR(255),"
            . "Pzas VARCHAR(255),"
            . "TotOrig DOUBLE,"
            . "TotUSD DOUBLE,"
            . "TotUtilizado DOUBLE"
            . ")";
    $queries[] = "TRUNCATE TABLE zk_compras_importacion";

    $query = "select pedimento,Nivel,Fecha from FSegPedimentoIMP where Nivel<>'' and Pedimento in (SELECT l.Pedimento FROM FTransfer ft,FMPLote l WHERE ft.Lote=l.Lote AND ft.FechaHora BETWEEN '$fec1' AND '$fec2' AND ft.CodigoMP='0001' AND l.Pedimento<>'' GROUP BY l.Pedimento);";
 
//RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $string = rtrim($row["Nivel"], "|");
            $facturas = explode("|", $string);
            for ($i = 1; $i <= count($facturas) / 6; $i++) {
                $x = ($i * 6) - 6;
                $f = ($i * 6);

                $queries[] = ""
                        . "INSERT INTO zk_compras_importacion "
                        . "(Pedimento,Fecha,Nivel,NumRecibo,Planta,Proveedor,Factura,Fecha_Recibo,Moneda,TC_SCP,TC_EUR,TC_MXN,Producto,Pzas,TotOrig,TotUSD,TotUtilizado)"
                        . " SELECT "
                        . "'" . $row["pedimento"] . "' as Pedimento,"
                        . "'" . $row["Fecha"] . "',"
                        . " " . $facturas[$x] . " AS Nivel, "
                        . "GROUP_CONCAT(r.NumRecibo),"
                        . "r.CentroCosto Planta,"
                        . "r.Proveedor,"
                        . "r.Factura,"
                        . "r.FechElabo, "
                        . "r.Moneda ,"
                        . "r.TipoCamR,"
                        . "$tcE,"
                        . "$tcP,  "
                        . "r.Producto, "
                        . "SUM(r.CantiRecibida) pzas,"
                        . "r.TotalSinIVA total_orig, "
                        . "TRUNCATE(sum(if(r.Moneda='E',r.TotalSinIVA*$tcE,if(r.Moneda='P',r.TotalSinIVA/$tcP,r.TotalSinIVA))),4) total_usd,"
                        . "SUM((SELECT SUM(FTransfer.KgMateriaPrima) FROM FTransfer,FMPLote WHERE FMPLote.Lote=FTransfer.Lote AND r.Producto='0001' AND FMPLote.Codigo='0001' AND FMPLote.ReciboMP =r.NumRecibo))  CONSUMO"
                        . " FROM "
                        . "FRemisionProveedor r "
                        . "WHERE r.Proveedor='" . $facturas[$f - 4] . "' "
                        . "AND r.Factura='" . $facturas[$f - 3] . "' "
                        . "AND r.Producto not in('SACOS','TARIP','TARIC','IVA PEDIMENTOS','IVA IMP')"
                        . " GROUP BY r.Proveedor,r.Factura, r.Moneda ,r.TipoCamR,  r.Producto";
            }
        }

        $queries[] = "UPDATE zk_compras_importacion imp,FFacturacionProveedor f set imp.Fecha_Factura=f.FechElaboFac where imp.Factura=f.NumFactura";
        $queries[] = "UPDATE zk_compras_importacion imp,FDeptos d set imp.PlantaDesc=d.DescDepto where imp.Planta=d.NumDepto";
        $queries[] = "UPDATE zk_compras_importacion imp,FProveedor p set imp.ProveedorDesc=p.NomProvee, imp.TipoU=p.DescProvee where imp.Proveedor=p.CveProvee";
        $queries[] = "UPDATE zk_compras_importacion zk, (SELECT Pedimento,tipoU FROM zk_compras_importacion zk2 WHERE zk2.Producto='0001' GROUP BY zk2.Pedimento) zkAg SET zk.tipoU=zkAg.tipoU WHERE zk.Pedimento=zkAg.Pedimento AND zk.Producto<>'0001'";   
        $respuesta = makeInserts($queries, $mysqli);
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
//        $queries[]="UPDATE zk_compras_importacion imp,FTransfer ft,FMPLote mpl set imp.TotUtilizado =ft.KgMateriaPrima  where mpl.Lote = ft.Lote AND mpl.ReciboMP = imp.NumRecibo";

    return json_encode($respuesta);
}

function makeInserts($queries, $mysqli) {
    $queriesStr = implode(";", $queries);
    $respuesta["status"] = 1;
    $mysqli->autocommit(FALSE);
    foreach ($queries as $query) {
        if (!$mysqli->query($query)) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return $respuesta;
        }
    }
    $mysqli->commit();
    return $respuesta;
}

function muestraDatos() {
    set_time_limit(180);
    global $hostZK, $userZK, $passZK, $dbZK, $portZK;
    $mysqli = new mysqli($hostZK, $userZK, $passZK, $dbZK, $portZK);

    $respuesta["status"] = 1;

    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    $respuesta ["tipoNivel"] = tipoNivel($mysqli);
    $respuesta ["provTipoNivel"] = provTipoNivel($mysqli);
    $respuesta ["detalle"] = detalle($mysqli);
    $respuesta ["tipo"] = tipo($mysqli);
    return json_encode($respuesta);
}

function detalle($mysqli) {

    //RESULTADOS DEL QUERY
    $respuesta["title"] = "DETALLE DE COSTOS";
    $respuesta["tabla"] = "Detalle";
    $respuesta["numericCols"] = array(15, 16, 19);
    $respuesta["currencyCols"] = array(11, 12, 13, 17, 18);
    $respuesta["sumCol"] = 17;

    //Detalle
    $query = "SELECT "
            . "imp.Pedimento,"
            . "Fecha Fecha_Pedimento,"
            . "Factura,"
            . "Fecha_Factura,"
            . "Nivel,"
            . "replace(NumRecibo,',',', ') recibo,"
            . "Fecha_Recibo,"
            . "PlantaDesc planta,"
            . "concat(Proveedor,' ',ProveedorDesc) proveedor,"
            . "impAg.tipo_u100,"
            . "Moneda,"
            . "TC_SCP,"
            . "TC_EUR,"
            . "TC_MXN,"
            . "Producto,"
            . "Pzas,"
            . "impAg.cant_urea AS cant_U100,"
            . "TRUNCATE(TOTUSD,2) AS USD_TOT,"
            . "TRUNCATE(TOTUSD/impAg.cant_urea,2) AS USD_MT,"
            . "TOTUTILIZADO "
            . " FROM zk_compras_importacion imp LEFT JOIN "
            . "  (SELECT Pedimento,tipoU tipo_u100,SUM(Pzas) cant_urea FROM zk_compras_importacion imp2 WHERE imp2.Producto='0001' GROUP BY imp2.Pedimento,imp2.tipoU) impAg "
            . "ON imp.Pedimento=impAg.Pedimento";

    if ($result = $mysqli->query($query)) {
        $columns = $result->fetch_fields();
        $respuesta["conteo"] = $mysqli->field_count;
        foreach ($columns as $value) {
            $respuesta["columns"][] = array(data => $value->name, title => str_replace("_", " ", strtoupper($value->name)));
        }
        $respuesta["status"] = 1;
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"][] = $row;
        }
        $result->free();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return $respuesta;
}

function tipo($mysqli) {
    //RESULTADOS DEL QUERY
    $respuesta["title"] = "COSTOS POR TIPO DE U-100";
    $respuesta["tabla"] = "Tipo";
    $respuesta["numericCols"] = array(1,4);
    $respuesta["currencyCols"] = array(2,3,5,6);
    $respuesta["sumCol"] = 6;

    //Detalle
    $query = "SELECT "
            . "imp.tipoU,"
            . "impAg.cant_urea AS cant_U100,"
            . "sum(TOTUSD) USD_TOT,"
            . "sum(TOTUSD)/impAg.cant_urea AS USD_MT,"
            . "SUM(totUtilizado) Utilizado,"
            . "TRUNCATE(((SUM(totUtilizado)*(sum(TOTUSD)/impAg.cant_urea))/(SELECT SUM(totUtilizado) FROM zk_compras_importacion)),2) PRORRATEADO, "
            . "((SUM(totUtilizado)*(sum(TOTUSD)/impAg.cant_urea))/(SELECT SUM(totUtilizado) FROM zk_compras_importacion))*.000355 U100_POR_LT "
            . " FROM zk_compras_importacion imp LEFT JOIN "
            . "  (SELECT tipoU, sum(Pzas) as cant_urea,sum(totUtilizado) utilizado FROM zk_compras_importacion imp2 WHERE imp2.Producto='0001' GROUP BY imp2.tipoU) impAg "
            . "ON impAg.tipoU=imp.tipoU"
            . " GROUP BY "
            . "tipoU"
    ;
    if ($result = $mysqli->query($query)) {
        $columns = $result->fetch_fields();
        $respuesta["conteo"] = $mysqli->field_count;
        foreach ($columns as $value) {
            $respuesta["columns"][] = array(data => $value->name, title => str_replace("_", " ", strtoupper($value->name)));
        }
        $respuesta["status"] = 1;
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"][] = $row;
        }
        $result->free();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return $respuesta;
}

function provTipoNivel($mysqli) {
    //RESULTADOS DEL QUERY
    $respuesta["title"] = "COSTOS POR PROVEEDOR, TIPO DE U-100 Y NIVEL";
    $respuesta["tabla"] = "ProvTipoNivel";
    $respuesta["numericCols"] = array(3,6);
    $respuesta["currencyCols"] = array(4,5,7,8);
    $respuesta["sumCol"] = 4;

    //Detalle
    $query = "SELECT "
            . "imp.tipoU,"
            . "IF(imp.Nivel=1,'U-100',IF(imp.Nivel=2,'ADUANAS',IF(imp.Nivel=3,'FLETES',imp.Nivel))) nivel_costo,"
            . "concat(imp.Proveedor,' ',imp.ProveedorDesc) nombre_proveedor,"
            . "impAg.cant_urea AS cant_U100,"
            . "sum(TOTUSD) USD_TOT,"
            . "sum(TOTUSD)/impAg.cant_urea AS USD_MT,"
            . "SUM(totUtilizado) Utilizado,"
            . "TRUNCATE(((SUM(totUtilizado)*(sum(TOTUSD)/impAg.cant_urea))/(SELECT SUM(totUtilizado) FROM zk_compras_importacion)),2) PRORRATEADO, "
            . "((SUM(totUtilizado)*(sum(TOTUSD)/impAg.cant_urea))/(SELECT SUM(totUtilizado) FROM zk_compras_importacion))*.000355 U100_POR_LT "
            . " FROM zk_compras_importacion imp LEFT JOIN "
            . "  (SELECT proveedor as prov,tipoU, sum(Pzas) as cant_urea FROM zk_compras_importacion imp2 WHERE imp2.Producto='0001' GROUP BY imp2.tipoU,imp2.Proveedor) impAg "
            . "ON impAg.tipoU=imp.tipoU AND impAg.prov=imp.Proveedor"
            . " GROUP BY "
            . "tipoU,"
            . "Nivel,"
            . "imp.Proveedor"
            . " ORDER BY TipoU,Nivel,imp.Proveedor"
    ;
    if ($result = $mysqli->query($query)) {
        $columns = $result->fetch_fields();
        $respuesta["conteo"] = $mysqli->field_count;
        foreach ($columns as $value) {
            $respuesta["columns"][] = array(data => $value->name, title => str_replace("_", " ", strtoupper($value->name)));
        }
        $respuesta["status"] = 1;
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"][] = $row;
        }
        $result->free();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return $respuesta;
}

function tipoNivel($mysqli) {
    //RESULTADOS DEL QUERY
    $respuesta["title"] = "COSTOS POR TIPO DE U-100 Y NIVEL";
    $respuesta["tabla"] = "TipoNivel";
    $respuesta["numericCols"] = array(2,5);
    $respuesta["currencyCols"] = array(3, 4,6,7);
    $respuesta["sumCol"] = 3;

    //Detalle
    $query = "SELECT "
            . "imp.tipoU,"
            . "IF(imp.Nivel=1,'U-100',IF(imp.Nivel=2,'ADUANAS',IF(imp.Nivel=3,'FLETES',imp.Nivel))) nivel_costo,"
            . "impAg.cant_urea AS cant_U100,"
            . "sum(TOTUSD) USD_TOT,"
            . "sum(TOTUSD)/impAg.cant_urea AS USD_MT,"
            . "SUM(totUtilizado) Utilizado,"
            . "TRUNCATE(((SUM(totUtilizado)*(sum(TOTUSD)/impAg.cant_urea))/(SELECT SUM(totUtilizado) FROM zk_compras_importacion)),2) PRORRATEADO, "
            . "((SUM(totUtilizado)*(sum(TOTUSD)/impAg.cant_urea))/(SELECT SUM(totUtilizado) FROM zk_compras_importacion))*.000355 U100_POR_LT "
            . " FROM zk_compras_importacion imp LEFT JOIN "
            . "  (SELECT tipoU, sum(Pzas) as cant_urea FROM zk_compras_importacion imp2 WHERE imp2.Producto='0001' GROUP BY imp2.tipoU) impAg "
            . "ON impAg.tipoU=imp.tipoU"
            . " GROUP BY "
            . "tipoU,"
            . "Nivel"
    ;
    if ($result = $mysqli->query($query)) {
        $columns = $result->fetch_fields();
        $respuesta["conteo"] = $mysqli->field_count;
        foreach ($columns as $value) {
            $respuesta["columns"][] = array(data => $value->name, title => str_replace("_", " ", strtoupper($value->name)));
        }
        $respuesta["status"] = 1;
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"][] = $row;
        }
        $result->free();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return $respuesta;
}
