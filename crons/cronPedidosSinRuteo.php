<?php

date_default_timezone_set('America/Mexico_City');
require_once("../php/conexion.php");
require_once("classMySql.php");
$fuentes = [];
$fuentes["Pedidos Raloy"] = fromServicePedidosRaloy();
$fuentes["Ventas Raloy"] = fromServiceVentasRaloy();
$fuente3 = fromSCPZK();
$fuentes["Ventas ZK"]= executemultiquery($fuente3);
echo json_encode($fuentes);

//******************************************************************************************
// function executemultiquery($queries) {
//     $dataconn = dataconn("intranet");
//     $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
//     $affected = 0;
//     $errors = [];
//     foreach ($queries as $query) {
//         if ($query != "") {
//             if (!$mysqli->query($query)) {
//                 $errors[] = $mysqli->error;
//             }
//             $affected += $mysqli->affected_rows;
//         }
//     }
//     $mysqli->close();
//     return array("errors" => $errors, "affectedRows" => $affected);
// }

// function executeSimpleQuery($mysqli, $query) {
//     $respuesta = array("error" => "", "query" => $query);
//     if (!$mysqli->query($query)) {
//         $respuesta["error"] = $mysqli->error;
//     }
//     return $respuesta;
// }

// function escape_values($v) {
//     if (is_numeric($v)) {
//         $str = "'" . $v . "'";
//     } else {
//         $v = str_replace("'", " ", $v);
//         $pos = strpos($v, "/");
//         if ($pos == true) {
//             $datePart = substr($v, 0, 10);
//             if (strlen($v) > 10) {
//                 $timePart = substr($v, -8, 8);
//             } else {
//                 $timePart = "";
//             }

//             $y = substr($datePart, -4, 4);
//             $m = substr($datePart, -7, 2);
//             $d = substr($datePart, -10, 2);
//             $v = $y . "-" . $m . "-" . $d . " " . $timePart;
//         }
//         $str = "'" . $v . "'";
//     }
//     return $str;
// }

function fromServiceVentasRaloy() {
    
    $from = (array_key_exists("from",$_GET))?$_GET["from"]:date("Y-m-d", strtotime("-1 month"));
    $to = (array_key_exists("to",$_GET))?$_GET["to"]:date("Y-m-d");
    $file = "https://odoo-bi.raloy.com.mx/services/total_cliente_determinante_adblue.php?from=$from&to=$to";
    $array = file($file);
    $data=[];
    $data = json_decode($array[0], True);
    $arrValues = [];
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $affected = 0;
    $errors = [];
    $query="";
    if (count($data) > 0) {
        foreach ($data as $row) {
            $values = [];
            $values[] = $row["id_enviado"];
            $values[] = "'" . str_replace(array('"', "'"), "", $row["cliente"]) . "'";
            $values[] = "'" . str_replace(array('"', "'"), "",$row["enviado"]) . "'";
            $values[] = "'" . str_replace(array('"', "'"), "",$row["geo_municipio"]) . "'";
            $values[] = "'" . str_replace(array('"', "'"), "",$row["geo_estado"]) . "'";

            $arrValues[] = "(" . implode(",", $values) . ",'Odoo Raloy')";
        }
        $strValues = implode(",", $arrValues);

        $query = " INSERT INTO smartRoad_stdDet (id_det_origen,cliente_nombre,destino,ciudad,estado,fuenteDatos) VALUES $strValues ON DUPLICATE KEY UPDATE ciudad=VALUES(ciudad)";
        $exec = executeSimpleQuery($mysqli, $query);
        if ($exec["error"] !== "") {
            $errors[] = $exec["error"];
        } else {
            $affected = $affected + 1;
        }
    }
    $mysqli->close();
    // return array("errors" => $errors, "affectedRows" => $affected,"query"=>$query);
    return array("errors" => $errors, "affectedRows" => $affected);
}

