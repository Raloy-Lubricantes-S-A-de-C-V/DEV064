<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');

require("../../../php/conexion.php");

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function dimeResults()
{
    $lote = $_GET["nl"];

    if ($lote == "") {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Proporcione un número de Lote válido";
        return json_encode($respuesta);
    }
    $respuesta["status"] = 1; //Status exitoso
    $respuesta["error"] = ""; //Sin errores

    $query = <<<SQL
    SELECT 
        a.Pid,
        a.PNombre param,
        IF(a.PValor REGEXP ('^[0-9]'),ROUND(a.PValor,4),a.PValor) resultado,
        m.Fecha f1,
        m.FechaImpreso f2,
        a.Analista an
    FROM
        muestras m 
    LEFT JOIN
        analisisMuestras a 
    ON 
        m.folio = a.idMuestra 
        AND m.YearFolio = a.YearMuestra 
    WHERE 
        referencia = "$lote" 
SQL;

    //CONEXIÓN A MYSQL
    $dataconn = dataconn("laboratorio");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
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
        if ($result->num_rows == 0) {
            $respuesta["status"] = 0;
            $respuesta["error"] = "No se han encontrado datos con el lote especificado";
            return json_encode($respuesta);
        }

        //CON DATOS
        $valores = [];
        while ($row = $result->fetch_assoc()) {
            switch ($row["Pid"]) {
                case "1089":
                    $valores["MgVal"] = $row["resultado"];
                    break;
                case "1081":
                    $valores["insolVal"] = $row["resultado"];
                    break;
                case "1091":
                    $valores["NiVal"] = $row["resultado"];
                    break;
                case "1088":
                    $valores["KVal"] = $row["resultado"];
                    break;
                case "1090":
                    $valores["NaVal"] = $row["resultado"];
                    break;
                case "322":
                    $valores["identidadVal"] = str_replace("Identico", "Idéntico", $row["resultado"]);
                    break;
                case "1083":
                    $valores["PO4Val"] = $row["resultado"];
                    break;
                case "1080":
                    $valores["IrVal"] = $row["resultado"];
                    break;
                case "1087":
                    $valores["HeVal"] = $row["resultado"];
                    break;
                case "1085":
                    $valores["CrVal"] = $row["resultado"];
                    break;
                case "1132":
                    $valores["densVal"] = $row["resultado"] * 1000;
                    break;
                case "1032":
                    $valores["densVal"] = $row["resultado"] * 1000;
                    break;
                case "1093":
                    $valores["ureaVal"] = $row["resultado"];
                    break;
                case "1086":
                    $valores["CuVal"] = $row["resultado"];
                    break;
                case "1092":
                    $valores["ZVal"] = $row["resultado"];
                    break;
                case "1084":
                    $valores["CaVal"] = $row["resultado"];
                    break;
                case "1079":
                    $valores["biuretVal"] = $row["resultado"];
                    break;
                case "1094":
                    $valores["AlVal"] = $row["resultado"];
                    break;
                case "1082":
                    $valores["aldVal"] = $row["resultado"];
                    break;
                case "1078":
                    $valores["NH3Val"] = $row["resultado"];
                    break;

                default:

                    break;
            }
            $valores["f1"] = $row["f1"];
            $valores["f2"] = $row["f2"];
            $valores["an"] = $row["an"];
        }

        $respuesta["valores"] = $valores;
        return json_encode($respuesta);
    }
}

