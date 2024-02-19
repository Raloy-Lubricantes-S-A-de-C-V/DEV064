<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
require_once("../../../php/conexion.php");

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function dimeOMsSCP() {
    $numLote = $_GET["numLote"];
    $cveProducto = $_GET["cveProducto"];
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    $query = <<<SQL
          SELECT 
            NumOrden,
            KgEntregadosPT - IF(sumas.suma>0,sumas.suma,0) qty,
            NumCodProd cveProducto,
            LotePT,
            PTUniMedida unidad
          FROM
            FTransfer 
            LEFT JOIN
            (SELECT 
              numOrden numOrdenTraz,
              SUM(cantidad) suma 
            FROM
              zk_trazabilidad 
            GROUP BY numOrden) sumas 
            ON sumas.numOrdenTraz = FTransfer.NumOrden 
            LEFT JOIN InvProdTerm ON FTransfer.NumCodProd=InvProdTerm.PTNumArticulo
          WHERE KgEntregadosPT > 0 
            AND LotePT = '$numLote' 
            AND STATUS <> 'C' 
            AND NumCodProd = '$cveProducto' 
            AND NumOrden NOT IN 
            (SELECT 
              traz.numOrden 
            FROM
              zk_trazabilidad traz 
            WHERE traz.numLote = '$numLote')
SQL;
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }
        //CON DATOS
        if ($result->num_rows > 0) {
            //Tabla datos
            //tbody
            $tbody = "";
            $total = 0;
            $selectOM_RR = creaSelectOM_RR();

            while ($row = $result->fetch_assoc()) {
                $respuesta["status"] = 1;
                $tbody.="<tr class='oldRow'>";
                $tbody.="<td>$selectOM_RR</td><td><input disabled='disabled' type='text'class='tdOMezc' value='" . $row["NumOrden"] . "'/></td><td><input disabled='disabled' type='text' class='tdNumLote' value='" . $row["LotePT"] . "'/></td><td><input type='text' class='tdQty' value='" . $row["qty"] . "' qtyEnLaOrden='" . $row["qty"] . "'/></td>";
                $tbody.="</tr>";
                $total = $total + $row["qty"];
                $numLote = $row["LotePT"];
                $respuesta["unidad"] = $row["unidad"];
            }
            $tfoot = "<tr><th colspan='3'>Total</th><th style='text-align:right'>$total</th><th></th></tr>";
            $respuesta["tbody"] = $tbody;
            $respuesta["tfoot"] = $tfoot;
        } else {
            $respuesta["status"] = 2;
        }
        return json_encode($respuesta);
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function dimeOMsTraz() {
    $numLote = $_GET["numLote"];
    $cveProducto = $_GET["cveProducto"];
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }
    $query = <<<SQL
         SELECT 
  traz.id_regTraz idreg,
  traz.numLote,
  traz.numOrden numOrd,
  traz.om_oe_rr tipo,
  traz.cveProducto,
  traz.cantidad,
  traz.uniMedida unidad 
FROM
  zk_trazabilidad traz 
WHERE traz.numOrden IN 
  (SELECT DISTINCT 
    (numReg) 
  FROM
    (
      (SELECT DISTINCT 
        (NumOrden) numReg 
      FROM
        FTransfer 
      WHERE FTransfer.LotePT = '$numLote' 
        AND NumCodProd = '$cveProducto' 
        AND STATUS <> 'C') 
      UNION
      (SELECT DISTINCT 
        (NumOrden) numReg 
      FROM
        zk_trazabilidad 
      WHERE (
          (
            numLote = '$numLote' OR numOrden = '$numLote' 
          )
        ) 
        AND cveProducto = '$cveProducto')
    ) unionTablas) 
ORDER BY traz.numLote,
  traz.om_oe_rr,
  traz.numOrden,
  traz.cantidad 