function fromServicePedidosRaloy() {
    $array=[];
    set_time_limit(20);
    $array = file("https://odoo-bi.raloy.com.mx/services/pedidos_adblue.php");
    if(count($array)<=0){
        return array("errors" => "TimeOut", "affectedRows" => "");
    }
    $data = json_decode($array[0], True);
    $fechaHrLog = date("Y-m-d H:i:s");
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $affected = 0;
    $errors = [];
    if (count($data) > 0) {
        foreach ($data as $row) {
            $destino = str_replace(array('"', "'"), "", $row["destino"]);
            $values = [];
            $columns = implode(",", array_keys($row));
            $columns = $columns . ",fechaHrLog" . ",fuente";
            $values[] = $row["soid"];
            $values[] = "'" . $row["pedido"] . "'";
            $values[] = "'" . $row["x_sale_order_cerrada"] . "'";
            $values[] = trim(escape_values($row["create_date"]));
            $values[] = trim(escape_values($row["commitment_date2"]));
            $values[] = $row["id_destino"];
            $values[] = "'" . $destino . "'";
            $values[] = "'" . $row["estado"] . "'";
            $values[] = "'" . $row["ciudad"] . "'";
            $values[] = "'" . str_replace(array('"', "'"), "", $row["calle"]) . "'";
            $values[] = "'" . str_replace(array('"', "'"), "", $row["colonia"]) . "'";
            $values[] = "'" . str_replace("'", " ", $row["obs"]) . "'";
            $values[] = $row["id_cliente"];
            $values[] = "'" . $row["cliente"] . "'";
            $values[] = "'" . str_replace(array('"', "'"), "", $row["cliente_nombre"]) . "'";
            $values[] = "'" . $row["clave"] . "'";
            $values[] = "'" . $row["descripcion"] . "'";
            $values[] = str_replace(",", "", $row["litros"]);
            $values[] = "'" . $row["albaran"] . "'";
            $values[] = "'" . $row["estado_remision"] . "'";
            $values[] = "'" . $row["ventas"] . "'";
            $values[] = "'" . $fechaHrLog . "'";
            $values[] = "'Odoo Raloy'";
//        $escaped_values = array_map("escape_values", array_values($row));
            $strvalues = implode(",", $values);
            $lts = str_replace(",", "", $row["litros"]);
            $commitmentDate = trim(escape_values($row["commitment_date2"]));

//            $queries[] = "INSERT INTO smartRoad_a_logPedidos($columns) VALUES ($strvalues) ON DUPLICATE KEY UPDATE litros='$lts', commitment_date2=$commitmentDate,destino='$destino'";
            $query = "INSERT INTO smartRoad_a_logPedidos($columns) VALUES ($strvalues) ON DUPLICATE KEY UPDATE litros='$lts', commitment_date2=$commitmentDate,destino='$destino'";
            $exec = executeSimpleQuery($mysqli, $query);
            if ($exec["error"] !== "") {
                $errors[] = $exec["error"];
            } else {
                $affected = $affected + 1;
            }
        }

        $queryDet = <<<SQL
        INSERT INTO smartRoad_stdDet (
            cliente,
            cliente_nombre,
            destino,
            ciudad,
            estado,
            calle,
            colonia,
            id_det_origen,
            fuenteDatos
        ) 
        SELECT 
            t.id_cliente,
            t.cliente_nombre,
            t.destino,
            t.ciudad,
            t.estado,
            t.calle,
            t.colonia,
            t.id_destino,
            fuente
        FROM
            smartRoad_a_logPedidos t 
        WHERE NOT ISNULL(t.id_destino) 
        GROUP BY t.id_destino 
            ON DUPLICATE KEY UPDATE 
                cliente = VALUES(cliente),
                calle = VALUES(calle),
                colonia = VALUES(colonia)        
SQL;
    }
    $inserted=0;
    if(!$mysqli->query($queryDet)){
        $errors[] = "Error al integrar determinantes: " . $mysqli["error"];
    }else{
        $inserted=$mysqli->affected_rows;
    }

    $mysqli->close();
    return array("errors" => $errors, "affectedRows" => $affected,"vendorsInserted"=>$inserted);
}