function resultadosPorFecha()
{
    $resultQuery = ejecutaQueryResult("resultadosPorFecha");
    // $respuesta["query"] = $resultQuery["query"];
    $respuesta["status"] = $resultQuery["status"];
    if ($resultQuery["status"] == 1) {
        $result = $resultQuery["result"];
        $table = "<table class='table w-100 p-2'><col><col><col><col><col><col>";
        $table .= "<thead><th>Lote</th><th><i class='fa fa-calendar'></i> Ingreso</th><th><i class='fa fa-calendar'></i> Resultado</th><th colspan='3'>Status</th></thead>";
        $table .= "<tbody>";
        while ($row = $result->fetch_assoc()) {
            $color = ($row["errok"] == "OK") ? "green" : "red";
            $datetime1 = new DateTime($row["fingreso"]);
            $datetime2 = ($row["fresult"] == "") ? new DateTime(date("Y-m-d H:i:s")) : new DateTime($row["fresult"]);
            $interval = $datetime1->diff($datetime2);
            $colordate = ($interval->format('%a') > 1) ? "red" : "#000";
            $diff = $interval->format('%a día(s)');
            $table .= "<tr plantId='".$row["planta"]."'><td>" . $row["lote"] . "</td><td>" . $row["fingreso"] . "</td><td>" . $row["fresult"] . "</td><td style='color:$colordate;'>$diff</td><td style='color:$color;'>" . utf8_encode($row["errok"]) . "</td><td style='cursor:pointer;'><i class='btnVerAnalisis fa fa-eye' numLote='" . $row["lote"] . "'></i></td></tr>";
        }
        $table .= "</tbody></table>";
        $respuesta["datos"] = $table;
    } else {

        $respuesta["error"] = $resultQuery["error"];
    }
    return json_encode($respuesta);
}
function resultadosPorLote()
{
    $resultQuery = ejecutaQueryResult("resultadosPorFecha");
    $respuesta["query"] = $resultQuery["query"];
    $respuesta["status"] = $resultQuery["status"];
    if ($resultQuery["status"] == 1) {
        $result = $resultQuery["result"];
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"][$row["lote"]] = array(
                "lote" => $row["lote"],
                "color" => ($row["errok"] == "OK") ? "green" : "red",
                "datetime1" => new DateTime($row["fingreso"]),
                "datetime2" => ($row["fresult"] == "") ? new DateTime(date("Y-m-d H:i:s")) : new DateTime($row["fresult"]),
                "errok" => $row["errok"]
            );
        }
    } else {
        $respuesta["error"] = $resultQuery["error"];
    }
    return $respuesta;
}
function muestraDetallesAnalisis()
{
    $resultQuery = ejecutaQueryResult("muestraDetallesAnalisis");
    $respuesta["status"] = $resultQuery["status"];
    if ($resultQuery["status"] == 1) {
        $result = $resultQuery["result"];
        $thead2 = "<th>Parámetro</th><th>MIN</th><th>MAX</th><th>Resultado</th>";
        $tbody = "<tbody>";
        while ($row = $result->fetch_assoc()) {
            $color = ($row["okErr"] == "OK") ? "green" : "red";
            $thead1 = "<tr><th colspan='2'>Lote: " . $row["lote"] . "</th><th colspan='2'>Planta: " . $row["planta"] . "</th></tr>";
            $thead1a = "<tr><th colspan='2'>Fecha Prod: " . $row["fechaProd"] . "</th><th colspan='2'>Tanque: " . $row["tq"] . "</th></tr>";
            $tbody .= "<tr><td><span  style='color:$color;'><i class='fa fa-circle'></i></span> " . $row["param"] . "</td><td>" . $row["valMin"] . "</td><td>" . $row["valMax"] . "</td><td style='color:$color;'>" . $row["resultado"] . "</td></tr>";
        }
        $tbody .= "</tbody>";
        $table = "<table class='table w-100'><thead>$thead1 $thead1a $thead2</thead>$tbody</table>";
        $respuesta["datos"] = $table;
    } else {

        $respuesta["error"] = $resultQuery["error"];
    }
    return json_encode($respuesta);
}

