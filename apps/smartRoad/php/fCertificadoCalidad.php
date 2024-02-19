<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
require_once("../../../php/conexion.php");

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function llenaCertificado() {
    $folio = $_GET["folio"];
    $iprs = $_GET["iprs"];
    $query = <<<SQL
      SELECT 
  p.loteEPT,
  e.placas,
  p.sellosDescarga sellosDescar,
  p.loteZK AS lote,
  p.id_det_origen,
  GROUP_CONCAT(DISTINCT pedido) pedInt,
  CONCAT(p.cveProducto, ' ' , p.nombreProducto) prod,
  truncate(p.densidad*1000,1) densidad,
  truncate(p.concentracion,2) concentracion,
  truncate(p.indicer,4) indicer,
  p.apariencia,
  f.sellosFijos,
  p.sellosEscotilla sellosEscot,
  CONCAT(FORMAT(SUM(p.ltsSurtir),2),' L') lts,
  DATE_FORMAT(p.fechaHoraCertificado,'%d-%m-%Y %H:%i') fechaHoraCertificado
FROM
  smartRoad_entregas e 
  INNER JOIN
  smartRoad_flota f 
  ON e.placas = f.placas 
  INNER JOIN
  smartRoad_pre_ruteo p 
  ON e.id_entrega = p.id_entrega 
WHERE e.id_entrega = $folio AND p.id_pre_ruteo IN ($iprs) AND p.statusCargaZK=1
GROUP BY p.loteZK,
  p.sellosDescarga,
  p.cveProducto,
  p.id_det_origen

SQL;

    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
        } else {
            //CON DATOS
            if ($result->num_rows > 0) {
                $respuesta["data"] = $result->fetch_assoc();
                $id_det = $respuesta["data"]["id_det_origen"];
                $result->free();
                $pruebas = formatoCertificado($mysqli, $id_det);
                $respuesta["dataresults"] = resultadosAnalisis($respuesta["data"]["loteEPT"], $pruebas);
                $respuesta["status"] = 1;
            } else {
                $respuesta["status"] = 2;
                $respuesta["error"] = "No se han encontrado datos";
            }
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }

    $mysqli->close();
    return json_encode($respuesta);
}

function resultadosAnalisis($lote, $pruebas) {
    $respuesta["lote"] = $lote;
    $respuesta["pruebas"] = $pruebas;
    $query = <<<SQL
        SELECT 
            cat.NomPrueba param,
            cat.Unidad,
            cat.Metodo,
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
            catPruebas cat 
            INNER JOIN
            Skyblue_referenciaISO iso 
            ON cat.IdPrueba = iso.Pid 
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
                    $pruebas
                ) 
                AND m.Tipo = "ZK" 
                AND m.Fecha >= "2017-05-01" 
            ORDER BY a.Pid) mues 
            ON mues.Pid = iso.Pid 
        ORDER BY iso.ordenEnNorma 
SQL;

    $respuesta["status"] = 1; //Status exitoso
    $respuesta["error"] = ""; //Sin errores
    $dataconn = dataconn("laboratorio");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
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
        $tabla = "";
        while ($row = $result->fetch_assoc()) {
            if ($row["okErr"] == "OK") {
                $tabla .= "<tr>"
                        . "<td>" . utf8_encode($row["param"]) . ", " . utf8_encode($row["Unidad"]) . "</td>"
                        . "<td>" . utf8_encode($row["Metodo"]) . "</td>"
                        . "<td>" . number_format($row["valMin"], 4) . "</td>"
                        . "<td>" . number_format($row["valMax"], 4) . "</td>"
                        . "<td>" . $row["resultado"] . "</td>"
                        . "</tr>";
            } else {
                $tabla = "<tr><td colspan='5' style='color:red;font-size:12pt;font-weight:bold;'>Error al emitir el certificado, por favor contacte al encargado de Gestión de Calidad</td></tr>";
            }
        }
        $respuesta["table"] = $tabla;
        $mysqli->close();
        return $respuesta;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "No se han encontrado datos con el lote especificado";
        return $respuesta;
    }
}

function formatoCertificado($mysqli, $id_det_origen) {
    $query = <<<SQL
            SELECT 
                f.id_prueba 
            FROM
                smartRoad_stdDet d 
                INNER JOIN
                merit_formatosCert f 
                ON d.id_formato_cert = f.id_formato_cert 
                INNER JOIN
                merit_referenciaISO p 
                ON f.id_prueba = p.Pid 
            WHERE id_det_origen = '$id_det_origen' 
            ORDER BY p.ordenEnNorma
SQL;
    $result = $mysqli->query($query);
    $pruebas = [];
    while ($row = $result->fetch_assoc()) {
        $pruebas[] = $row["id_prueba"];
    }
    $strPruebas = implode(",", $pruebas);
    return $strPruebas;
}
