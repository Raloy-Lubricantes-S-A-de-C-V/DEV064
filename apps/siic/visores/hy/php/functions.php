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

function r_ventasEvol()
{
    global $host, $user, $pass, $db, $port;

    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];

    $respuesta["status"] = 1; //Status exitoso
    $respuesta["error"] = ""; //Sin errores

    $arrMeses = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec");
    $respuesta["strokeColors"] = array("#0b62a4", "#7a92a3", "#cb4b4b", "#4da74d", "#edc240", "#ff7ba3", "#9999cc", "#a2f213", "#7a92a3", "#7a92a3", "#7a92a3", "#7a92a3");
    $query = <<<SQL
    SELECT
    DATE_FORMAT( FechElabo, '2015-%m' ) as mes,
    MONTH( FechElabo) as mesNum,
    YEAR( FechElabo ) as anio,
    SUM(CantiDada) AS Piezas ,
    SUM(
        CantiDada * CDV
    ) AS Litros ,
    SUM(TotalSinIva) / SUM(CantiDada) AS PU ,
    SUM(TotalSinIva) / SUM(
        CantiDada * CDV
    ) AS PXL ,
    SUM(TotalSinIva) AS Importe
FROM
    FRemision INNER JOIN InvProdTerm
        ON Producto = PTNumArticulo LEFT JOIN FClienteEnvio
        ON FRemision.Cliente = FClienteEnvio.Cliente
    AND Enviado = Determinante INNER JOIN FClientes
        ON FRemision.Cliente = CveCliente
WHERE
    FechElabo >= '$fec1'
    AND FechElabo <= '$fec2'
    AND PTCatalogo = 'ADBLUE'
    AND Status <> 'C'
    AND NumRemi > 0
GROUP BY
    YEAR( FechElabo ),
    MONTH( FechElabo )
ORDER BY
    MONTH( FechElabo ),
    YEAR( FechElabo ) desc
    
