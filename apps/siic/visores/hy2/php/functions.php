<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
require_once("../../../../../php/conexion.php");
$fase = $_GET["f"];
$response = call_user_func($fase);

echo $response;

function dimePermisos()
{
    $dataconn = dataconn("intranet");
    $mysqliR = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    $us = $_GET["u"];
    $id = $_GET["id"];
    $permisos = array();
    $respuesta["status"] = 1;
    if ($mysqliR->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqliR->connect_error;
        return json_encode($respuesta);
    }

    $queryPermisos = "SELECT planta perm,IF(FIND_IN_SET(p.id_planta,per.IDPlanta)>0,'yes','no') sino FROM smartRoad_plantas p, siic_perfiles per  WHERE per.UsrName='$us' AND per.IDReporte=$id";
    if ($resultPerm = $mysqliR->query($queryPermisos)) {
        while ($rowPerm = $resultPerm->fetch_assoc()) {
            $permisos2[$rowPerm["perm"]] = $rowPerm["sino"];
            if ($rowPerm["sino"] == "yes") {
                $permisos[] = $rowPerm["perm"];
            }
        }
    }
    $respuesta["permisos"] = $permisos2;
    $respuesta["permisosPHP"] = "'" . implode("','", $permisos) . "'";
    $respuesta["arrPlantas"] = $permisos;
    return json_encode($respuesta);
}
function dimePlantas()
{
    return json_encode(explode(",", $_SESSION["sessionInfo"]["strPlantas"]["plants"]));
}
function muestraDetallePdn()
{
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }

    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $perm = json_decode(dimePermisos(), true)["permisosPHP"];
    $query = "SELECT 
  IF(
    NumMaquina = 'ML-1',
    'STG',
    REPLACE(NumMaquina, 'ML-', '')
  ) planta,
  NumOrden,
  FechaHora,
  SUM(KgMateriaPrima) kgsU,
  SUM(LotePT) LitrosSK,
  SUM(KgMateriaPrima) / SUM(LotePT) Utilizacion,
  (SELECT 
    GROUP_CONCAT(CAST(f.KgMateriaPrima AS CHAR)) 
  FROM
    FTransfer f 
  WHERE f.NumCodProd = '0001' 
    AND f.NumOrden = FTransfer.NumOrden) cantLotes,
  (SELECT 
    GROUP_CONCAT(f.Lote) 
  FROM
    FTransfer f 
  WHERE f.NumCodProd = '0001' 
    AND f.NumOrden = FTransfer.NumOrden) lotes,
  (SELECT 
    GROUP_CONCAT(
      IF(
        l.Provee = '',
        IF(
          RIGHT(l.Lote, 1) = '',
          RIGHT(l.Lote, 2),
          RIGHT(l.Lote, 1)
        ),
        l.Provee
      )
    ) 
  FROM
    FTransfer f 
    INNER JOIN
    FMPLote l 
    ON l.Lote = f.Lote 
  WHERE f.NumCodProd = '0001' 
   AND IF(
    NumMaquina = 'ML-1',
    'STG',
    REPLACE(NumMaquina, 'ML-', '')
  ) IN ($perm)
    AND f.NumOrden = FTransfer.NumOrden) Prov,
  (SELECT 
    SUM(f2.kgMateriaPrima) 
  FROM
    FTransfer f2 
  WHERE f2.NumCodProd = 'APU' 
    AND f2.NumOrden = FTransfer.NumOrden) agua,
  (SELECT 
    f.CantInvMP 
  FROM
    FTransfer f 
  WHERE f.NumCodProd = '0001' 
    AND f.NumOrden = FTransfer.NumOrden 
  ORDER BY f.CantInvMP 
  LIMIT 1) inv
FROM
  FTransfer 
WHERE DATE(FechaHora) >= '$fec1' 
  AND DATE(FechaHora) <= '$fec2' 
  AND NumCodProd = 'PESOREALU' 
GROUP BY NumMaquina,
  NumOrden DESC,
  FechaHora";
    $arrayTipos = array("036E" => "P", "043" => "P", "P" => "P", "P " => "P", "070" => "C", "172" => "C", "C" => "C", "O" => "I", "D" => "I", "I" => "I", "037D" => "YUY", "356D" => "RUS", "228D" => "HEN", "292P" => "INQ");

    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["numRows"] = $result->num_rows;
        $respuesta["totales"] = [];
        $respuesta["data"]["tblDetalePdn"] = "";
        while ($row = $result->fetch_assoc()) {
            $arrCants = explode(",", $row["cantLotes"]);
            $arrLotes = explode(",", $row["lotes"]);
            $arrProvs = explode(",", $row["Prov"]);
            $stringTipos = "";
            foreach ($arrCants as $key => $value) {
                $stringTipos .= $arrCants[$key]
                    . " " . $arrayTipos[$arrProvs[$key]]
                    . " (LOTE: " . $arrLotes[$key] . "); ";
            }
            $color = ($row["Utilizacion"] <= .345 || $row["Utilizacion"] >= .365) ? " style='color:red;' " : "";
            $respuesta["totales"][$row["planta"]]["agua"] = (array_key_exists($row["planta"], $respuesta["totales"])) ? $respuesta["totales"][$row["planta"]]["agua"] + $row["agua"] : $row["agua"];
            $respuesta["totales"][$row["planta"]]["kgsU"] = (array_key_exists($row["planta"], $respuesta["totales"])) ? $respuesta["totales"][$row["planta"]]["kgsU"] + $row["kgsU"] : $row["kgsU"];
            $respuesta["totales"][$row["planta"]]["LitrosSK"] = (array_key_exists($row["planta"], $respuesta["totales"])) ? $respuesta["totales"][$row["planta"]]["LitrosSK"] + $row["agua"] : $row["LitrosSK"];

            $respuesta["plantas"][$row["planta"]] = $row["planta"];
            $respuesta["data"]["tblDetalePdn"] .= "<tr class='" . $row["planta"] . "' $color><td>" . $row["planta"] . "</td><td>" . $row["NumOrden"] . "</td><td>" . $row["FechaHora"] . "</td><td style='text-align:right;'>" . number_format($row["kgsU"], 2) . "</td><td style='text-align:right;'>" . number_format($row["agua"], 2) . "</td><td style='text-align:right;'>" . number_format($row["LitrosSK"], 2) . "</td><td style='text-align:right;'>" . number_format($row["Utilizacion"], 4) . "</td><td style='text-align:right;'>" . $stringTipos . "</td><td style='text-align:right;'>" . $row["inv"] . " SACOS</td></tr>";
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return json_encode($respuesta);
}

