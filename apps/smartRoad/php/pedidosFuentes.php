<?php
require_once("../../../php/conexion.php");
$arrEdosMpios = dimeEdosMpiosStd();
$fuente1 = dimePedidosPendientesOdoo();
$fuente2= dimePedidosZK();
$data=$fuente1.$fuente2;
echo $data;

function dimePedidosPendientesOdoo() {
    global $arrEdosMpios;
    $array = file("http://siic.raloy.com.mx/odoo-bi/services/pedidos_adblue.php");
    $data = json_decode($array[0], True);
    $table = "";
    if (count($data) > 0) {
        foreach ($data as $row) {
            $ltsPedido = str_replace(",", "", $row["litros"]);
            $id_det_origen = $row["id_destino"];
            $idDet = (array_key_exists($id_det_origen, $arrEdosMpios)) ? $arrEdosMpios[$id_det_origen]["id_determinante"] : 0;
            $idEM = (array_key_exists($id_det_origen, $arrEdosMpios)) ? $arrEdosMpios[$id_det_origen]["idEM"] : 0;
            $edoCor = (array_key_exists($id_det_origen, $arrEdosMpios)) ? $arrEdosMpios[$id_det_origen]["edoCor"] : 0;
            $mpio = (array_key_exists($id_det_origen, $arrEdosMpios)) ? $arrEdosMpios[$id_det_origen]["mpio"] : 0;
            $fechaComp = formatDate(trim($row["commitment_date2"]));
            $colorNoLiberado = ($idEM == 0 || $idDet == 0) ? " style='color:red;' " : "";

            $inputAtq = "<input class='inputsPed inputAtq' value='' type='text'/>";
            $inputLts = "<input atq='' class='inputsPed inputLts' type='text' placeholder='" . $ltsPedido . "' value='" . $ltsPedido . "'/>";
            $inputEta = "<input class='inputsPed inputEta' value='" . $fechaComp . "' readonly='true' type='text'/>";
            $inputCausa = "<input class='inputsPed inputCausa' value='' placeholder='Cuando Aplique' type='text'/>";
            //Creando tabla
            $table.="<tr $colorNoLiberado idpr='' idDet='" . $idDet . "' id_det_origen='$id_det_origen'>";
            $table.="<td class='atqNmbr'></td>"; //0
            $table.="<td>$inputAtq</td>";
            $table.="<td class='numeric'>$inputLts</td>";
            $table.="<td>$inputEta</td>";
            $table.="<td>$inputCausa</td>";
            $table.="<td>" . $edoCor . "</td>"; //Estado Estándar
            $table.="<td idEM='$idEM'>" . $mpio . "</td>"; //Ciudad Estándar
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
            $table.="</tr>";
        }
    }
    return $table;
}

