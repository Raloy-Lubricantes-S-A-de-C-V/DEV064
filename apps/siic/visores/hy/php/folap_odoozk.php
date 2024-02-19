<?php

//Revisar Ventas Duplicadas por Albaranes pendientes
//Cómo obtener las devoluciones en Odoo
//Marca en Odoo
//Presentación en Odoo
//Agregar campos faltantes en query scp

// ini_set('display_errors', 1);
// error_reporting(E_ALL);


include("../../../../../php/conexion.php");
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

// require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
// $check = session_check($_GET["t"]);
// if ($check != 1) {
//     echo json_encode(array("status" => "0", "error" => "Session: " . $check));
//     return;
// }
$fx = $_GET["fx"];
echo call_user_func($fx);


function pg()
{
    return pg_connect(dataconn("odoozar"));
}

function getAllData()
{
    $scp = getDataSCP();
    $dataSCP = [];
    if ($scp["status"] == 1 && array_key_exists("jsondata", $scp) && count($scp["jsondata"]) > 0) {
        $dataSCP = $scp["jsondata"];
    } else {
        $respuesta["errorSCP"] = $scp["error"];
    }

    $odoo = getDataOdoo();
    $dataOdoo = [];
    if ($odoo["status"] == 1 && array_key_exists("jsondata", $odoo) && count($odoo["jsondata"]) > 0) {
        $dataOdoo = $odoo["jsondata"];
    } else {
        $respuesta["errorOdoo"] = $odoo["error"];
    }


    $respuesta["jsondata"] = array_merge($dataOdoo, $dataSCP);
    if ($odoo["status"] == 1 || $scp["status"] == 1) {
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
    }
    return json_encode($respuesta);
}
function getDataOdoo()
{


    $f1 = $_GET["f1"];
    $f2 = $_GET["f2"];

//     $sql = <<<SQL
//     SELECT 
//         sp.name albaran,
//         sol.id solid,
//         sm.product_qty cantidad,
//         sol.name clave,
//         rp.name cliente,
//         CONCAT('[',so.partner_shipping_id,'] ',determinantes.name) enviado,
//         CONCAT('[',plantas.id,'] ',plantas.name) planta,
//         'ventas' AS equipo,
//         am.name AS factura,
//         'familia' familia,
//         to_char(sp.date_done,'YYYY-MM-DD') fecha,
//         to_char(am.invoice_date,'YYYY-MM-DD') fechafactura,
//         ROUND(am.amount_untaxed,4) subtotal,
//         sol.price_unit,
//         so.create_date fechaso,
//         geo_pais.name geo_country,
//         geo_estado.name geo_estado,
//         determinantes.city geo_municipio,
//         so.partner_shipping_id id_enviado,
//         ROUND(sm.product_qty/uom.factor,4) litros,
//         so.client_order_ref origin,
//         so.partner_reference pedidocliente,
//         ROUND(1/uom.factor,4) udm,
//         am.currency_id,
//         so.id soid,
//         so.partner_id,
//         so.name pedido,
//         so.state,
//         sp.route,
//         to_char(so.create_date,'YYYY-MM-DD') fechapedido,
//         so.note notas,
//         sp.is_raloy esRaloy
//     FROM 
//         sale_order_line sol 
//         INNER JOIN sale_order so ON so.id=sol.order_id
//         INNER JOIN uom_uom uom ON sol.product_uom=uom.id
//         INNER JOIN product_product pp ON sol.product_id=pp.id
//         LEFT JOIN stock_move sm ON sol.id=sm.sale_line_id 
//         INNER JOIN stock_picking sp ON sm.picking_id=sp.id 
//         LEFT JOIN res_partner rp ON so.partner_id=rp.id
//         LEFT JOIN res_partner determinantes ON so.partner_shipping_id=determinantes.id
//         LEFT JOIN base_plant bp ON so.plant_id=bp.id
//         LEFT JOIN res_partner plantas ON bp.partner_id=plantas.id
//         LEFT JOIN sale_order_line_invoice_rel solir ON solir.order_line_id=sol.id
//         INNER JOIN account_move_line aml ON solir.invoice_line_id=aml.id
//         INNER JOIN account_move am ON aml.move_id=am.id
//         LEFT JOIN res_country geo_pais ON determinantes.country_id=geo_pais.id
//         LEFT JOIN res_country_state geo_estado ON determinantes.state_id=geo_estado.id
//     WHERE 
//         so.state<>'cancel' 
//         AND sol.qty_delivered>0
//         AND sp.date_done>= '$f1 00:00:00' AND sp.date_done <='$f2 23:59:59'
// SQL;


    //     $sql = <<<SQL
    // SELECT 
    //     sp.name albaran,
    //     sol.id,
    //     SUM(sm.product_qty) cantidad,
    //     sol.name clave,
    //     rp.name cliente,
    //     CONCAT('[',so.partner_shipping_id,'] ',determinantes.name) enviado,
    //     CONCAT('[',plantas.id,'] ',plantas.name) planta,
    //     'ventas' AS equipo,
    //     am.name AS factura,
    //     'familia' familia,
    //     to_char(sp.date_done,'YYYY-MM-DD') fecha,
    //     to_char(am.invoice_date,'YYYY-MM-DD') fechafactura,
    //     ROUND(SUM(am.amount_untaxed),4) subtotal,
    //     sol.price_unit,
    //     so.create_date fechaso,
    //     geo_pais.name geo_country,
    //     geo_estado.name geo_estado,
    //     determinantes.city geo_municipio,
    //     so.partner_shipping_id id_enviado,
    //     ROUND(SUM(sm.product_qty/uom.factor),4) litros,
    //     so.client_order_ref origin,
    //     so.partner_reference pedidocliente,
    //     ROUND(1/uom.factor,4) udm,
    //     am.currency_id,
    //     so.id soid,
    //     so.partner_id,
    //     so.name pedido,
    //     so.state,
    //     sp.route,
    //     to_char(so.create_date,'YYYY-MM-DD') fechapedido,
    //     so.note notas,
    //     sp.is_raloy esRaloy
    // FROM 
    //     sale_order_line sol 
    //     INNER JOIN sale_order so ON so.id=sol.order_id
    //     INNER JOIN uom_uom uom ON sol.product_uom=uom.id
    //     INNER JOIN product_product pp ON sol.product_id=pp.id
    //     INNER JOIN stock_move sm ON sol.id=sm.sale_line_id 
    //     LEFT JOIN stock_picking sp ON sm.picking_id=sp.id 
    //     LEFT JOIN res_partner rp ON so.partner_id=rp.id
    //     LEFT JOIN res_partner determinantes ON so.partner_shipping_id=determinantes.id
    //     LEFT JOIN base_plant bp ON so.plant_id=bp.id
    //     LEFT JOIN res_partner plantas ON bp.partner_id=plantas.id
    //     LEFT JOIN account_move am ON so.name=am.invoice_origin
    //     LEFT JOIN res_country geo_pais ON determinantes.country_id=geo_pais.id
    //     LEFT JOIN res_country_state geo_estado ON determinantes.state_id=geo_estado.id
    // WHERE 
    //     so.state<>'cancel' 
    //     AND sm.is_done='t'
    //     AND sp.date_done>= '$f1 00:00:00' AND sp.date_done <='$f2 23:59:59'
    // GROUP BY 
    //     so.id,
    //     sol.id,
    //     sp.id,
    //     sm.id,
    //     uom.id,
    //     rp.id,
    //     determinantes.id,
    //     bp.id,
    //     plantas.id,
    //     am.id,
    //     am.id,
    //     geo_pais.id,
    //     geo_estado.id
    // SQL;

    // $sql = "SELECT * FROM stock_picking";
    // $sql = "SELECT * FROM stock_move";
    // $sql = "select id,name,display_name from res_partner where id=13162";
    // $sql = "SELECT * FROM sale_order_line";

    $sql=<<<SQL
            SELECT 
        sp.name albaran,
        sol.id solid,
        SUM(sm.product_qty) cantidad,
        sol.name clave,
        rp.name cliente,
        CONCAT('[',so.partner_shipping_id,'] ',determinantes.name) enviado,
        CONCAT('[',plantas.id,'] ',plantas.name) planta,
        'ventas' AS equipo,
        am.name AS factura,
        'familia' familia,
        to_char(sp.date_done,'YYYY-MM-DD') fecha,
        to_char(am.invoice_date,'YYYY-MM-DD') fechafactura,
        ROUND(SUM(am.amount_untaxed),4) subtotal,
        sol.price_unit,
        so.create_date fechaso,
        geo_pais.name geo_country,
        geo_estado.name geo_estado,
        determinantes.city geo_municipio,
        so.partner_shipping_id id_enviado,
        ROUND(SUM(sm.product_qty/uom.factor),4) litros,
        so.client_order_ref origin,
        so.partner_reference pedidocliente,
        ROUND(1/uom.factor,4) udm,
        am.currency_id,
        so.id soid,
        so.partner_id,
        so.name pedido,
        so.state,
        sp.route,
        to_char(so.create_date,'YYYY-MM-DD') fechapedido,
        so.note notas,
        sp.is_raloy esRaloy
    FROM 
        sale_order_line sol 
        INNER JOIN sale_order so ON so.id=sol.order_id
        INNER JOIN uom_uom uom ON sol.product_uom=uom.id
        INNER JOIN product_product pp ON sol.product_id=pp.id
        LEFT JOIN stock_move sm ON sol.id=sm.sale_line_id 
        INNER JOIN stock_picking sp ON sm.picking_id=sp.id 
        LEFT JOIN res_partner rp ON so.partner_id=rp.id
        LEFT JOIN res_partner determinantes ON so.partner_shipping_id=determinantes.id
        LEFT JOIN base_plant bp ON so.plant_id=bp.id
        LEFT JOIN res_partner plantas ON bp.partner_id=plantas.id
        LEFT JOIN account_move am ON so.name=am.invoice_origin
        LEFT JOIN res_country geo_pais ON determinantes.country_id=geo_pais.id
        LEFT JOIN res_country_state geo_estado ON determinantes.state_id=geo_estado.id
    WHERE 
        so.state<>'cancel' 
        AND sol.qty_delivered>0
        AND sp.date_done>= '$f1 00:00:00' AND sp.date_done <='$f2 23:59:59'
    GROUP BY 
        so.id,
        sol.id,
        uom.id,
        sp.id,
        rp.id,
        determinantes.id,
        bp.id,
        plantas.id,
        am.id,
        am.id,
        geo_pais.id,
        geo_estado.id
SQL;
    $pg = pg();
    $result = pg_query($pg, $sql);
    $data = [];
    while ($row = pg_fetch_assoc($result)) {
        $data[] = $row;
    }

    // $respuesta["sql"]=$sql;
    $respuesta["error"] = pg_last_error($pg);
    $respuesta["error"] .= pg_result_error($result);
    $respuesta["jsondata"] = $data;
    pg_close($pg);

    if (strlen($respuesta["error"]) > 0) {
        $respuesa["status"] = 0;
    } else {
        $respuesta["status"] = 1;
    }

    // return json_encode($respuesta);
    return $respuesta;
}