function fromSCPZK() {
    $queryLogPedidos = <<<SQL
            SELECT 
    CONCAT(c.CveCliente, "-", p.NumPedido) soid,
    p.NumRemi pedido,
    p.FechElabo create_date,
    DATE(p.FehcEntre) commitment_date2,
    CONCAT(c.CveCliente, "@@", Enviar) id_destino,
    e.nombre destino,
    IFNULL(e.Pais, c.PaisCliente) estado,
    IFNULL(e.Ciudad, c.CiudadCliente) ciudad,
    IFNULL(e.Direccion, c.DirCliente) calle,
    IFNULL(e.Colonia, c.Colonia) colonia,
    Observ2 obs,
    c.CveCliente id_cliente,
    c.CveCliente cliente,
    c.NomCliente cliente_nombre,
    CONCAT(p.Producto," ",p.Acabado) clave,
    pt.PTDesc descripcion,
    SUM(p.CantiOrden - p.CantiDada)*CDV litros,
    p.NumRemi albaran,
    p.Autoriza estado_remision,
    p.Usuario ventas,
    "SCP ZK" AS fuente 
FROM
    FPedidos p 
    INNER JOIN
    FClientes c 
    ON p.Cliente = c.CveCliente 
    INNER JOIN
    FClienteEnvio e 
    ON p.Cliente = e.Cliente 
    AND p.Enviar = e.Determinante 
    INNER JOIN
    InvProdTerm pt 
    ON p.Producto = pt.PTNumArticulo AND p.Acabado=pt.PTTipo
WHERE fechElabo >= "2019-01-01" 
    AND p.cantiDada < p.cantiOrden 
    AND c.cveCliente <> 1 
    AND pt.PTCatalogo = "SKYBLUE" 
GROUP BY soid,
    p.NumRemi,
    p.Cliente,
    p.Enviar,
    p.Producto
SQL;
    $query = <<<SQL
            SELECT 
    CONCAT(c.CveCliente, "@@", Enviar) id_det_origen,
    c.CveCliente,
    c.NomCliente,
    e.Determinante,
    e.Nombre,
    IFNULL(e.Direccion, c.DirCliente) calle,
    IFNULL(e.Colonia, c.Colonia) col,
    IFNULL(e.Ciudad, c.CiudadCliente) ciudad,
    IFNULL(e.Pais, c.PaisCliente) edo,
    IFNULL(e.CP, c.CPCliente) cp,
    "SCP ZK" AS fuenteDatos 
FROM
    FPedidos p 
    INNER JOIN
    FClientes c 
    ON p.Cliente = c.CveCliente 
    INNER JOIN
    FClienteEnvio e 
    ON p.Cliente = e.Cliente 
    AND p.Enviar = e.Determinante 
WHERE fechElabo >= "2019-01-01" 
GROUP BY id_det_origen,
    c.CveCliente,e.Determinante,
    edo,
    ciudad 
SQL;
    $queries = [];
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        return $mysqli->connect_errno;
    }
    $result = $mysqli->query($queryLogPedidos);
    if ($mysqli->error) {
        return $mysqli->error;
    }
    $fechaHrLog = date("Y-m-d H:i:s");
    $columns = "";
    if ($result->num_rows > 0) {
        $fieldnames = [];
        foreach ($result->fetch_fields() as $field) {
            $fieldnames[] = $field->name;
        }
        $columns = implode(",", $fieldnames);
        $columns = $columns . ",fechaHrLog";
        while ($row = $result->fetch_assoc()) {
            $values = [];
            $values[] = "'" . $row["soid"] . "'";
            $values[] = "'" . $row["pedido"] . "'";
            $values[] = "'" . $row["create_date"] . "'";
            $values[] = "'" . $row["commitment_date2"] . "'";
            $values[] = "'" . $row["id_destino"] . "'";
            $values[] = "'" . $row["destino"] . "'";
            $values[] = "'" . $row["estado"] . "'";
            $values[] = "'" . $row["ciudad"] . "'";
            $values[] = "'" . $row["calle"] . "'";
            $values[] = "'" . $row["colonia"] . "'";
            $values[] = "'" . str_replace("'", " ", $row["obs"]) . "'";
            $values[] = "'" . $row["id_cliente"] . "'";
            $values[] = "'" . $row["cliente"] . "'";
            $values[] = "'" . $row["cliente_nombre"] . "'";
            $values[] = "'" . $row["clave"] . "'";
            $values[] = "'" . $row["descripcion"] . "'";
            $values[] = str_replace(",", "", $row["litros"]);
            $values[] = "'" . $row["albaran"] . "'";
            $values[] = "'" . $row["estado_remision"] . "'";
            $values[] = "'" . $row["ventas"] . "'";
            $values[] = "'" . $row["fuente"] . "'";
            $values[] = "'" . $fechaHrLog . "'";
            $strvalues = implode(",", $values);
            $queries[] = "INSERT INTO smartRoad_a_logPedidos($columns) VALUES ($strvalues) ON DUPLICATE KEY UPDATE litros=VALUES(litros), commitment_date2=VALUES(commitment_date2),destino=VALUES(destino),estado_remision=VALUES(estado_remision);";
        }
    }
    $result->free();
    $result = $mysqli->query($query);
    if ($mysqli->error) {
        return $mysqli->error;
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $values = [];
            $values[] = "'" . $row["id_det_origen"] . "'";
            $values[] = "'" . $row["CveCliente"] . "'";
            $values[] = "'" . utf8_encode($row["NomCliente"]) . "'";
            $values[] = "'" . $row["Determinante"] . " " . utf8_encode($row["Nombre"]) . "'";
            $values[] = "'" . utf8_encode($row["ciudad"]) . "'";
            $values[] = "'" . utf8_encode($row["edo"]) . "'";
            $values[] = "'" . utf8_encode($row["calle"]) . "'";
            $values[] = "'" . utf8_encode($row["col"]) . "'";
            $values[] = "'" . $row["cp"] . "'";
            $values[] = "'" . $row["fuenteDatos"] . "'";
            $arrValues[] = "(" . implode(",", $values) . ")";
        }
        $strValues = implode(",", $arrValues);
        $queries[] = "INSERT INTO smartRoad_stdDet (id_det_origen,cliente,cliente_nombre,destino,ciudad,estado,calle,colonia,cp,fuenteDatos) VALUES $strValues ON DUPLICATE KEY UPDATE ciudad=VALUES(ciudad);";
    } else {
        $queries[] = "";
    }
    return $queries;
}