SQL;
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }
        //CON DATOS
        if ($result->num_rows > 0) {
            $respuesta["OMsSCP"] = dimeOMsSCP($numLote);
            //Tabla datos
            $respuesta["datos"] = "<table>";

            //thead
            $respuesta["datos"].="<thead>"
                    . "<tr><th colspan='6'>Trazabilidad del lote</th></tr>"
                    . "<tr><th>Lote</th><th>Orden</th><th>Clave Prod.</th><th>Cant.</th><th></th><th></th></tr>"
                    . "</thead>";

            $respuesta["datos"].="</thead>";
            //tbody
            $respuesta["datos"].="<tbody>";
            $datosRel = "";
            $total = 0;
            $totalRel = 0;
            while ($row = $result->fetch_assoc()) {
                $respuesta["status"] = 1;
                if ($numLote === $row["numLote"]) {
                    $respuesta["datos"].="<tr>";
                    $respuesta["datos"].="<td>" . $row["numLote"] . "</td>";
                    $respuesta["datos"].="<td>" . $row["tipo"] . $row["numOrd"] . "</td>";
                    $respuesta["datos"].="<td>" . $row["cveProducto"] . "</td>";
                    $respuesta["datos"].="<td class='numeric'>" . number_format($row["cantidad"]) . "</td>";
                    $respuesta["datos"].="<td>" . $row["unidad"] . "</td>";
                    $respuesta["datos"].=($row["tipo"] == "OM") ? "<td class='btnRemoveOMezc' oMezc='" . $row["numOrd"] . "' idreg='" . $row["idreg"] . "' numLote='$numLote'><i class='fa fa-trash-o'></i></td>" : "<td></td>";
                    $respuesta["datos"].="</tr>";
                    
                    $total = $total + $row["cantidad"];
                } else {
                    //si hay datos de órdenes con diferente número de lote en SCP y en Trazabilidad
                    $datosRel.="<tr>";
                    $datosRel.="<td>" . $row["numLote"] . "</td>";
                    $datosRel.="<td>" . $row["tipo"] . $row["numOrd"] . "</td>";
                    $datosRel.="<td>" . $row["cveProducto"] . "</td>";
                    $datosRel.="<td class='numeric'>" . number_format($row["cantidad"]) . "</td>";
                    $datosRel.="<td>" . $row["unidad"] . "</td>";
                    $datosRel.="</tr>";
                    $totalRel = $totalRel + $row["cantidad"];
                }
            }

            $respuesta["datos"].="</tbody>";
            $respuesta["datos"].="<tfoot><tr><td colspan='3'>TOTAL</td><td class='numeric'>" . number_format($total) . "</td><td></td><td></td></tr></tfoot>";
            $respuesta["datos"].="</table>";

            //Tabla si hay datos relacionados
            if (strlen($datosRel) > 0) {
                $respuesta["datos"].="<br/><table>";
                $respuesta["datos"].="<thead>"
                        . "<tr><th colspan='6'>Órdenes relacionadas</th></tr>"
                        . "<tr><th>Lote</th><th>Orden</th><th>Clave Prod.</th><th>Cant.</th><th></th></tr>"
                        . "</thead>";
                $respuesta["datos"].="</thead>";
                //tbody
                $respuesta["datos"].="<tbody>$datosRel</tbody>";
                $respuesta["datos"].="<tfoot><tr><td colspan='3'>TOTAL</td><td class='numeric'>" . number_format($totalRel) . "</td><td></td></tr></tfoot>";
                $respuesta["datos"].="</table>";
            }
        } else {
            $respuesta["status"] = 2;
        }
        return json_encode($respuesta);
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function removeOMezc() {
    $numLote = $_GET["numLote"];
    $oMezc = $_GET["oMezc"];
    $idreg = $_GET["idreg"];
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    $query = <<<SQL
          delete from zk_trazabilidad where id_regTraz=$idreg and numOrden=$oMezc and numLote='$numLote'
SQL;
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        return json_encode($respuesta);
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function guardarCambiosLote() {
    $strValues = $_GET["strValues"];

    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    $query = <<<SQL
          INSERT INTO zk_trazabilidad(om_oe_rr,numLote,numOrden,cantidad,cveProducto,uniMedida,usuario_traz)  VALUES $strValues;
SQL;
    $respuesta["query"] = $query;
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        return json_encode($respuesta);
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}

function creaSelectOM_RR() {
    $select = "<select class='selOMRR'>";
    $select.="<option value='OM' selected='selected'>OM</option>";
    $select.="<option value='RR'>RR</option>";
    $select.="</select>";
    return $select;
}
function dimeDatosOM(){
    $respuesta["status"]=2;
    return json_encode($respuesta);
}

//********************* ORDENES DE ENSAMBLE *****************

function dimeOELote() {
    $numLote = $_GET["numLote"];
    $cveProducto = $_GET["cveProducto"];
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    $query = <<<SQL
          SELECT 
  * 
FROM
  FTransfer t 
WHERE t.LotePT = '030717s102' 
  AND NumCodProd = '1001' 
  AND NumOrden < 0
SQL;
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }
        //CON DATOS
        if ($result->num_rows > 0) {
            //Tabla datos
            //tbody
            $tbody = "";
            $total = 0;
            $selectOM_RR = creaSelectOM_RR();

            while ($row = $result->fetch_assoc()) {
                $respuesta["status"] = 1;
                $tbody.="<tr class='oldRow'>";
                $tbody.="<td>$selectOM_RR</td><td><input disabled='disabled' type='text'class='tdOMezc' value='" . $row["NumOrden"] . "'/></td><td><input disabled='disabled' type='text' class='tdNumLote' value='" . $row["LotePT"] . "'/></td><td><input type='text' class='tdQty' value='" . $row["qty"] . "' qtyEnLaOrden='" . $row["qty"] . "'/></td>";
                $tbody.="</tr>";
                $total = $total + $row["qty"];
                $numLote = $row["LotePT"];
                $respuesta["unidad"] = $row["unidad"];
            }
            $tfoot = "<tr><th colspan='3'>Total</th><th style='text-align:right'>$total</th><th></th></tr>";
            $respuesta["tbody"] = $tbody;
            $respuesta["tfoot"] = $tfoot;
        } else {
            $respuesta["status"] = 2;
        }
        return json_encode($respuesta);
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
}