function getWarehouses()
{
    $respuesta = [];
    //     $dataconn = dataconn("intranet");
    //     $sql = <<<SQL
    //     SELECT AlmacenR,odoo_zk FROM smartRoad_plantas
    // SQL;
    //     $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    //     $mysqli->set_charset("utf8");
    //     $result = $mysqli->query($sql);
    //     while ($row = $result->fetch_assoc()) {
    //         $respuesta[$row["AlmacenR"]] = $row["odoo_zk"];
    //     }
    //     return $respuesta;
    $plantas = array(
        "" => "[37] SANTIAGO",
        "GDL" => "[43] TLAJOMULCO",
        "SAL" => "[42] SALTILLO",
        "PUE" => "[44] PUEBLA",
        "MER" => "[38] MÉRIDA",
        "APASEO" => "[40] APASEO",
        "CUL" => "[39] CULIACÁN",
        "120" => "[41] MEXICALI",
        "MTY" => "[--] MONTERREY",
    );
    return $plantas;
}

function getDataSCP()
{
    $fieldCurr = ",SUM(totalSinIVA*((100-DescTot)/100)) monto,SUM(totalSinIVA*((100-DescTot)/100)*RTipoCambio) MXN,r.Divisa Moneda";
    // $fieldCurrNC = ",SUM(-r.Precio * Cantidad) monto,SUM(-r.Precio * Cantidad * RTipoCambio) MXN,rd.Divisa Moneda ";
    $fieldCurrNC = ",0 monto,0 MXN,'' Moneda ";
    $groupbyCurr = ",Moneda";
    $fec1 = $_GET["f1"];
    $fec2 = $_GET["f2"];
    $query = <<<SQL
        SELECT 
            "ventas" AS tipotransaccion,
            DATE_FORMAT(r.FechElabo, '%Y-%m-%d') fecha,
            r.AlmacenR planta,
            CONCAT(c.CveCliente,' ',c.NomCliente) cliente,
            IFNULL(CONCAT(e.Determinante,' ',e.Nombre),'') determinante,
            IFNULL(e.ciudad,c.CiudadCliente) mpio,
            IFNULL(e.Estado,c.Estado) edo,
            IFNULL(e.Pais,c.PaisCliente) paisscp,
            e.Sucursal,
            pt.Marca Imagen,
            pt.Marca2 Presentacion,
            CONCAT(r.Producto, ' ', r.Acabado) clave,
            pt.PTDesc producto,
            SUM(CantiDada) Pzas,
            CDV,
            GROUP_CONCAT(r.numRemi) albaran,
            SUM(CantiDada * CDV) litros, 
            IF(c.NomCliente LIKE "%Raloy%","Raloy","ZK") fuente 
            $fieldCurr
        FROM
            FRemision r
            INNER JOIN InvProdTerm pt ON r.Producto=pt.PTNumArticulo AND r.Acabado=pt.PTTipo
            INNER JOIN FClientes c ON r.Cliente=c.CveCliente
            LEFT JOIN FClienteEnvio e ON c.CveCliente=e.Cliente AND e.Determinante=r.Enviado
        WHERE r.FechElabo >= "$fec1" 
            AND r.FechElabo <= "$fec2" 
            AND r.FechElabo<="2022-08-18" 
            AND r.STATUS <> "C" 
            AND PTCatalogo IN ("SKYBLUE") 
        GROUP BY tipotransaccion,
                fecha,
                r.numRemi,
                planta,
                pt.Marca,
                pt.Marca2,
                clave,
                pt.PTDesc,
                c.CveCliente,
                e.Determinante,
                CDV $groupbyCurr
        
        UNION

        SELECT 
            "Devoluciones" AS tipotransaccion,
            DATE_FORMAT(r.Fecha, '%Y-%m-%d') fecha,
            'DEV' planta,
            CONCAT(c.CveCliente, ' ', c.NomCliente) cliente,
            'DEV' determinante,
            'DEV' mpio,
            'DEV' edo,
            'DEV' paisscp,
            'DEV' Sucursal,
            pt.Marca Imagen,
            pt.Marca2 Presentacion,
            CONCAT(r.Producto, ' ', r.Acabado) clave,
            pt.PTDesc producto,
            SUM(- Cantidad) Pzas,
            CDV,
            GROUP_CONCAT(r.Remision) albaran,
            SUM(- Cantidad * CDV) litros,
            IF(
                c.NomCliente LIKE "%Raloy%",
                "Raloy",
                "ZK"
            ) fuente 
            $fieldCurrNC
        FROM
            FDevolCliente r 
            INNER JOIN
            InvProdTerm pt 
            ON r.Producto = pt.PTNumArticulo 
            AND r.Acabado = pt.PTTipo 
            INNER JOIN
            FClientes c 
            ON r.Cliente = c.CveCliente 
        WHERE DATE(r.Fecha) >= "$fec1" 
            AND DATE(r.Fecha) <= "$fec2" 
            AND DATE(r.Fecha)<="2022-08-18"
            AND PTCatalogo IN ("SKYBLUE") 
        GROUP BY tipotransaccion,
            fecha,
            planta,
            pt.Marca,
            pt.Marca2,
            clave,
            pt.PTDesc,
            c.CveCliente,
            determinante,
            r.Remision,
            CDV $groupbyCurr
SQL;
    $respuesta["status"] = 0;
    $respuesta["error"] = "";
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($result = $mysqli->query($query)) {
        $arrWhs = getWarehouses();
        while ($row = $result->fetch_assoc()) {
            $array = [];
            foreach ($row as $key => $value) {
                if ($key == "planta") {
                    $value = (count($arrWhs) > 0 && array_key_exists($value, $arrWhs)) ? $arrWhs[$value] : $value;
                }
                $array[$key] = $value;
            }
            $respuesta["jsondata"][] = $array;
        }
        $respuesta["status"] = 1;
    } else {
        $respuesta["error"] = $mysqli->error;
    };
    return $respuesta;
}
function getPriceLists()
{
    $sql = <<<SQL
        SELECT 
            *
        FROM 
            product_pricelist_item ppli 
            INNER JOIN 
            product_pricelist ppl ON ppli.pricelist_id=ppl.id 
            INNER JOIN product_product ON 
        WHERE 
            ppl.name LIKE 'LP_Raloy_USD'
SQL;

    $pg = pg();
    $result = pg_query($pg, $sql);
    $data = [];
    while ($row = pg_fetch_assoc($result)) {
        $data[] = $row;
    }

    // $respuesta["sql"]=$sql;
    $respuesta["error"] = pg_last_error($pg);
    $respuesta["error"] .= pg_result_error($result);
    $respuesta["jsondata"] = $data;
    pg_close($pg);
    if (strlen($respuesta["error"]) > 0) {
        $respuesa["status"] = 0;
    } else {
        $respuesta["status"] = 1;
    }

    return json_encode($respuesta);
}
function execSQL()
{
    $sql = $_GET["sql"];
    $pg = pg();
    $result = pg_query($pg, $sql);
    $data = [];
    while ($row = pg_fetch_assoc($result)) {
        $data[] = $row;
    }
    $respuesta["error"] = pg_last_error($pg);
    $respuesta["error"] .= pg_result_error($result);
    $respuesta["jsonData"] = $data;
    $respuesta["numRows"] = pg_num_rows($result);
    pg_close($pg);
    if (strlen($respuesta["error"]) > 0) {
        $respuesa["status"] = 0;
    } else {
        $respuesta["status"] = 1;
    }
    return json_encode($respuesta);
}

$queries = array(
    "Products" => "SELECT pp.id,pp.default_code,combination_indices,product_tmpl_id,pt.name, pt.default_code ptdefcode,pt.categ_id,pt.uom_id,pt.active,pt.state FROM product_product pp INNER JOIN product_template pt ON pp.product_tmpl_id=pt.id WHERE pp.product_tmpl_id=239",
    "Pt" => "SELECT pp.default_code dc,pp.*,pt.* FROM product_product pp INNER JOIN product_template pt ON pp.product_tmpl_id=pt.id AND PT.categ_id=90",
);