function ejecutaQueryResult($nombreQuery)
{
    $respuesta["status"] = 1; //Status exitoso
    $respuesta["error"] = ""; //Sin errores

    $query = dimeQuery($nombreQuery);
    $respuesta["query"] = $query;
    $dataconn = dataconn("laboratorio");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return $respuesta;
        }
        if ($result->num_rows == 0) {
            $respuesta["status"] = 2;
            $respuesta["error"] = "Sin datos";
            return $respuesta;
        }
        $respuesta["result"] = $result;

        $mysqli->close();
        return $respuesta;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "No se han encontrado datos con el lote especificado";
        return $respuesta;
    }
}
function dimeQuery($nombreQuery = "")
{
    $strpermisos = $_SESSION["sessionInfo"]["strPlantas"]["lab"];
    
    switch ($nombreQuery) {
        case "resultadosPorFecha":
            $fec1 = $_GET["f1"];
            $fec2 = $_GET["f2"];
            $query = <<<SQL
        SELECT 
  lote,
  todos.fechaProd,
  todos.f1 fingreso,
  todos.f2 fresult,
  todos.planta,
  IF(
    FIND_IN_SET("err", GROUP_CONCAT(okErr)) > 0,
    "Err",
    IF(
      FIND_IN_SET("ND", GROUP_CONCAT(okErr)) > 0,
      "ND",
      "OK"
    )
  ) errok 
FROM
  (SELECT 
    Referencia lote,
    mues.fechaProd,
    mues.planta,
    mues.tanque tq,
    mues.f1,
    mues.f2,
    mues.param,
    valMin,
    valMax,
    mues.Valor resultado,
    IF(
      mues.Valor = "" 
      OR ISNULL(mues.Valor),
      "ND",
      IF(
        ISNULL(valMax) 
        AND mues.Valor = "si",
        "OK",
        IF(
          ISNULL(valMin),
          IF(valMax >= mues.valor, "OK", "ERR"),
          IF(
            valMax >= mues.valor 
            AND valMin <= mues.Valor,
            "OK",
            "ERR"
          )
        )
      )
    ) okErr 
  FROM
    Skyblue_referenciaISO iso 
    INNER JOIN
    (SELECT 
      a.Pid,
      CONCAT(
        "20",
        SUBSTRING(m.Referencia, 5, 2),
        "-",
        SUBSTRING(m.Referencia, 3, 2),
        "-",
        LEFT(m.Referencia, 2)
      ) fechaProd,
      SUBSTRING(m.Referencia, 7, 1) planta,
      SUBSTRING(m.Referencia, 8, 1) Tanque,
      m.Referencia,
      m.idMuestra,
      a.PNombre param,
      IF(
        a.PValor REGEXP ('^[0-9]'),
        ROUND(a.PValor, 4),
        a.PValor
      ) valor,
      m.Fecha f1,
      m.respuesta_fecha f2,
      a.Analista an,
      m.Descripcion 
    FROM
      muestras m 
      LEFT JOIN
      analisisMuestras a 
      ON m.folio = a.idMuestra 
      AND m.YearFolio = a.YearMuestra 
    WHERE Pid IN (
        1032,
        1132,
        1078,
        1079,
        1080,
        1081,
        1082,
        1083,
        1084,
        1085,
        1086,
        1087,
        1088,
        1089,
        1090,
        1091,
        1092,
        1093,
        1094,
        322
      ) 
      AND m.Tipo = "ZK" 
      AND CONCAT(
        "20",
        SUBSTRING(m.Referencia, 5, 2),
        "-",
        SUBSTRING(m.Referencia, 3, 2),
        "-",
        LEFT(m.Referencia, 2)
      ) >= "$fec1"  AND CONCAT(
        "20",
        SUBSTRING(m.Referencia, 5, 2),
        "-",
        SUBSTRING(m.Referencia, 3, 2),
        "-",
        LEFT(m.Referencia, 2)
      ) <= "$fec2" 
    ORDER BY a.Pid) mues 
    ON mues.Pid = iso.Pid 
     WHERE planta IN($strpermisos) 
  ORDER BY mues.fechaProd DESC,
    lote,
    iso.ordenEnNorma) todos 
GROUP BY todos.lote 
ORDER BY DATE(todos.fechaProd) DESC  
SQL;
            break;
        case "muestraDetallesAnalisis":
            $lote = $_GET["numLote"];

            $query = <<<SQL
                SELECT 
                    Referencia lote,
                    mues.fechaProd,
                    mues.planta,
                    mues.tanque tq,
                    mues.f1,
                    mues.param,
                    valMin,
                    valMax,
                    mues.Valor resultado,
                    IF(
                      mues.Valor = "" 
                      OR ISNULL(mues.Valor),
                      "ND",
                      IF(
                        ISNULL(valMax) 
                        AND mues.Valor = "si",
                        "OK",
                        IF(
                          ISNULL(valMin),
                          IF(valMax >= mues.valor, "OK", "ERR"),
                          IF(
                            valMax >= mues.valor 
                            AND valMin <= mues.Valor,
                            "OK",
                            "ERR"
                          )
                        )
                      )
                    ) okErr 
                  FROM
                    Skyblue_referenciaISO iso 
                    INNER JOIN
                    (SELECT 
                      a.Pid,
                      CONCAT(
                        "20",
                        SUBSTRING(m.Referencia, 5, 2),
                        "-",
                        SUBSTRING(m.Referencia, 3, 2),
                        "-",
                        LEFT(m.Referencia, 2)
                      ) fechaProd,
                      SUBSTRING(m.Referencia, 7, 1) planta,
                      SUBSTRING(m.Referencia, 8, 1) Tanque,
                      m.Referencia,
                      m.idMuestra,
                      a.PNombre param,
                      IF(
                        a.PValor REGEXP ('^[0-9]'),
                        ROUND(a.PValor, 4),
                        a.PValor
                      ) valor,
                      m.Fecha f1,
                      m.FechaImpreso f2,
                      a.Analista an,
                      m.Descripcion 
                    FROM
                      muestras m 
                      LEFT JOIN
                      analisisMuestras a 
                      ON m.folio = a.idMuestra 
                      AND m.YearFolio = a.YearMuestra 
                      AND m.Referencia = "$lote" 
                    WHERE Pid IN (
                        1032,
                        1132,
                        1078,
                        1079,
                        1080,
                        1081,
                        1082,
                        1083,
                        1084,
                        1085,
                        1086,
                        1087,
                        1088,
                        1089,
                        1090,
                        1091,
                        1092,
                        1093,
                        1094,
                        322
                      ) 
                      AND m.Tipo = "ZK" 
                      AND m.Fecha >= "2017-05-01" 
                    ORDER BY a.Pid) mues 
                    ON mues.Pid = iso.Pid 
                  ORDER BY mues.fechaProd DESC,
                    lote,
                    iso.ordenEnNorma 
SQL;
    }
    return $query;
}