function muestraResumenPdn()
{
    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $arrPlantas = [];

    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $sql = "SELECT nomenc_pdn, planta FROM smartRoad_plantas";
    $res = $mysqli->query($sql);

    while ($row = $res->fetch_assoc()) {
        $arrPlantas[$row["nomenc_pdn"]] = $row["planta"];
    }
    $dataconn = dataconn("scpzar");
    $res->free();
    $mysqli->close();

    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $allP = json_decode(dimePermisos(), true);
    $respuesta["perm"] = $allP["permisosPHP"];
    $respuesta["plantas"] = $arrPlantas;
    $lastyear = strtotime("-1 year", strtotime(date("Y-m-d")));

    //Resumen KgsUrea,LitrosSK,Utilización,Promedio de litros en los meses seleccionados.
    $query = "SELECT 
                NumCodProd,
                NumMaquina planta,
                SUM(KgMateriaPrima) KgsUrea,
                COUNT(DISTINCT(MONTH(FechaHora))) NumMeses,
                SUM(LotePT) LitrosSK,
                format(SUM(KgMateriaPrima) / SUM(LotePT),3) Utilizacion,
                GROUP_CONCAT(FTransfer.NumOrden) ordenes
              FROM
                FTransfer 
              WHERE 
                DATE(FechaHora)>='2015-09-01'  
                AND DATE(FechaHora)>='$fec1' 
                AND DATE(FechaHora)<='$fec2'
                AND NumCodProd = 'PESOREALU' 
              GROUP BY 
                NumCodProd,
                NumMaquina 
              ORDER BY NumMaquina";

    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["numRows"] = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $planta = (array_key_exists($row["planta"], $arrPlantas)) ? $arrPlantas[$row["planta"]] : $row["planta"];
            $respuesta[$planta] = array(
                "urea" => number_format($row["KgsUrea"], 2),
                "Liters" => number_format($row["LitrosSK"], 2),
                "ureaxl" => number_format($row["Utilizacion"], 3),
                "LitersAvg" => number_format($row["LitrosSK"] / $row["NumMeses"], 2),
                "sacos" => 0,
                "ureamix" => "",
                "agua" => 0
            );
            $arrOrdenes[] = $row["ordenes"];
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }

    $ordenes = implode(",", $arrOrdenes);
    $result->free();

    $sqlSacos = <<<SQL
    SELECT 
        NumMaquina planta,
        SUM(KgMateriaPrima) ltsAgua
    FROM
      FTransfer 
    WHERE 
      NumOrden IN ($ordenes)
      AND NumCodProd = 'APU' 
    GROUP BY 
      NumMaquina 