SQL;

    //CONEXIÓN A MYSQL
    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }
        //CON DATOS

        while ($row = $result->fetch_assoc()) {
            //            $respuesta["data"][$row["anio"]]=$row["anio"];
            $respuesta["data"][$row["mes"]]["m"] = $row["mes"];
            $respuesta["data"][$row["mes"]][$row["anio"]] = $row["Litros"];
            $ykeys[] = $row["anio"];
            $meses[$row["mes"]] = $row["mes"];
            $mesesNum[$row["mes"]] = $row["mesNum"];
        }
        $respuesta["ykeys"] = array_unique($ykeys);

        //TABLA DE DATOS
        //THEAD
        $respuesta["table"] = "<table><thead><tr><th>Periodo</th>";
        foreach ($mesesNum as $mesNum) {
            $respuesta["table"] .= "<th>" . $arrMeses[$mesNum] . "</th>";
        }
        $respuesta["table"] .= "<th>Total</th></thead>";
        $respuesta["table"] .= "</tr></thead>";

        //TBODY
        $respuesta["table"] .= "<tbody>";
        $c = 0;
        foreach ($respuesta["ykeys"] as $anio) {
            $color = $respuesta["strokeColors"][$c];
            $c = $c + 1;
            $respuesta["table"] .= "<tr><td  style='color:$color;font-weight:bold;'>$anio</td>";
            foreach ($meses as $mes) {
                $ty = $respuesta["data"][$mes][$anio];
                $respuesta["tot"][$anio][] = $ty;
                $respuesta["table"] .= "<td class='numeric'>" . $ty . "</td>";
            }
            $respuesta["table"] .= "<td class='numeric'>" . array_sum($respuesta["tot"][$anio]) . "</td>";
            $respuesta["table"] .= "</tr>";
        }
        $respuesta["table"] .= "</tbody></table>";
        return json_encode($respuesta);
    } else {
        //ERRORES
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function r_ventasxArea()
{
    global $host, $user, $pass, $db, $port;

    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];

    $respuesta["status"] = 1; //Status exitoso
    $respuesta["error"] = ""; //Sin errores

    $arrMeses = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec");
    $respuesta["strokeColors"] = array("#0b62a4", "#7a92a3", "#cb4b4b", "#4da74d", "#edc240", "#ff7ba3", "#9999cc", "#a2f213", "#7a92a3", "#7a92a3", "#7a92a3", "#7a92a3");
    $query = <<<SQL
    SELECT 
  FClientes.Zona area_comercial,
  DATE_FORMAT(DATE(FechElabo), '%Y-%m') aniomes,
  YEAR(FechElabo) AS Anio,
  MONTH(FechElabo) AS Mes,
  SUM(CantiDada) AS Piezas,
  SUM(CantiDada * CDV) AS Litros,
  SUM(TotalSinIva) / SUM(CantiDada) AS PU,
  SUM(TotalSinIva) / SUM(CantiDada * CDV) AS PXL,
  SUM(TotalSinIva) AS Importe 
FROM
  FRemision 
  INNER JOIN
  InvProdTerm 
  ON Producto = PTNumArticulo 
  LEFT JOIN
  FClienteEnvio 
  ON FRemision.Cliente = FClienteEnvio.Cliente 
  AND Enviado = Determinante 
  INNER JOIN
  FClientes 
  ON FRemision.Cliente = CveCliente 
WHERE FechElabo >= '$fec1' 
  AND FechElabo <= '$fec2' 
  AND PTCatalogo = 'ADBLUE' 
  AND STATUS <> 'C' 
  AND NumRemi > 0 
  AND FClientes.Zona IN ('VTADIR', 'MAQ', 'DIST') 
GROUP BY FClientes.Zona,
  YEAR(FechElabo),
  MONTH(FechElabo) 
ORDER BY area_comercial,
  YEAR(FechElabo),
  MONTH(FechElabo)
    
SQL;

    //CONEXIÓN A MYSQL
    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }
        //CON DATOS

        while ($row = $result->fetch_assoc()) {
            $respuesta["data"][$row["aniomes"]]["m"] = $row["aniomes"];
            $respuesta["data"][$row["aniomes"]][$row["area_comercial"]] = $row["Litros"];
            $ykeys[] = $row["area_comercial"];
            $periodos[$row["aniomes"]] = $row["aniomes"];
            $periodosLabel[$row["aniomes"]] = $arrMeses[$row["mesNum"]] . "-" . $row["anio"];
        }
        $respuesta["ykeys"] = array_unique($ykeys);

        //TABLA DE DATOS
        //THEAD
        $respuesta["table"] = "<table><thead><tr><th>Periodo</th>";
        foreach ($periodosLabel as $periodoLabel) {
            $respuesta["table"] .= "<th>" . $periodoLabel . "</th>";
        }
        $respuesta["table"] .= "<th>Total</th></thead>";
        $respuesta["table"] .= "</tr></thead>";

        //TBODY
        $respuesta["table"] .= "<tbody>";
        $c = 0;
        foreach ($respuesta["ykeys"] as $area) {
            $color = $respuesta["strokeColors"][$c];
            $c = $c + 1;
            $respuesta["table"] .= "<tr><td  style='color:$color;font-weight:bold;'>$area</td>";
            foreach ($periodos as $periodo) {
                $ty = $respuesta["data"][$periodo][$area];
                $respuesta["tot"][$area][] = $ty;
                $respuesta["table"] .= "<td class='numeric'>" . $ty . "</td>";
            }
            $respuesta["table"] .= "<td class='numeric'>" . array_sum($respuesta["tot"][$area]) . "</td>";
            $respuesta["table"] .= "</tr>";
        }
        $respuesta["table"] .= "</tbody></table>";
        return json_encode($respuesta);
    } else {
        //ERRORES
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function dimePermisosPlantas($us, $id)
{
    global $host, $user, $pass, $port;
    $respuesta["status"] = 1;
    $mysqliR = new mysqli($host, $user, $pass, "bopi", $port);
    if ($mysqliR->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqliR->connect_error;
        return $respuesta;
    }

    $queryPermisos = "SELECT Planta perm FROM plantasZK p JOIN Perfiles per ON FIND_IN_SET(p.IDPlanta,per.IDPlanta) WHERE per.UsrName='$us' AND per.IDReporte=$id";
    if ($resultPerm = $mysqliR->query($queryPermisos)) {
        while ($rowPerm = $resultPerm->fetch_assoc()) {
            $permisos[] = "'" . $rowPerm["perm"] . "'";
        }
    }
    $respuesta["permisos"] = implode(",", $permisos);
    return $respuesta;
}

function zk_ventasxFam()
{
    global $hostZK, $userZK, $passZK, $dbZK, $portZK;

    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $u = $_GET["uSe"];

    //Permisos de plantas
    $permisos = dimePermisosPlantas($u, $_GET["id"]);

    if ($permisos["status"] == 1) {
        $perm = $permisos["permisos"];
    } else {
        $perm = $permisos["error"];
    }

    //reporte
    $mysqli = new mysqli($hostZK, $userZK, $passZK, $dbZK, $portZK);

    $respuesta["numericCols"] = array(6, 7, 8);
    $respuesta["sumCol"] = 8;
    $mysqli->query("set names UTF-8;");
    $query = <<<SQL
        SELECT 
            DATE_FORMAT(r.FechElabo,'%Y-%m') Mes,
        COALESCE(
          IF(AlmacenR = "", "1 STG", IF(AlmacenR="GDL","2 GDL",IF(AlmacenR="MTY","3 MTY",AlmacenR))),
          "Subtotal Planta"
        ) planta,
        InvProdTerm.Marca Imagen,
        InvProdTerm.Marca2 Presentacion,
        CONCAT(r.Producto,' ',r.Acabado) cve,
        InvProdTerm.PTDesc producto,
        SUM(CantiDada) Pzas,
        CDV,
        SUM(CantiDada * CDV) litros,
        SUM(TotalSinIVA) usd
      FROM
        FRemision r,
        InvProdTerm 
      WHERE r.FechElabo >= "$fec1" 
        AND r.FechElabo <= "$fec2" 
        AND r.Producto = InvProdTerm.PTNumArticulo 
        AND r.Acabado = InvProdTerm.PTTipo 
        AND r.STATUS <> "C" 
        AND PTCatalogo in ("SKYBLUE") 
      GROUP BY YEAR(r.FechElabo),
        MONTH(r.FechElabo),
        AlmacenR,
        InvProdTerm.Marca,
        InvProdTerm.Marca2,
        CONCAT(r.Producto,' ',r.Acabado),
        InvProdTerm.PTDesc
      ORDER BY YEAR(r.FechElabo),
        MONTH(r.FechElabo),
        AlmacenR,
        InvProdTerm.Marca,
        InvProdTerm.Marca2,
        CONCAT(r.Producto,' ',r.Acabado),
        InvProdTerm.PTDesc; 
            
SQL;

    $querydevoluciones = <<<SQL
        SELECT 
    DATE_FORMAT(r.Fecha, '%Y-%m') Mes,
    InvProdTerm.Marca Imagen,
    InvProdTerm.Marca2 Presentacion,
    CONCAT(r.Producto, ' ', r.Acabado) cve,
    InvProdTerm.PTDesc producto,
    SUM(Cantidad) Pzas,
    CDV,
    SUM(Cantidad * CDV) litros,
    SUM(r.Precio*Cantidad) usd 
FROM
    FDevolCliente r,
    InvProdTerm 
WHERE r.Fecha >= "$fec1" 
    AND r.Fecha <= "$fec2" 
    AND r.Producto = InvProdTerm.PTNumArticulo 
    AND r.Acabado = InvProdTerm.PTTipo  
    AND PTCatalogo IN ("SKYBLUE") 
GROUP BY YEAR(r.Fecha),
    MONTH(r.Fecha),
    InvProdTerm.Marca,
    InvProdTerm.Marca2,
    CONCAT(r.Producto, ' ', r.Acabado),
    InvProdTerm.PTDesc 
SQL;
    $fec1f = strtotime($fec1);
    $fec2f = strtotime($fec2);
    $respuesta["fecSp"]["fec1"] = date("d-m-Y", $fec1f);
    $respuesta["fecSp"]["fec2"] = date("d-m-Y", $fec2f);

    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {

        $columns = $result->fetch_fields();
        foreach ($columns as $value) {
            $respuesta["columns"][] = array("data" => $value->name, "title" => str_replace("_", " ", strtoupper($value->name)));
        }

        $arrData_plantas = [];
        $arrData_plantasUSD = [];
        $arrData_present = [];
        $arrData_presentUSD = [];
        $arrData_imagene = [];
        $arrData_imageneUSD = [];
        $plantas = [];
        $presentaciones = [];
        $imagenes = [];

        while ($row = $result->fetch_assoc()) {

            if ($row["Imagen"] != "ZK") { //quitando tarimas, agua y otros productos vendidos en ZK
                $respuesta["filas"][] = array("Mes" => $row["Mes"], "Planta" => $row["planta"], "Present" => $row["Presentacion"], "Litros" => $row["litros"]);
                $respuesta["filasusd"][] = array("Mes" => $row["Mes"], "Planta" => $row["planta"], "Present" => $row["Presentacion"], "USD" => $row["usd"]);
                $respuesta["filasusdunit"][] = array("Mes" => $row["Mes"], "Planta" => $row["planta"], "Present" => $row["Presentacion"], "USDUNIT" => $row["usd"] / $row["litros"]);
                if (!in_array($row["planta"], $plantas))
                    $plantas[] = $row["planta"];
                if (!in_array($row["Presentacion"], $presentaciones))
                    $presentaciones[] = $row["Presentacion"];
                if (!in_array($row["Imagen"], $imagenes))
                    $imagenes[] = $row["Imagen"];

                $arrData_plantas[$row["Mes"]][$row["planta"]] += $row["litros"];
                $arrData_plantasUSD[$row["Mes"]][$row["planta"]] += $row["usd"];

                $arrData_present[$row["Mes"]][$row["Presentacion"]] += $row["litros"];
                $arrData_presentUSD[$row["Mes"]][$row["Presentacion"]] += $row["usd"];

                $arrData_imagene[$row["Mes"]][$row["Imagen"]] += $row["litros"];
                $arrData_imageneUSD[$row["Mes"]][$row["Imagen"]] += $row["usd"];
            }
        }

        $result->free();


        //Gráficas
        //Plantas
        $respuesta["arrData"] = creaArrayGraph($arrData_plantas, $plantas);
        $respuesta["USDarrData"] = creaArrayGraph($arrData_plantasUSD, $plantas);
        //Presentaciones
        $respuesta["arrDataPres"] = creaArrayGraph($arrData_present, $presentaciones);
        $respuesta["USDarrDataPres"] = creaArrayGraph($arrData_presentUSD, $presentaciones);
        //Imágenes
        $respuesta["arrDataImg"] = creaArrayGraph($arrData_imagene, $imagenes);
        $respuesta["USDarrDataImg"] = creaArrayGraph($arrData_imageneUSD, $imagenes);

        //Devoluciones
        if ($resultdevs = $mysqli->query($querydevoluciones)) {
            $respuesta["tabladevs"] = "<table><thead><tr><th>MES</th><th>IMAGEN</th><th>PRESENTACIÓN</th><th>CLAVE</th><th>PRODUCTO</th><th>PIEZAS</th><th>LITROS</th><th>USD</th></tr></thead><tbody>";
            while ($rowdevs = $resultdevs->fetch_assoc()) {
                $respuesta["tabladevs"] .= "<tr><td>" . $rowdevs["Mes"] . "</td><td>" . utf8_decode($rowdevs["Imagen"]) . "</td><td>" . utf8_decode($rowdevs["Presentacion"]) . "</td><td>" . utf8_decode($rowdevs["cve"]) . "</td><td>" . utf8_decode($rowdevs["producto"]) . "</td><td>" . number_format($rowdevs["pzas"], 2) . "</td><td>" . number_format($rowdevs["litros"], 2) . "</td><td>$" . number_format($rowdevs["usd"], 2) . "</td></tr>";
            }
            $respuesta["tabladevs"] .= "</tbody></table>";
        } else {
            $respuesta["tabladevs"] = "";
        }
        return json_encode($respuesta);
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function creaArrayGraph($arr1, $arr2)
{
    //Estableciendo los headers en el primer objeto del array
    $headers = [];
    $headers[] = "Mes";
    foreach ($arr2 as $headItem) {
        $headers[] = $headItem;
    }

    //Insertando los headers creados como primer objeto del array a devolver
    $arrReturn = [];
    $arrReturn[] = $headers;

    //Creando los objetos del array con los valores
    foreach ($arr1 as $key => $value) {
        $newArray = [];
        $newArray[] = $key; //inserta el mes
        foreach ($arr2 as $valueP) {
            $newArray[] = $value[$valueP]; //inserta los litros
        }
        //Insertando el array en el array a devolver
        $arrReturn[] = $newArray;
    }

    return $arrReturn;
}

function resumenIncome()
{
    global $hostZK, $userZK, $passZK, $dbZK, $portZK;

    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $u = $_GET["uSe"];

    $respuesta["status"] = 1;
    $respuesta["error"] = "";

    //Permisos de plantas
    $permisos = dimePermisosPlantas($u, $_GET["id"]);

    if ($permisos["status"] == 1) {
        $perm = $permisos["permisos"];
    } else {
        $perm = $permisos["error"];
    }

    //reporte
    $mysqli = new mysqli($hostZK, $userZK, $passZK, $dbZK, $portZK);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    $mysqli->query("set names UTF-8;");

    $query = <<<SQL
    SELECT 
        rem.Mes,
        ROUND(rem.litros, 0) Lts,
        ROUND(rem.usd, 2) USD_sales,
        ROUND(IF(nc.usd>0,nc.usd,0)) USD_cn,
        ROUND(rem.usd - IF(nc.usd>0,nc.usd,0),2) USD_total,
        ROUND(rem.usd / rem.litros,3) usd_lt,
        ROUND(IF(nc.usd>0,nc.usd,0) / rem.litros,3) nc_usd_lt,
        ROUND((rem.usd - IF(nc.usd>0,nc.usd,0)) / rem.litros,3) income_lt 
    FROM
        (SELECT 
          DATE_FORMAT(r.FechElabo, '%Y-%m') Mes,
          SUM(CantiDada * CDV) litros,
          SUM(TotalSinIVA) usd 
        FROM
          FRemision r,
          InvProdTerm 
        WHERE r.FechElabo >= "$fec1" 
          AND r.FechElabo <= "$fec2" 
          AND r.Producto = InvProdTerm.PTNumArticulo 
          AND r.Acabado = InvProdTerm.PTTipo 
          AND r.STATUS <> "C" 
          AND PTCatalogo IN ("SKYBLUE") 
        GROUP BY YEAR(r.FechElabo),
          MONTH(r.FechElabo) 
        ORDER BY YEAR(r.FechElabo),
          MONTH(r.FechElabo)) rem 
        
        LEFT JOIN
        
        (SELECT 
          DATE_FORMAT(FechElabo, '%Y-%m') Periodo,
          ROUND(SUM(TotalSinIVA), 2) usd 
        FROM
          FNotaCredito nc 
        WHERE NumFactura = 0 
          AND STATUS <> 'C' 
          AND nc.FechElabo >= '$fec1' 
          AND nc.FechElabo <= '$fec2' 
        GROUP BY Periodo 
        ORDER BY FechElabo DESC) nc 
        ON rem.Mes = nc.Periodo        
SQL;
    if (!$result = $mysqli->query($query)) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    $respuesta["table"] = "";
    $lts = 0;
    $sales = 0;
    $nc = 0;
    $income = 0;
    while ($row = $result->fetch_assoc()) {
        $respuesta["table"]["tbody"] .= "<tr>"
            . "<td>" . $row["Mes"] . "</td>"
            . "<td class='numeric'>" . $row["Lts"] . "</td>"
            . "<td class='currency'>" . $row["USD_sales"] . "</td>"
            . "<td class='currency'>" . $row["USD_cn"] . "</td>"
            . "<td class='currency'>" . $row["USD_total"] . "</td>"
            . "<td style='text-align:right;' class='currency3'>" . $row["usd_lt"] . "</td>"
            . "<td style='text-align:right;' class='currency3'>" . $row["nc_usd_lt"] . "</td>"
            . "<td style='text-align:right;' class='currency3'>" . $row["income_lt"] . "</td>"
            . "</tr>";
        $lts += $row["Lts"];
        $sales += $row["USD_sales"];
        $nc += $row["USD_cn"];
        $income += $row["USD_total"];
    }
    $respuesta["table"]["tfoot"] .= "<tr>"
        . "<th></th>"
        . "<th class='numeric'>" . $lts . "</th>"
        . "<th class='currency'>" . $sales . "</th>"
        . "<th class='currency'>" . $nc . "</th>"
        . "<th class='currency'>" . $income . "</th>"
        . "<th style='text-align:right;' class='currency3'>" . round($sales / $lts, 3) . "</th>"
        . "<th style='text-align:right;' class='currency3'>" . round($nc / $lts, 3) . "</th>"
        . "<th style='text-align:right;' class='currency3'>" . round($income / $lts, 3) . "</th>"
        . "</tr>";

    return json_encode($respuesta);
}

function zk_ventasEvol()
{
    global $host, $user, $pass, $db, $port;

    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];

    $respuesta["status"] = 1; //Status exitoso
    $respuesta["error"] = ""; //Sin errores

    $arrMeses = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec");
    $respuesta["strokeColors"] = array("#0b62a4", "#7a92a3", "#cb4b4b", "#4da74d", "#edc240", "#ff7ba3", "#9999cc", "#a2f213", "#7a92a3", "#7a92a3", "#7a92a3", "#7a92a3");
    $query = <<<SQL
    SELECT
    DATE_FORMAT( FechElabo, '2015-%m' ) as mes,
    MONTH( FechElabo) as mesNum,
    YEAR( FechElabo ) as anio,
    SUM(CantiDada) AS Piezas ,
    SUM(
        CantiDada * CDV
    ) AS Litros ,
    SUM(TotalSinIva) / SUM(CantiDada) AS PU ,
    SUM(TotalSinIva) / SUM(
        CantiDada * CDV
    ) AS PXL ,
    SUM(TotalSinIva) AS Importe
FROM
    FRemision INNER JOIN InvProdTerm
        ON Producto = PTNumArticulo LEFT JOIN FClienteEnvio
        ON FRemision.Cliente = FClienteEnvio.Cliente
    AND Enviado = Determinante INNER JOIN FClientes
        ON FRemision.Cliente = CveCliente
WHERE
    FechElabo >= '$fec1'
    AND FechElabo <= '$fec2'
    AND PTCatalogo = 'ADBLUE'
    AND Status <> 'C'
    AND NumRemi > 0
GROUP BY
    YEAR( FechElabo ),
    MONTH( FechElabo )
ORDER BY
    MONTH( FechElabo ),
    YEAR( FechElabo ) desc
    
SQL;

    //CONEXIÓN A MYSQL
    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }
        //CON DATOS

        while ($row = $result->fetch_assoc()) {
            //            $respuesta["data"][$row["anio"]]=$row["anio"];
            $respuesta["data"][$row["mes"]]["m"] = $row["mes"];
            $respuesta["data"][$row["mes"]][$row["anio"]] = $row["Litros"];
            $ykeys[] = $row["anio"];
            $meses[$row["mes"]] = $row["mes"];
            $mesesNum[$row["mes"]] = $row["mesNum"];
        }
        $respuesta["ykeys"] = array_unique($ykeys);

        //TABLA DE DATOS
        //THEAD
        $respuesta["table"] = "<table><thead><tr><th>Periodo</th>";
        foreach ($mesesNum as $mesNum) {
            $respuesta["table"] .= "<th>" . $arrMeses[$mesNum] . "</th>";
        }
        $respuesta["table"] .= "<th>Total</th></thead>";
        $respuesta["table"] .= "</tr></thead>";

        //TBODY
        $respuesta["table"] .= "<tbody>";
        $c = 0;
        foreach ($respuesta["ykeys"] as $anio) {
            $color = $respuesta["strokeColors"][$c];
            $c = $c + 1;
            $respuesta["table"] .= "<tr><td  style='color:$color;font-weight:bold;'>$anio</td>";
            foreach ($meses as $mes) {
                $ty = $respuesta["data"][$mes][$anio];
                $respuesta["tot"][$anio][] = $ty;
                $respuesta["table"] .= "<td class='numeric'>" . $ty . "</td>";
            }
            $respuesta["table"] .= "<td class='numeric'>" . array_sum($respuesta["tot"][$anio]) . "</td>";
            $respuesta["table"] .= "</tr>";
        }
        $respuesta["table"] .= "</tbody></table>";
        return json_encode($respuesta);
    } else {
        //ERRORES
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function zk_ventasVSobj()
{
    global $hostZK, $userZK, $passZK, $dbZK, $portZK;

    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $u = $_GET["uSe"];

    //permisos de plantas
    $permisos = dimePermisosPlantas($u, $_GET["id"]);
    if ($permisos["status"] == 1) {
        $perm = $permisos["permisos"];
    } else {
        $perm = $permisos["error"];
    }

    //reporte
    $mysqli = new mysqli($hostZK, $userZK, $passZK, $dbZK, $portZK);

    $respuesta["numericCols"] = array(2, 3);
    $respuesta["sumCol"] = 3;

    $query = <<<SQL
SELECT 
  Planta,
  present,
  lts_obj,
  lts_avance,
  lts_porc_avance,
  usd_obj,
  ROUND(usd_avance,2) usd_avance,
  nc_avance,
  usd_porc_avance,
  ROUND(lts_avance / dias_transcurridos * 22,0) lts_tendencia,
  ROUND(
    lts_avance / dias_transcurridos * 22 / lts_obj,
    4
  ) lts_porc_tendencia 
FROM
  (SELECT 
    IF(AlmacenR = '', 'STG', AlmacenR) Planta,
    IF(
      pt.Marca2 LIKE '%TOTE%',
      'TOTE',
      pt.Marca2
    ) present,
    pron.litros lts_obj,
    SUM(CantiDada * CDV) lts_avance,
    ROUND(SUM(CantiDada * CDV) / pron.litros, 4) lts_porc_avance,
    pron.usd usd_obj,
    SUM(TotalSinIVA) usd_avance,
    SUM(CantiDada * CDV) * .1 nc_avance,
    ROUND(
      (
        SUM(TotalSinIVA) - (SUM(CantiDada * CDV) * .1)
      ) / pron.usd,
      4
    ) usd_porc_avance,
    dias_transcurridos 
  FROM
    FRemision r 
    LEFT JOIN
    InvProdTerm pt 
    ON r.Producto = pt.PTNumArticulo 
    AND r.Acabado = pt.PTTipo 
    LEFT JOIN
    zk_pronostico_ventas pron 
    ON pron.anio = DATE_FORMAT(FechElabo, '%Y') 
    AND pron.mes = DATE_FORMAT(FechElabo, '%m') 
    AND pron.planta_scp = AlmacenR 
    AND pron.presentacion = IF(
      pt.Marca2 LIKE '%TOTE%',
      'TOTE',
      pt.Marca2
    ),
    (SELECT 
      - DATEDIFF('2017-06-01', '2017-06-08') dias_transcurridos) dias 
  WHERE FechElabo >= '2017-06-01' 
    AND FechElabo <= '2017-06-08' 
    AND STATUS <> 'C' 
    AND PTCatalogo = 'SKYBLUE' 
  GROUP BY AlmacenR,
    present) todo 
SQL;
    $fec1f = strtotime($fec1);
    $fec2f = strtotime($fec2);
    $respuesta["fecSp"]["fec1"] = date("d-m-Y", $fec1f);
    $respuesta["fecSp"]["fec2"] = date("d-m-Y", $fec2f);

    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {

        $table = "<table id='tablaDatos'>";
        $table .= "<thead>";
        $table .= "<tr><th rowspan='2'>Planta</th><th rowspan='2'>Presentación</th><th colspan='3'>LITROS</th><th colspan='3'>USD</th><th colspan='2'>TENDENCIA AL CIERRE</th>";
        $table .= "<tr><th>Objetivo</th><th>Avance</th><th>% Avance</th><th>Objetivo</th><th>Avance</th><th>NC Avance</th><th>% Avance</th><th>Litros</th><th>%</th>";
        $table .= "</thead>";
        $table .= "<tbody>";
        while ($row = $result->fetch_assoc()) {
            $table .= "<tr>";
            $table .= "<td>" . utf8_encode($row["Planta"]) . "</td><td>" . utf8_encode($row["present"]) . "</td>";
            $table .= "<td class='numeric'>" . $row["lts_obj"] . "</td><td class='numeric'>" . $row["lts_avance"] . "</td><td class='porc'>" . $row["lts_porc_avance"] . "</td>";
            $table .= "<td class='numeric currency'>" . $row["usd_obj"] . "</td><td class='numeric currency'>" . $row["usd_avance"] . "</td><td class='numeric currency'>" . $row["nc_avance"] . "</td><td class='porc'>" . $row["usd_porc_avance"] . "</td>";
            $table .= "<td class='numeric'>" . $row["lts_tendencia"] . "</td><td class='porc'>" . $row["lts_porc_tendencia"] . "</td>";
        }
        $table .= "</tbody>";
        $table .= "</table>";
        $respuesta["tabla"] = $table;
        return json_encode($respuesta);
    } else {

        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function zk_ventasVSobj_summary()
{
    global $hostZK, $userZK, $passZK, $dbZK, $portZK;

    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $u = $_GET["uSe"];

    //permisos de plantas
    $permisos = dimePermisosPlantas($u, $_GET["id"]);
    if ($permisos["status"] == 1) {
        $perm = $permisos["permisos"];
    } else {
        $perm = $permisos["error"];
    }

    //reporte
    $mysqli = new mysqli($hostZK, $userZK, $passZK, $dbZK, $portZK);

    $respuesta["numericCols"] = array(2, 3);
    $respuesta["sumCol"] = 3;

    $query = <<<SQL
SELECT 
  Planta,
  present,
  lts_obj,
  lts_avance,
  lts_porc_avance,
  usd_obj,
  ROUND(usd_avance,2) usd_avance,
  nc_avance,
  usd_porc_avance,
  ROUND(lts_avance / dias_transcurridos * 22,0) lts_tendencia,
  ROUND(
    lts_avance / dias_transcurridos * 22 / lts_obj,
    4
  ) lts_porc_tendencia 
FROM
  (SELECT 
    IF(AlmacenR = '', 'STG', AlmacenR) Planta,
    IF(
      pt.Marca2 LIKE '%TOTE%',
      'TOTE',
      pt.Marca2
    ) present,
    pron.litros lts_obj,
    SUM(CantiDada * CDV) lts_avance,
    ROUND(SUM(CantiDada * CDV) / pron.litros, 4) lts_porc_avance,
    pron.usd usd_obj,
    SUM(TotalSinIVA) usd_avance,
    SUM(CantiDada * CDV) * .1 nc_avance,
    ROUND(
      (
        SUM(TotalSinIVA) - (SUM(CantiDada * CDV) * .1)
      ) / pron.usd,
      4
    ) usd_porc_avance,
    dias_transcurridos 
  FROM
    FRemision r 
    LEFT JOIN
    InvProdTerm pt 
    ON r.Producto = pt.PTNumArticulo 
    AND r.Acabado = pt.PTTipo 
    LEFT JOIN
    zk_pronostico_ventas pron 
    ON pron.anio = DATE_FORMAT(FechElabo, '%Y') 
    AND pron.mes = DATE_FORMAT(FechElabo, '%m') 
    AND pron.planta_scp = AlmacenR 
    AND pron.presentacion = IF(
      pt.Marca2 LIKE '%TOTE%',
      'TOTE',
      pt.Marca2
    ),
    (SELECT 
      - DATEDIFF('2017-06-01', '2017-06-08') dias_transcurridos) dias 
  WHERE FechElabo >= '2017-06-01' 
    AND FechElabo <= '2017-06-08' 
    AND STATUS <> 'C' 
    AND PTCatalogo = 'SKYBLUE' 
  GROUP BY AlmacenR) todo 
SQL;
    $fec1f = strtotime($fec1);
    $fec2f = strtotime($fec2);
    $respuesta["fecSp"]["fec1"] = date("d-m-Y", $fec1f);
    $respuesta["fecSp"]["fec2"] = date("d-m-Y", $fec2f);

    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {

        $table = "<hr/><br/><table id='tablaDatos_summary'>";
        $table .= "<thead>";
        $table .= "<tr><th rowspan='2'>Planta</th><th rowspan='2'>Presentación</th><th colspan='3'>LITROS</th><th colspan='3'>USD</th><th colspan='2'>TENDENCIA AL CIERRE</th>";
        $table .= "<tr><th>Objetivo</th><th>Avance</th><th>% Avance</th><th>Objetivo</th><th>Avance</th><th>NC Avance</th><th>% Avance</th><th>Litros</th><th>%</th>";
        $table .= "</thead>";
        $table .= "<tbody>";
        while ($row = $result->fetch_assoc()) {
            $table .= "<tr>";
            $table .= "<td>" . utf8_encode($row["Planta"]) . "</td><td>" . utf8_encode($row["present"]) . "</td>";
            $table .= "<td class='numeric'>" . $row["lts_obj"] . "</td><td class='numeric'>" . $row["lts_avance"] . "</td><td class='porc'>" . $row["lts_porc_avance"] . "</td>";
            $table .= "<td class='numeric currency'>" . $row["usd_obj"] . "</td><td class='numeric currency'>" . $row["usd_avance"] . "</td><td class='numeric currency'>" . $row["nc_avance"] . "</td><td class='porc'>" . $row["usd_porc_avance"] . "</td>";
            $table .= "<td class='numeric'>" . $row["lts_tendencia"] . "</td><td class='porc'>" . $row["lts_porc_tendencia"] . "</td>";
        }
        $table .= "</tbody>";
        $table .= "</table>";
        $respuesta["tabla"] = $table;
        return json_encode($respuesta);
    } else {

        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function sumaFechas($suma, $fechaInicial = false)
{
    $fecha = !empty($fechaInicial) ? $fechaInicial : date('Y-m-d');
    $nuevaFecha = strtotime($suma, strtotime($fecha));
    $nuevaFecha = date('Y-m-d', $nuevaFecha);
    return $nuevaFecha;
}

function creaAnalitico($campo)
{
    global $host, $user, $pass, $db, $port;
    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    $campo = $_GET["campo"];
    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $respuesta["status"] = 1;
    $query = <<<SQL
    SELECT 
      IFNULL(campo,'Totales') etiqueta,
      ROUND(pm / meses) avg_pm,
      ROUND(tm) thismonth,
     ROUND(tm-(pm/meses)) dif,
     ROUND((tm-(pm/meses))/(pm / 6),4)*100 perc
    FROM
      (SELECT 
      $campo campo,
      SUM(IF(periodo = 'pm', Litros, 0)) AS pm,
      SUM(IF(periodo = 'tm', Litros, 0)) tm,
      TIMESTAMPDIFF(MONTH, '$fec1', '$fec2') meses
    FROM
      (SELECT 
        $campo,
        CantiDada * CDV Litros,
        IF(
          r.FechElabo <= DATE_SUB('$fec2',INTERVAL 1 MONTH),
          'pm',
          'tm'
        ) periodo 
      FROM
        FClientes c 
        INNER JOIN
        FRemision r 
        ON c.CveCliente = r.Cliente 
        INNER JOIN
        InvProdTerm pt 
        ON r.Producto = pt.PTNumArticulo 
      WHERE pt.PTCatalogo = 'ADBLUE' 
        AND r.FechElabo >= '$fec1' 
        AND r.FechElabo <= '$fec2') ltsxmesxcliente 
    GROUP BY $campo WITH ROLLUP) sums
     ORDER BY avg_pm desc
SQL;
    $respuesta["query"] = trim($query);
    if ($campo == "CDV") {
        $presentaciones = array("1" => "Granel", "1000" => "Tote", "20" => "Bid&oacute;n", "200" => "Tambor", "Totales" => "Totales");
    }
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($query)) {
        $table = "";
        while ($row = $result->fetch_assoc()) {
            ($row["dif"] <= 0) ? $classColor = "negativos" : $classColor = "positivos";
            ($campo == "CDV") ? $etiqueta = $presentaciones[$row["etiqueta"]] : $etiqueta = utf8_encode($row["etiqueta"]);
            $table .= "<tr><td class='etiqueta'>$etiqueta</td><td class='numeric'>" . $row["avg_pm"] . "</td><td class='numeric'>" . $row["thismonth"] . "</td><td class='numeric $classColor'>" . $row["dif"] . "</td><td class='perc $classColor'>" . $row["perc"] . "</td></tr>";
        }
        $respuesta["tbody"] = $table;
        return json_encode($respuesta);
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}