function dimePedidosZK() {
    global $arrEdosMpios;
    $query = <<<SQL
        SELECT 
            CONCAT(c.CveCliente, "@@", Enviar) id_destino,
            SUM(p.CantiOrden - p.CantiDada) litros,
            p.NumPedido,
            p.FehcEntre commitment_date2,
            p.FechElabo create_date,
            IFNULL(e.Pais, c.PaisCliente) estado,
            IFNULL(e.Ciudad, c.CiudadCliente) ciudad,
            c.CveCliente cliente,
            c.NomCliente cliente_nombre,
            e.Determinante,
            p.Producto clave,
            pt.PTDesc descripcion,
            p.NumRemi albaran,
            p.Usuario ventas,
            p.Autoriza estado_remision,
            p.NumPedido soid 
        FROM
            FPedidos p 
            INNER JOIN
            FClientes c 
            ON p.Cliente = c.CveCliente 
            LEFT JOIN
            FClienteEnvio e 
            ON p.Cliente = e.Cliente 
            AND p.Enviar = e.Determinante 
            INNER JOIN
            InvProdTerm pt 
            ON p.Producto = pt.PTNumArticulo 
        WHERE fechElabo >= "2019-01-01" 
            AND p.cantiDada < p.cantiOrden 
            AND c.cveCliente <> 1 
            AND pt.PTCatalogo = "SKYBLUE" 
        GROUP BY p.NumPedido,
            p.Cliente,
            p.Enviar,
            p.Producto     
SQL;
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    
    $result = $mysqli->query($query);
    $table = "";
    while ($row = $result->fetch_assoc()) {
        $ltsPedido = str_replace(",", "", $row["litros"]);
        $id_det_origen = $row["id_destino"];
        $idDet = (array_key_exists($id_det_origen, $arrEdosMpios)) ? $arrEdosMpios[$id_det_origen]["id_determinante"] : 0;
        $idEM = (array_key_exists($id_det_origen, $arrEdosMpios)) ? $arrEdosMpios[$id_det_origen]["idEM"] : 0;
        $edoCor = (array_key_exists($id_det_origen, $arrEdosMpios)) ? $arrEdosMpios[$id_det_origen]["edoCor"] : 0;
        $mpio = (array_key_exists($id_det_origen, $arrEdosMpios)) ? $arrEdosMpios[$id_det_origen]["mpio"] : 0;
        $fechaComp = formatDate(trim($row["commitment_date2"]));
        $colorNoLiberado = ($idEM == 0 || $idDet == 0) ? " style='color:red;' " : "";

        $inputAtq = "<input class='inputsPed inputAtq' value='' type='text'/>";
        $inputLts = "<input atq='' class='inputsPed inputLts' type='text' placeholder='" . $ltsPedido . "' value='" . $ltsPedido . "'/>";
        $inputEta = "<input class='inputsPed inputEta' value='" . $fechaComp . "' readonly='true' type='text'/>";
        $inputCausa = "<input class='inputsPed inputCausa' value='' placeholder='Cuando Aplique' type='text'/>";
        //Creando tabla
        $table.="<tr $colorNoLiberado idpr='' idDet='" . $idDet . "' id_det_origen='$id_det_origen'>";
        $table.="<td class='atqNmbr'></td>"; //0
        $table.="<td>$inputAtq</td>";
        $table.="<td class='numeric'>$inputLts</td>";
        $table.="<td>$inputEta</td>";
        $table.="<td>$inputCausa</td>";
        $table.="<td>" . $edoCor . "</td>"; //Estado Estándar
        $table.="<td idEM='$idEM'>" . $mpio . "</td>"; //Ciudad Estándar
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
        $table.="</tr>";
    }
    return $table;
}

function dimeEdosMpiosStd() {
    $arrEdosMpios = [];
    $respuesta["status"] = 1;
    $respuesta["error"] = "";

    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    $query = <<<SQL
        SELECT
            d.id_determinante,
            d.id_det_origen id_det_origen,
            m.id,
            m.edoCor,
            m.mpio 
        FROM
            smartRoad_stdDet d LEFT JOIN 
            smartRoad_stdEdosMpios m ON d.id_relEdoMpio=m.id        
SQL;
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $arrEdosMpios[$row["id_det_origen"]] = array("id_determinante" => $row["id_determinante"], "idEM" => $row["id"], "edoCor" => utf8_encode($row["edoCor"]), "mpio" => utf8_encode($row["mpio"]));
        }
    }
    $mysqli->close();
    return $arrEdosMpios;
}

function dimeRuteados() {
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";

    $query = <<<SQL
        SELECT 
            CONCAT(
                TRIM(IFNULL(pedido, '')),
                '@@',
                TRIM(IFNULL(id_det_origen, '')),
                '@@',
                TRIM(IFNULL(cveProducto, '')),
                '@@',
                TRIM(IFNULL(soid, ''))
            ) cveRuteado,
            SUM(ltsSurtir) lts 
        FROM
            smartRoad_pre_ruteo 
        WHERE NOT ISNULL(id_det_origen)  
        GROUP BY cveRuteado
SQL;
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            if ($row["cveRuteado"] != null) {
                $respuesta["datos"][utf8_encode($row["cveRuteado"])] = $row["lts"];
            }
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    $ruteados = $respuesta;
    return;
}

function formatDate($dateddmmyyyy) {
    $v = str_replace("'", " ", $dateddmmyyyy);
    $pos = strpos($v, "/");
    if ($pos == true) {
        $datePart = substr($v, 0, 10);
        if (strlen($v) > 10) {
            $timePart = substr($v, -8, 8);
        } else {
            $timePart = "";
        }

        $y = substr($datePart, -4, 4);
        $m = substr($datePart, -7, 2);
        $d = substr($datePart, -10, 2);
        $v = $y . "-" . $m . "-" . $d . " " . $timePart;
    }
    return $v;
}
