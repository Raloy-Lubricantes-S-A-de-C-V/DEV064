<?php
require_once ("../../../php/conexion.php");
$data = dimePedidosFromLog();
echo $data;

function dimePedidosFromLog() {
    $query = <<<SQL
        SELECT 
            logP.*,
            logP.litrosPedido - IFNULL(ruteado.litros, 0) ltsSurtir 
        FROM
            (SELECT 
                CONCAT(
                    lp.soid,
                    lp.pedido,
                    lp.id_destino,
                    lp.clave
                ) idpedido,
                lp.id_destino,
                SUM(lp.litros) litrosPedido,
                d.id_determinante,
                lp.pedido,
                lp.commitment_date2,
                lp.create_date,
                lp.estado,
                lp.ciudad,
                lp.cliente,
                lp.cliente_nombre,
                lp.destino,
                lp.clave,
                lp.descripcion,
                lp.albaran,
                lp.ventas,
                lp.estado_remision,
                lp.soid,
                IFNULL(em.id, 0) id_em,
                IFNULL(em.edoCor, "") edoCor,
                IFNULL(em.mpio, "") mpio,
                lp.routeagain,
                lp.fuente fuentepedido
            FROM
                smartRoad_a_logPedidos lp 
                LEFT JOIN
                smartRoad_stdDet d 
                ON lp.id_destino = d.id_det_origen 
                LEFT JOIN
                smartRoad_stdEdosMpios em 
                ON d.id_relEdoMpio = em.id 
            WHERE NOT ISNULL(lp.id_destino) 
                AND (commitment_Date2 > "2021-09-01"  OR commitment_Date2="0000-00-00" OR lp.pedido IN ('SO34807','SO31788','SO28270') OR lp.routeagain=1)
            GROUP BY lp.soid,
                lp.pedido,
                lp.id_destino,
                lp.clave) logP 
            LEFT JOIN
            (SELECT 
                CONCAT(
                    pr.soid,
                    pr.pedido,
                    pr.id_det_origen,
                    pr.cveProducto
                ) idpedido,
                SUM(pr.ltsSurtir) litros 
            FROM
                smartRoad_pre_ruteo pr 
            GROUP BY pr.soid,
                pr.pedido,
                pr.id_det_origen,
                pr.cveProducto) ruteado 
            ON logP.idpedido = ruteado.idpedido 
        WHERE (logP.litrosPedido - IFNULL(ruteado.litros, 0) > 0) OR routeagain=1       
SQL;
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $result = $mysqli->query($query);
    $table = "";
    while ($row = $result->fetch_assoc()) {
        $ltsPedido = round(str_replace(",", "", $row["litrosPedido"]),2);
        $ltsSurtir = round(str_replace(",", "", $row["ltsSurtir"]),2);
        $fuentepedido = $row["fuentepedido"];
        $id_det_origen = $row["id_destino"];
        $idDet = $row["id_determinante"];
        $idEM = ($row["id_em"]>0) ? $row["id_em"] : 0;
        $edoCor = ($row["edoCor"]!="") ? $row["edoCor"] : 0;
        $mpio = ($row["mpio"]!="") ? $row["mpio"] : 0;
        $fechaComp = $row["commitment_date2"];
        $colorNoLiberado = ($idEM == 0 || $idDet == 0) ? " style='color:red;' " : "";

        $inputAtq = "<input class='inputsPed inputAtq' value='' type='text'/>";
        $inputLts = "<input atq='' class='inputsPed inputLts' type='text' placeholder='" . $ltsSurtir . "' value='" . $ltsPedido . "'/>";
        $inputEta = "<input class='inputsPed inputEta' value='" . $fechaComp . "' readonly='true' type='text'/>";
        $inputCausa = "<input class='inputsPed inputCausa' value='' placeholder='Cuando Aplique' type='text'/>";
        //Creando tabla
        $table.="<tr $colorNoLiberado idpr='' idDet='" . $idDet . "' id_det_origen='$id_det_origen' fuentepedido='$fuentepedido'>";
        $table.="<td class='atqNmbr'></td>"; //0
        $table.="<td>$inputAtq</td>";
        $table.="<td class='numeric'>$inputLts</td>";
        $table.="<td>$inputEta</td>";
        $table.="<td>$inputCausa</td>";
        $table.="<td>" . $edoCor . "</td>"; //Estado Estándar
        $table.="<td idEM='$idEM'>" . utf8_encode($mpio) . "</td>"; //Ciudad Estándar
        $table.="<td>" . $row["pedido"] . "</td>";
        $table.="<td>" . $row["create_date"] . "</td>"; //Fecha de Pedido
        $table.="<td>" . $fechaComp . "</td>"; //Fecha Compromiso
        $table.="<td>" . $row["cliente"] . "</td>"; //Clave del Cliente
        $table.="<td>" . $row["cliente_nombre"] . "</td>"; //Cliente
        $table.="<td>" . $row["destino"] . "</td>"; //en SCP es el Determinante
        $table.="<td>" . $row["clave"] . "</td>"; //Clave Producto
        $table.="<td>" . $row["descripcion"] . "</td>"; //Producto
        $table.="<td>" . $row["estado"] . "</td>"; //Estado Odoo
        $table.="<td>" . $row["ciudad"] . "</td>"; //Ciudad Odoo
        $table.="<td>" . $ltsPedido . "</td>"; //Litros en el pedido
        $table.="<td>" . $ltsPedido . "</td>"; //Litros Pendientes (Sin Ruteo)
        $table.="<td>" . $row["albaran"] . "</td>";
        $table.="<td>" . $row["ventas"] . "</td>";
        $table.="<td>" . $row["estado_remision"] . "</td>";
        $table.="<td>" . $row["soid"] . "</td>";
        $table.="<td>" . $fuentepedido . "</td>";
        $table.="</tr>";
    }
    return $table;
}