function trazabilidad()
{
    $infoCalidadLote = resultadosPorLote();
    $fec1 = filter_input(INPUT_GET, "f1", FILTER_SANITIZE_STRING);
    $fec2 = filter_input(INPUT_GET, "f2", FILTER_SANITIZE_STRING);
    $sql = <<<SQL
SELECT 

    - tr.NumOrden LotePT,
    tr.FechaHora FechaHr,
    IF(
        tr.AlmaDist = "",
        "STG",
        tr.AlmaDist
    ) Planta,
    tr.LotePT LotePrimario,
    dop.NumCodProd ClavePT,
    dop.TipoAcabado AcabadoPT,
    dop.PTDesc Descripcion,
    dop.Usuario Usuario,
    ROUND(- SUM(tr.KgEntregadosPT), 2) CantPrimario,
    ROUND(- SUM(tr.KgEntregadosPT) * 100 / SUM(dop.KgEntregadosPT),2) PercPrimario,
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

    LEFT JOIN(
    SELECT 
        LotePT,
        SUM(KgEntregadosPT) EPTprod 
    FROM
        FTransfer eptprod 
    WHERE NumOrden > 0 
        AND STATUS = "F" 
        AND CodigoMP = "EPTB580" 
    GROUP BY LotePT 
    ) prodEPT
    ON tr.LotePT=prodEPT.LotePT

WHERE tr.NumOrden < 0 
    AND tr.NumCodProd = 1001 
    AND DATE(tr.FechaHora) >= "$fec1"
    AND DATE(tr.FechaHora) <= "$fec2" 
GROUP BY tr.NumOrden,
    tr.AlmaDist,
    tr.LotePT,
    dop.NumCodProd,
    dop.TipoAcabado,
    dop.PTDesc 
ORDER BY - tr.NumOrden DESC 
SQL;

    //CONEXIÓN A MYSQL
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }
    $mysqli->set_charset("utf8");
    //RESULTADOS DEL QUERY
    if ($result = $mysqli->query($sql)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
        } elseif ($result->num_rows == 0) {
            $respuesta["status"] = 0;
            $respuesta["error"] = "No se han encontrado datos con el lote especificado";
        } else {
            $respuesta["status"] = 1;
            $respuesta["utilizadoEPT"] = array();
            while ($row = $result->fetch_assoc()) {

                $respuesta["color"][$row["LotePrimario"]] = (array_key_exists($row["LotePrimario"], $infoCalidadLote["data"])) ? $infoCalidadLote["data"][$row["LotePrimario"]]["color"] : "#f67a59";
                $respuesta["errok"][$row["LotePrimario"]] = (array_key_exists($row["LotePrimario"], $infoCalidadLote["data"])) ? $infoCalidadLote["data"][$row["LotePrimario"]]["errok"] : "nd";
                $respuesta["countLotes"][$row["Planta"]][] = $row["LotePrimario"];
                if ($respuesta["errok"][$row["LotePrimario"]] != "OK") {
                    $respuesta["lotesErroneos"][$row["Planta"]][] = $row["LotePrimario"];
                }
                $plantas[] = $row["Planta"];

                $respuesta["data"][] = array(
                    "LotePT" => $row["LotePT"],
                    "FechaHr" => $row["FechaHr"],
                    "Planta" => $row["Planta"],
                    "LotePrimario" => $row["LotePrimario"],
                    "ClavePT" => $row["ClavePT"],
                    "AcabadoPT" => $row["AcabadoPT"],
                    "Descripcion" => $row["Descripcion"],
                    "Usuario" => $row["Usuario"],
                    "CantPrimario" => "<span class='numeric'>" . number_format($row["CantPrimario"], 2) . "</span>",
                    "PercPrimario" => "<span class='numeric'>" . number_format($row["PercPrimario"], 2) . " %" . "</span>",
                    "TotEnsamble" =>  "<span class='numeric yellowed'>" . number_format($row["TotEnsamble"], 2) . "</span>",
                    "EptProducido" => "<span class='numeric grayed'>" . number_format($row["EptProducido"], 2) . "</span>"
                );
                // Calcular errores para KPIs
                // $conteo[$row["Planta"]] = array_key_exists($row["planta"], $conteo) ? $conteo[$row["Planta"]] + 1 : 1;
                // $conteoErr[$row["Planta"]] = ($errok=="ok")?$conteoErr[$row["Planta"]]:array_key_exists($row["planta"], $conteoErr) ? $conteo[$row["Planta"]] + 1 : $conteo[$row["Planta"]];

                if (array_key_exists($row["LotePrimario"], $respuesta["utilizadoEPT"])) {
                    $respuesta["utilizadoEPT"][$row["LotePrimario"]] += $row["CantPrimario"];
                } else {
                    $respuesta["utilizadoEPT"][$row["LotePrimario"]] = $row["CantPrimario"];
                }
            }
            foreach ($respuesta["utilizadoEPT"] as $i => $val) {
                $respuesta["utilizadoEPT"][$i] = "<span class='numeric grayed'>" . number_format($val, 2) . "</span>";
            }
            $respuesta["plantas"] = array_unique($plantas);
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Error " . $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}