SQL;

    if ($result = $mysqli->query($sqlSacos)) {
        while ($row = $result->fetch_assoc()) {
            $planta = (array_key_exists($row["planta"], $arrPlantas)) ? $arrPlantas[$row["planta"]] : $row["planta"];
            $respuesta[$planta]["agua"] = number_format($row["ltsAgua"], 2);
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    $result->free();

    $mysqli->close();

    return json_encode($respuesta);
}
function mix_pdn()
{
    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $arrPlantas = [];

    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $sql = "SELECT nomenc_pdn, planta FROM smartRoad_plantas";
    $res = $mysqli->query($sql);

    while ($row = $res->fetch_assoc()) {
        $arrPlantas[$row["nomenc_pdn"]] = $row["planta"];
    }
    $respuesta["plantas"] = $arrPlantas;
    
    $dataconn = dataconn("scpzar");
    $res->free();
    $mysqli->close();

    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    

    //Número de sacos utilizados por tipo de U-100
    $queryMix = <<<SQL
  SELECT 
      NumMaquina planta,
      NumCodProd,
      GROUP_CONCAT(FTransfer.NumOrden),
      IF(
          l.Provee = '',
          IF(
              RIGHT(l.Lote, 1) = '',
              RIGHT(l.Lote, 2),
              RIGHT(l.Lote, 1)
          ),
          l.Provee
      ) prov,
      SUBSTRING(p.DescProvee, 1, 5) tipoU,
      SUM(KgMateriaPrima) NumSacos,
      SUM(LotePT) LitrosSK 
  FROM
      FTransfer 
      INNER JOIN
      FMPLote l 
      ON l.Lote = FTransfer.Lote 
      AND l.AlmacenDis = FTransfer.AlmaDist 
      INNER JOIN
      FProveedor p 
      ON l.Provee = p.CveProvee 
  WHERE NumOrden IN 
      (SELECT DISTINCT 
          FTransfer.NumOrden 
      FROM
          FTransfer 
      WHERE DATE(FechaHora) >= '2015-09-01' 
          AND DATE(FechaHora) >= '$fec1' 
          AND DATE(FechaHora) <= '$fec2' 
          AND NumCodProd = 'PESOREALU') 
      AND NumCodProd = '0001' 
  GROUP BY NumMaquina,
      NumCodProd,
      Prov,
      TipoU 
  ORDER BY NumMaquina,
      NumCodProd,
      Prov  
  SQL;
    //    $arrayTipos = array("036E" => "P", "043" => "P", "P" => "P", "P " => "P", "070" => "C", "172" => "C", "C" => "C", "O" => "I", "D" => "I", "I" => "I","037D"=>"YUY","356D"=>"RUS","228D"=>"HEN","292P"=>"INQ");
    $arrayLog = array("036E" => 180.94, "043" => 74.43, "P" => 74.43, "P " => 74.43, "070" => 135.03, "172" => 80.19, "C" => 107, "O" => 74, "D" => 74, "I" => 74, "169" => 80);
    $umix = [];
    $tots = [];
    $subtot = [];
    $provs = [];
    if ($result = $mysqli->query($queryMix)) {
        $respuesta["status"] = 1;

        while ($row = $result->fetch_assoc()) {
            $tipo = $row["tipoU"];
            $planta = (array_key_exists($row["planta"], $arrPlantas)) ? $arrPlantas[$row["planta"]] : $row["planta"];

            $subtot[$planta][$tipo] = (array_key_exists($tipo, $subtot[$planta])) ? $subtot[$planta][$tipo]  + $row["NumSacos"] : $row["NumSacos"];
            $provs[$tipo] = $row["prov"];

            $tots[$planta] = (array_key_exists($planta, $tots)) ? $tots[$planta] + $row["NumSacos"] : $row["NumSacos"];
        }

        foreach ($arrPlantas as $key => $planta) {
            $respuesta[$planta]["sacos"] = $tots[$planta];
            foreach ($subtot[$planta] as $tipo => $numSacos) {
                $umix[$planta][] = substr($tipo, 0, 20) . " " . $provs[$tipo] . " : " . number_format(($numSacos / $tots[$planta]) * 100, 2) . "% (" . $numSacos . " sacos)";
            }
            if (array_key_exists($planta, $umix)) {
                $respuesta[$planta]["ureamix"] = implode("<br/>", $umix[$planta]);
            }
        }
    } else {

        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    $mysqli->close();
    return json_encode($respuesta);
}
function consumosLW()
{
    $perm = json_decode(dimePermisos(), true)["permisosPHP"];
    $fecL4W = date('Y-m-d', strtotime('-4 Monday'));
    $fecLW = date('Y-m-d', strtotime('-1 Monday'));
    $periodo = $_GET["periodo"];
    if ($periodo == "L4W") {
        $fecha = $fecL4W;
    } else {
        $fecha = $fecLW;
    }
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }

    $query = "SELECT 
  NumCodProd,
  YEAR(FechaHora) anio,
  IF(NumMaquina='ML-1','STG',REPLACE(NumMaquina,'ML-','')) planta,
  SUM(KgMateriaPrima) KgsUrea,
  COUNT(DISTINCT(MONTH(FechaHora))) NumMeses,
  SUM(LotePT) LitrosSK,
  format(SUM(KgMateriaPrima) / SUM(LotePT),3) Utilizacion,
  GROUP_CONCAT(FTransfer.NumOrden) ordenes
FROM
  FTransfer 
WHERE DATE(FechaHora)>='2015-09-01' AND DATE(FechaHora)>='$fecha'
  AND NumCodProd = 'PESOREALU' 
  AND IF(NumMaquina='ML-1','STG',REPLACE(NumMaquina,'ML-','')) IN($perm)
GROUP BY NumMaquina,YEAR(FechaHora),NumCodProd 
ORDER BY NumMaquina,YEAR(FechaHora)";
    //    $respuesta["query"] = $query;
    if ($result = $mysqli->query($query)) {
        //        $respuesta["query"] = $query;
        $respuesta["status"] = 1;
        $respuesta["numRows"] = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $ltsAVG = number_format($row["LitrosSK"] / $row["NumMeses"]);
            $respuesta["data"][$row["planta"]] = array(number_format($row["KgsUrea"], 2), number_format($row["LitrosSK"], 2), number_format($row["Utilizacion"], 3), $ltsAVG);
            $respuesta["plantas"][$row["planta"]] = $row["planta"];
            $arrOrdenes[] = $row["ordenes"];
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }

    $ordenes = implode(",", $arrOrdenes);
    $result->free();
    $queryTotMixPlanta = "SELECT 
  IF(
    NumMaquina = 'ML-1',
    'STG',
    REPLACE(NumMaquina, 'ML-', '')
  ) planta,
  YEAR(FechaHora) anio,
  SUM(KgMateriaPrima) Urea,
  COUNT(DISTINCT (MONTH(FechaHora))) NumMeses,
  SUM(LotePT) LitrosSK
FROM
  FTransfer 
  INNER JOIN
  FMPLote l 
  ON l.Lote = FTransfer.Lote 
WHERE NumOrden in ($ordenes)
  AND NumCodProd = '0001' 
GROUP BY NumMaquina,
  YEAR(FechaHora)
ORDER BY NumMaquina,
  YEAR(FechaHora)";

    if ($result = $mysqli->query($queryTotMixPlanta)) {
        $respuesta["status"] = 1;
        $totPlanta = [];
        while ($row = $result->fetch_assoc()) {
            $totPlanta[$row["planta"]] += $row["Urea"];
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }


    $result->free();
    $queryMix = "SELECT 
  IF(
    NumMaquina = 'ML-1',
    'STG',
    REPLACE(NumMaquina, 'ML-', '')
  ) planta,
  YEAR(FechaHora) anio,
  NumCodProd,
 FTransfer.NumOrden,
 IF(l.Provee='',IF(RIGHT(l.Lote,1)='',RIGHT(l.Lote,2),RIGHT(l.Lote,1)),l.Provee) Prov,
  SUM(KgMateriaPrima) NumSacos,
  COUNT(DISTINCT (MONTH(FechaHora))) NumMeses,
  SUM(LotePT) LitrosSK 
FROM
  FTransfer 
 INNER JOIN FMPLote l ON l.Lote=FTransfer.Lote
WHERE NumOrden in ($ordenes) 
  AND NumCodProd = '0001'
GROUP BY NumMaquina,
  YEAR(FechaHora),
  NumCodProd, Prov
ORDER BY NumMaquina,
  YEAR(FechaHora),
  NumCodProd,Prov";
    $arrayTipos = array("036E" => "P", "043" => "P", "P" => "P", "P " => "P", "070" => "C", "172" => "C", "C" => "C", "O" => "I", "D" => "I", "I" => "I", "037D" => "YUY", "356D" => "RUS", "228D" => "HEN", "292P" => "INQ");
    if ($result = $mysqli->query($queryMix)) {
        $respuesta["status"] = 1;
        $subtot = [];
        $sacos = [];
        while ($row = $result->fetch_assoc()) {
            $arrPlantas[$row["planta"]] = $row["planta"];
            $tipo = $arrayTipos[$row["Prov"]];
            $subtot[$row["planta"]][$tipo] += $row["NumSacos"];
            $shares[$row["planta"]][$tipo] = $tipo . ": <br/>" . number_format(($subtot[$row["planta"]][$tipo] / $totPlanta[$row["planta"]]) * 100, 2) . "% <br/> " . $subtot[$row["planta"]][$tipo] . " sacos";
            $sacos[$row["planta"]] += $row["NumSacos"];
        }
        foreach ($arrPlantas as $planta) {
            $respuesta["mix"][$planta][] = implode("<br/>", $shares[$planta]);
            $respuesta["sacos"][$planta] = $sacos[$planta];
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    return json_encode($respuesta);
}

function llenaInventarios()
{

    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    $perm = json_decode(dimePermisos(), true)["permisosPHP"];
    $query = "SELECT 
  IF(AlmacenDis = '', 'STG', AlmacenDis) planta,
  IF(
    Provee = '',
    IF(
      RIGHT(Lote, 1) = '',
      RIGHT(Lote, 2),
      RIGHT(Lote, 1)
    ),
    Provee
  ) Prov,
  SUM(Cantidad) Sacos 
FROM
  FMPLote 
WHERE Codigo = '0001' 
  AND cantidad > 0 
  AND IF(AlmacenDis = '', 'STG', AlmacenDis) IN ($perm)
GROUP BY AlmacenDis,
  Prov";
    //    $respuesta["query"]=$query;
    $arrayTipos = array("036E" => "P", "043" => "P", "P" => "P", "P " => "P", "070" => "C", "172" => "C", "C" => "C", "O" => "I", "D" => "I", "I" => "I", "037D" => "YUY", "356D" => "RUS", "228D" => "HEN", "292P" => "INQ");

    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["numRows"] = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $tipo = $arrayTipos[$row["Prov"]];
            $respuesta["tipos"][$tipo] = $tipo;
            $respuesta["invActual"][$row["planta"]][$tipo] += $row["Sacos"];
            $respuesta["invActual"]["TOTAL"][$tipo] += $row["Sacos"];
            $respuesta["totalesPlanta"][$row["planta"]] += $row["Sacos"];
            $respuesta["totalesPlanta"]["TOTAL"] += $row["Sacos"];
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    $result->free();
    //this week
    $wdate = date('d-m-Y');
    $wn = date('W', strtotime($wdate));
    $wy = date('Y', strtotime($wdate));
    $respuesta["wdate"][] = $wdate;
    $respuesta["wn"][] = number_format($wn, 0);
    $respuesta["wy"][] = $wy;

    for ($i = 1; $i <= 10; $i++) {
        $wdate = date('d-m-Y', strtotime('+' . $i . ' Monday'));
        $wn = date('W', strtotime('+' . $i . ' Monday'));
        $wy = date('Y', strtotime('+' . $i . ' Monday'));
        $respuesta["wdate"][] = $wdate;
        $respuesta["wn"][] = number_format($wn, 0);
        $respuesta["wy"][] = $wy;
    }

    $queryTransit = "SELECT 
  IF(
    c.CentroCosto = 30,
    'STG',
    IF(
      c.CentroCosto = 40,
      'GDL',
      IF(
        c.CentroCosto = 50,
        'MTY',
        c.CentroCosto
      )
    )
  ) planta,
  YEAR(c.FechaEntre) anio,
  WEEK(c.FechaEntre,3) sem,
  Proveedor prov,
  SUM(CantiOrden) cantidad,
  GROUP_CONCAT(NumPedido) pedidos 
FROM
  FOC c 
WHERE c.FechaEntre >= '2015-09-01' 
  AND c.Producto = '0001' 
  AND ISNULL(FechaTermino) 
  AND IF(
    c.CentroCosto = 30,
    'STG',
    IF(
      c.CentroCosto = 40,
      'GDL',
      IF(
        c.CentroCosto = 50,
        'MTY',
        c.CentroCosto
      )
    )
  ) IN ($perm)
GROUP BY c.CentroCosto,
  WEEK(c.FechaEntre,3),
  Proveedor 
ORDER BY c.CentroCosto,
  WEEK(c.FechaEntre,3),
  Proveedor";
    if ($result = $mysqli->query($queryTransit)) {
        $respuesta["status"] = 1;
        $respuesta["numRows"] = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $tipo = $arrayTipos[$row["prov"]];
            $respuesta["cantidades"][$row["planta"]][$row["anio"]][$row["sem"]][$tipo] += $row["cantidad"];
            $respuesta["pedidos"][$row["planta"]][$row["anio"]][$row["sem"]] = $row["pedidos"];
            $respuesta["plantas"][$row["planta"]] = $row["planta"];
        }
        $respuesta["plantas"]["TOTAL"] = "TOTAL";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    $result->free();
    $queryDetalleOC = "SELECT 
  IF(
    c.CentroCosto = 30,
    'STG',
    IF(
      c.CentroCosto = 40,
      'GDL',
      IF(
        c.CentroCosto = 50,
        'MTY',
        c.CentroCosto
      )
    )
  ) planta,
  DATE(c.FechaEntre) ETA,
  WEEK(c.FechaEntre, 3) sem,
  Proveedor prov,
 FProveedor.NomProvee,
  SUM(CantiOrden) cantidad,
  NumPedido pedido,
  c.Observa OBS
FROM
  FOC c INNER JOIN FProveedor ON c.Proveedor=FProveedor.CveProvee 
WHERE c.FechaEntre >= '2015-09-01' 
  AND c.Producto = '0001' 
  AND ISNULL(FechaTermino) 
  AND IF(
    c.CentroCosto = 30,
    'STG',
    IF(
      c.CentroCosto = 40,
      'GDL',
      IF(
        c.CentroCosto = 50,
        'MTY',
        c.CentroCosto
      )
    )
  ) IN($perm)
GROUP BY c.CentroCosto,
  DATE(c.FechaEntre),
  Proveedor,
  NumPedido 
ORDER BY c.CentroCosto,
  DATE(c.FechaEntre),
  Proveedor,
  NumPedido ";
    if ($result = $mysqli->query($queryDetalleOC)) {
        $respuesta["status"] = 1;
        $respuesta["numRows"] = $result->num_rows;
        $tabla = "";
        while ($row = $result->fetch_assoc()) {
            $tipo = $arrayTipos[$row["prov"]];
            $arrPlantas[$row["planta"]] = $row["planta"];
            $tabla .= "<tr class='" . $row["planta"] . "'><td>" . $row["planta"] . "</td><td>" . $row["ETA"] . "</td><td>" . $row["cantidad"] . "</td><td>" . $tipo . "</td><td>" . $row["pedido"] . "</td><td style='text-align:left;'>" . $row["prov"] . "</td><td style='text-align:left;'>" . $row["NomProvee"] . "</td><td>" . $row["OBS"] . "</td></tr>";
        }
        $table = "<table id='tblOcs' style='text-align:center;'>";
        $table .= "<thead><tr></tr><th>PLANTA</th><th>ETA</th><th>CANTIDAD</th><th>TIPO</th><th>ORDEN</th><th colspan='2'>PROVEEDOR</th><th>OBS</th></tr></thead>";
        $table .= "<tbody>" . $tabla . "</tbody>";
        $table .= "</table>";
        $respuesta["tablaOC"] = $table;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    return json_encode($respuesta);
}

function buscarCliente()
{
    $q = $_GET["q"];
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $query = "SELECT FClientes.CveCliente,FClienteEnvio.Determinante,NomCliente,Nombre AS NomDeterminante,FClienteEnvio.Ciudad FROM FClientes,FClienteEnvio WHERE FClientes.CveCliente=FClienteEnvio.Cliente AND FClientes.NomCliente LIKE '%$q%'";
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["numRows"] = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $data[] = json_encode($result->fetch_assoc());
        }
        $respuesta["data"] = $data;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return json_encode($respuesta);
}

function costoU100()
{
    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $query = "SELECT 
  SUM(costo) totCosto,
  SUM(cantidad) totSacos,
  (SUM(costo) / (SUM(cantidad) * 1000)) cunit 
FROM
  (SELECT 
    IF(r.AlmacenR = '', 'STG', r.AlmacenR) planta,
    lpt.Lote,
    r.NumRemi,
    r.Producto,
    lpt.Tipo,
    (SELECT 
      SUM(
        ft4.KgMateriaPrima * (
          rp.TotalSinIVA / rp.CantiRecibida
        ) * rp.TipoCamR
      ) 
    FROM
      FRemisionProveedor rp,
      FTransfer ft4,
      FMPLote lmp 
    WHERE rp.NumRecibo = lmp.REciboMP 
      AND (
        rp.Producto = '0001' 
        OR rp.Producto = '001'
      ) 
      AND lmp.Lote = ft4.Lote 
      AND ft4.NumOrden = ft.NumOrden 
      AND ft4.NumCodProd = '0001' 
      AND lmp.Codigo = '0001' 
    ORDER BY ft4.NumOrden) costo,
    (SELECT 
      SUM(ft5.KgMateriaPrima) 
    FROM
      FRemisionProveedor rp,
      FTransfer ft5,
      FMPLote lmp 
    WHERE rp.NumRecibo = lmp.REciboMP 
      AND (
        rp.Producto = '0001' 
        OR rp.Producto = '001'
      ) 
      AND lmp.Lote = ft5.Lote 
      AND ft5.NumOrden = ft.NumOrden 
      AND ft5.NumCodProd = '0001' 
      AND lmp.Codigo = '0001' 
    ORDER BY ft5.NumOrden) cantidad,
    (SELECT 
      GROUP_CONCAT(ft3.NumOrden) 
    FROM
      FTransfer ft3,
      FMPLote lmp 
    WHERE lmp.Lote = ft3.Lote 
      AND ft3.NumOrden = ft.NumOrden 
      AND ft3.NumCodProd = '0001' 
    ORDER BY ft3.NumOrden) recibos,
    (SELECT 
      GROUP_CONCAT(Lote) 
    FROM
      FTransfer ft2 
    WHERE ft2.NumOrden = ft.NumOrden 
      AND ft2.NumCodProd = '0001' 
    ORDER BY ft2.NumOrden) lotes,
    GROUP_CONCAT(ft.NumOrden),
    pt.PTDesc 
  FROM
    FRemision r 
    INNER JOIN
    FPTLote lpt 
    ON r.NumRemi = lpt.RemisionD 
    AND r.Producto = lpt.Codigo 
    INNER JOIN
    InvProdTerm pt 
    ON r.Producto = pt.PTNumArticulo 
    AND pt.PTTipo = lpt.Tipo 
    INNER JOIN
    FTransfer ft 
    ON ft.LotePT = lpt.Lote 
  WHERE r.FechElabo >= '$fec1' 
    AND r.FechElabo <= '$fec2' 
    AND ft.NumCodProd = '1001' 
    AND ft.CodigoMP = 'EPTB580' 
  GROUP BY r.NumRemi,
    r.Producto,
    lpt.Tipo,
    lpt.Lote) AS todo ";
    //    $respuesta["query"] = $query;
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["numRows"] = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"] = $row;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return json_encode($respuesta);
}

function getFeed()
{
    $xml = simplexml_load_file('http://www.banxico.org.mx/rsscb/rss?BMXC_canal=fix&BMXC_idioma=es');
    print_r($xml->item);
    $content = file_get_contents("http://www.banxico.org.mx/rsscb/rss?BMXC_canal=fix&BMXC_idioma=es");
    $x = new SimpleXmlElement($content);

    $feed = "<ul>";

    foreach ($x->item as $entry) {
        foreach ($entry as $entry1) {
            $feed .= "e1:" . $entry1 . "<br/>";
        }
        foreach ($entry as $key => $entry1) {
            $feed .= "e1:[" . $key . "]=" . $entry1 . "<br/>";
        }
        $feed .= "<li><a href='$entry->link' title='$entry->title' valor='" . $entry["cb:exchangerate"]["cb:value"] . "'>" . $entry->title . "</a></li>";
    }
    $feed .= "</ul>";
    return $content . "<br/>" . $feed;
}

function dimeVentas()
{
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];
    $query = "SELECT 
  IF(r.AlmacenR = '', 'STG', r.AlmacenR) planta,
  pt.PTCatalogo,
  pt.PTNumArticulo cve,
  r.Acabado acab,
  pt.PTDesc,
  CONCAT(pt.PTNumArticulo,' ',r.Acabado) tipoProd,
  SUM(CantiDada) PZAS,
  SUM(CantiDada * pt.CDV) LTS,
  SUM(TotalSinIVA) USD,
  SUM(TotalSinIVA) / SUM(CantiDada) USD_PZA,
  SUM(TotalSinIVA) / SUM(CantiDada * pt.CDV) USD_LT 
FROM
  FRemision r,
  InvProdTerm pt 
WHERE r.Producto = pt.PTNumArticulo 
  AND r.Acabado = pt.PTTipo 
  AND pt.PTCatalogo = 'SKYBLUE' 
  AND r.FechElabo >= '$fec1' 
  AND r.FechElabo <= '$fec2' 
GROUP BY AlmacenR,
  pt.PTNumArticulo,
  r.Acabado";
    $arrayTiposProd = array("3743TLZ" => "TOTE", "3743TLZ GR" => "TOTE EN GRANEL", "3743TP" => "TOTE TP", "3744Z" => "TAMBOR", "3827Z" => "BIDÓN 20L", "4432Z" => "GRANEL", "4433TPZ" => "TOTE TP", "4433TPZ GR" => "TOTE EN GRANEL", "4434Z" => "TAMBOR", "4435Z" => "BIDÓN 20L", "5293Z" => "TAMBOR", "6490Z" => "T 208", "8867" => "BIDÓN 20L");
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["numRows"] = $result->num_rows;
        $values = [];
        while ($row = $result->fetch_assoc()) {
            $tipoProd = ($row["acab"] == "GR") ? $arrayTiposProd[$row["tipoProd"]] : $arrayTiposProd[$row["cve"]];
            $todostipos[] = $tipoProd;
            $arrPlantas[$row["planta"]] = $row["planta"];
            $values[$row["planta"]][$tipoProd] += ($row["LTS"] > 0) ? $row["LTS"] : 0;
        }
        $respuesta["labels"] = array_values(array_unique($arrPlantas));
        $fillColors = array("GRANEL" => "#669ac3", "TOTE EN GRANEL" => "#b2cce1", "TOTE TP" => "#ecb634", "TAMBOR" => "#e75a4b", "BIDÓN 20L" => "#4a9819", "T 208" => "#072065");
        $strokePointColors = array("STG" => "rgba(147,201,0,1)", "GDL" => "rgba(125,116,229,1)", "MTY" => "rgba(255,176,61,1)");
        foreach (array_values(array_unique($todostipos)) as $tipo) {
            $valuestosend = [];
            foreach ($arrPlantas as $planta) {
                $valuestosend[] = ($values[$planta][$tipo] > 0) ? $values[$planta][$tipo] : 0;
            }
            $series[] = "$.gchart.series(" . $tipo . "," . implode(",", array_values($valuestosend)) . "," . $fillColors[$tipo] . ")";
        }
        $respuesta["series"] = implode(",", $series);
        //        $respuesta["labels"] = array("Eating", "Drinking", "Sleeping", "Designing", "Coding", "Cycling", "Running");
        //        $respuesta["datasets"][] = array(label => "My First dataset",
        //            fillColor => "rgba(220,220,220,0.2)",
        //            strokeColor => "rgba(220,220,220,1)",
        //            pointColor => "rgba(220,220,220,1)",
        //            pointStrokeColor => "#fff",
        //            pointHighlightFill => "#fff",
        //            pointHighlightStroke => "rgba(220,220,220,1)",
        //            data => [65, 59, 90, 81, 56, 55, 40]);
        //        $respuesta["datasets"][] = array(label => "My Second dataset",
        //            fillColor => "rgba(151,187,205,0.2)",
        //            strokeColor => "rgba(151,187,205,1)",
        //            pointColor => "rgba(151,187,205,1)",
        //            pointStrokeColor => "#fff",
        //            pointHighlightFill => "#fff",
        //            pointHighlightStroke => "rgba(151,187,205,1)",
        //            data => [28, 48, 40, 19, 96, 27, 100]);
        //        $respuesta["tablaOC"] = $table;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    return json_encode($respuesta);
}

function trazabilidad_consolida()
{
    $fx = $_GET["fx"];
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }
    $fec1 = $_GET["fec1"];
    $fec2 = $_GET["fec2"];

    $mysqli->set_charset("utf8");

    $consolida = [];
    if ($fx == "mp") {
        $query = trazabilidadEPT_MP($fec1, $fec2);
    } else if ($fx == "pt") {
        $query = trazabilidadEPT_PT($fec1, $fec2);
    }

    if (!$result = $mysqli->query($query)) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }

    while ($row = $result->fetch_assoc()) {
        $consolida[] = $row;
    }

    $result->free();
    $mysqli->close();

    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $respuesta["data"] = $consolida;
    return json_encode($respuesta);
}
function trazabilidadEPT_MP($fec1, $fec2)
{
    $query = <<<SQL
SELECT 
    ept.loteEPT LotePTP,
    "Orden de Mezcla" orden_tipo,
    ept.fechaHora FechaHr,
    CONCAT("OM-",t3.NumOrden) orden_num,
    IF(
        t3.AlmaDist = "",
        "STG",
        t3.AlmaDist
    ) Planta,
    t3.NumMaquina,
    mpl.Lote,
    p.CveProvee,
    p.NomProvee,
    LEFT(p.DescProvee, 5) urea_tipo,
    totSacosUrea.totSacos totSacosEns,
    pesoUrea.kgsUrea totKsUreaEns,
    pesoUrea.ltsAPU totLtsAPUEns,
    ept.ltsEPT totLtsEPTEns,
    t3.KgMateriaPrima thisSacos,
    ROUND(
        pesoUrea.kgsUrea / totSacosUrea.totSacos * t3.KgMateriaPrima,
        2
    ) thisKgsUrea,
    ROUND(
        pesoUrea.ltsAPU / totSacosUrea.totSacos * t3.KgMateriaPrima,
        2
    ) thisAPU,
    ROUND(
        ept.ltsEPT / totSacosUrea.totSacos * t3.KgMateriaPrima,
        2
    ) thisEPT 
FROM
    FTransfer t3 
    LEFT JOIN
    FMPLote mpl 
    ON t3.Lote = mpl.Lote 
    LEFT JOIN
    FProveedor p 
    ON mpl.Provee = p.CveProvee 
    LEFT JOIN
    (SELECT 
        NumOrden,
        NumMaquina,
        SUM(
            IF(CodigoMP = "APU", KgMateriaPrima, 0)
        ) ltsAPU,
        SUM(
            IF(
                CodigoMP = "PESOREALU",
                KgMateriaPrima,
                0
            )
        ) kgsUrea 
    FROM
        FTransfer t2 
    WHERE CodigoMP IN ("PESOREALU", "APU") 
    GROUP BY NumOrden,
        NumMaquina) pesoUrea 
    ON t3.NumOrden = pesoUrea.NumOrden 
    AND t3.NumMaquina = pesoUrea.NumMaquina 
    LEFT JOIN
    (SELECT 
        NumOrden,
        NumMaquina,
        SUM(KgMateriaPrima) totSacos 
    FROM
        FTransfer t1 
    WHERE CodigoMP IN ("0001") 
    GROUP BY NumOrden,
        NumMaquina) totSacosUrea 
    ON t3.NumOrden = totSacosUrea.NumOrden 
    AND t3.NumMaquina = totSacosUrea.NumMaquina 
    INNER JOIN
    (SELECT 
        NumOrden,
        NumMaquina,
        LotePT loteEPT,
        fechaHora,
        SUM(KgEntregadosPT) ltsEPT 
    FROM
        FTransfer tEPT 
    WHERE NumCodProd IN ("1001") 
        AND tEPT.STATUS <> "C" 
    GROUP BY NumOrden,
        NumMaquina) ept 
    ON t3.NumOrden = ept.NumOrden 
    AND t3.NumMaquina = ept.NumMaquina 
WHERE t3.CodigoMP = "0001" 
    AND DATE(ept.fechaHora) >= "{$fec1}" 
    AND DATE(ept.fechaHora) <= "{$fec2}" 
ORDER BY ept.fechaHora 
SQL;
    return $query;
}
function trazabilidadEPT_PT($fec1, $fec2)
{
    $query = <<<SQL
  SELECT 
    tr.LotePT LotePTP,
    "Orden de Ensamble" orden_tipo,
    tr.FechaHora FechaHr,
    CONCAT("OE-",- tr.NumOrden) orden_num,
    CONCAT("L-",- tr.NumOrden) LotePT,
    IF(
        tr.AlmaDist = "",
        "STG",
        tr.AlmaDist
    ) Planta,
    dop.NumCodProd Producto_cve,
    dop.TipoAcabado Producto_acabado,
    dop.PTDesc Producto_desc,
    dop.imagen Producto_imagen,
    dop.empaque Producto_empaque,
    dop.Usuario Usuario,
    ROUND(- SUM(tr.KgEntregadosPT), 2) lts_thisOE,
    ROUND(
        - SUM(tr.KgEntregadosPT) * 100 / SUM(dop.KgEntregadosPT),
        2
    ) PercPrimario,
    ROUND(SUM(dop.KgEntregadosPT), 2) TotEnsamble,
    ROUND(SUM(EPTprod)) EptProducido 
FROM
    FTransfer tr 
    INNER JOIN
    (SELECT 
        t.NumOrden,
        t.NumCodProd,
        t.TipoAcabado,
        t.Usuario,
        pt.PTDesc,
        pt.Marca imagen,
        pt.Marca2 empaque,
        KgEntregadosPT,
        t.AlmaDist 
    FROM
        FTransfer t 
        INNER JOIN
        InvProdTerm pt 
        ON t.NumCodProd = pt.PTNumArticulo 
        AND t.TipoAcabado = pt.PTTipo 
    WHERE KgEntregadosPT > 0 
        AND t.NumOrden < 0 
    GROUP BY t.numOrden,
        t.NumCodProd,
        t.TipoAcabado 
    ORDER BY FechaHora DESC) dop 
    ON tr.NumOrden = dop.NumOrden 
    AND tr.AlmaDist = dop.AlmaDist 
    LEFT JOIN
    (SELECT 
        LotePT,
        SUM(KgEntregadosPT) EPTprod 
    FROM
        FTransfer eptprod 
    WHERE NumOrden > 0 
        AND STATUS = "F" 
        AND CodigoMP = "EPTB580" 
    GROUP BY LotePT) prodEPT 
    ON tr.LotePT = prodEPT.LotePT 
WHERE tr.NumOrden < 0 
    AND tr.NumCodProd = 1001 
    AND DATE(tr.FechaHora) >= "{$fec1}" 
    AND DATE(tr.FechaHora) <= "{$fec2}" 
GROUP BY tr.NumOrden,
    tr.AlmaDist,
    tr.LotePT,
    dop.NumCodProd,
    dop.TipoAcabado,
    dop.PTDesc 
ORDER BY tr.FechaHora DESC 
SQL;
    return $query;
}
