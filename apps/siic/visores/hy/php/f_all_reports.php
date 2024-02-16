<?php
include("../../../../../php/conexion.php");
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
$check = session_check($_GET["t"]);
if ($check != 1) {
    echo json_encode(array("status" => "0", "error" => "Session: " . $check));
    return;
}

$fx = $_GET["fx"];
echo call_user_func($fx);

function fetchReportData()
{
    if (!array_key_exists("subfx", $_GET) || $_GET["subfx"] == "") {
        return array("status" => 0, "error" => "Unknown Function");
    }
    $subfx = $_GET["subfx"];
    return call_user_func($subfx);
}

function pg()
{
    return pg_connect(dataconn("odoozar"));
}
function resultQueryPG($sql)
{
    $pg = pg_connect(dataconn("odoozar"));
    $result = pg_query($pg, $sql);
    $data = [];
    while ($row = pg_fetch_assoc($result)) {
        $data[] = $row;
    }

    // $respuesta["sql"]=$sql;
    $respuesta["error"] = pg_last_error($pg);
    $respuesta["error"] .= pg_result_error($result);
    $respuesta["data"] = $data;
    pg_close($pg);

    if (strlen($respuesta["error"]) > 0) {
        $respuesa["status"] = 0;
    } else {
        $respuesta["status"] = 1;
    }

    return $respuesta;
}
function resultQueryMariaDB($strConn, $sql)
{
    $connData = dataconn($strConn);
    $mysqli = new mysqli($connData["host"], $connData["user"], $connData["pass"], $connData["db"], $connData["port"]);

    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        die(json_encode($respuesta));
    }

    $mysqli->set_charset("uf8");

    $result = $mysqli->query($sql);
    if ($mysqli->error) {
        $mysqli->close();
        return json_encode(array("status" => 0, "error" => $mysqli->error));
    }

    $respuesta["status"] = 1;
    $respuesta["numRows"] = $result->num_rows;
    $respuesta["data"] = [];

    while ($row = $result->fetch_assoc()) {
        $respuesta["data"][] = $row;
    }

    $mysqli->close();

    return $respuesta;
}
function resultFromJsonService($file)
{
    $respuesta["status"] = 1;
    $ctx = stream_context_create(array(
        'http' =>
        array(
            'timeout' => 1200,  //1200 Seconds is 20 Minutes
        )
    ));
    $respuesta["eTime"]["start"] = date("Y-m-d H:i:s");
    if (!$array = file($file, false, $ctx)) {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Host Desconocido";
        return $respuesta;
    }
    $respuesta["data"] = json_decode($array[0], True);
    $respuesta["eTime"]["end"] = date("Y-m-d H:i:s");
    $elapsedTime = (array) date_diff(date_create($respuesta["eTime"]["end"]), date_create($respuesta["eTime"]["start"]));
    $respuesta["eTime"]["took"] = $elapsedTime["h"] . "h:" . $elapsedTime["i"] . "m:" . $elapsedTime["s"] . "s";
    return $respuesta;
}
function fetchReportsList()
{
    $userId = $_SESSION["sessionInfo"]["userSession"];
    $sql = <<<SQL
        SELECT 
            r.IDReporte id,
            NomReporte label,
            Categoria categoria,
            p.IDPlanta plantas,
            r.fx 
        FROM
            siic_reportes r 
            INNER JOIN
            siic_perfiles p 
            ON r.IDReporte = p.IDReporte 
        WHERE
            r.Version=10
            AND p.id_usuario = $userId
        ORDER BY Categoria,
            NomReporte 
SQL;
    return json_encode(resultQueryMariaDB("intranet", $sql));
}

function salesZKAll()
{
    $f1 = $_GET["f1"];
    $f2 = $_GET["f2"];

    if ($f1 == "" || $f2 == "") {
        $f1 = date("Y-m-d");
        $f2 = date("Y-m-d");
    }

    $result["odoo"] = salesZKOdoo();
    $result["scp"] = salesZKSCP();

    // $data["odoo"] = ($result["odoo"]["status"] == 1) ? $result["odoo"]["data"] : [];
    // $data["scp"] = ($result["scp"]["status"] == 1) ? $result["scp"]["data"] : [];
    // $respuesta["data"] = array_merge($data["odoo"], $data["scp"]);
    $respuesta["data"][] = ($result["odoo"]["status"] == 1) ? $result["odoo"]["data"] : [];
    $respuesta["data"][] = ($result["scp"]["status"] == 1) ? $result["scp"]["data"] : [];

    $respuesta["error"]["odoo"] = $result["odoo"]["error"];
    $respuesta["error"]["scp"] = $result["scp"]["error"];

    if ($result["odoo"]["status"] == 1 || $result["scp"]["status"] == 1) {
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
    }

    $respuesta["dates"] = array("f1" => $f1, "f2" => $f2);

    $respuesta["formats"] = json_decode(
        '[
            {
                "name": "numeric",
                "thousandsSeparator": ",",
                "decimalSeparator": ".",
                "decimalPlaces": 2,
                "currencySymbol": "",
                "currencySymbolAlign": "left",
                "nullValue": "",
                "textAlign": "right",
                "isPercent": false
            }
        ]'
    );

    $respuesta["slice"] = json_decode(
        '{
            "rows": [
                {
                    "uniqueName": "planta"
                },
                {
                    "uniqueName": "cliente"
                }
            ],
            "columns": [
                {
                    "uniqueName": "movimiento_fecha.Year"
                },
                {
                    "uniqueName": "movimiento_fecha.Month"
                },
                {
                    "uniqueName": "Measures"
                }
            ],
            "measures": [
                {
                    "uniqueName": "litros",
                    "aggregation": "sum",
                    "format": "numeric"
                }
            ]
        }'
    );

    return json_encode($respuesta);
}
function facturacion()
{
    $f1 = $_GET["f1"];
    $f2 = $_GET["f2"];
    $sql = <<<SQL
        select
            am."name" factura,
            am.invoice_origin origen,
            am.type factura_tipo,
            am.invoice_payment_state status_pago,
            apt.name termino_pago,
            sp."name" remision,
            sm.to_refund es_devolucion,
            am.invoice_date factura_fecha,
            am.invoice_date_due factura_vencimiento_fecha,
            CONCAT('[', to_char(invoice_date_due at time zone 'UTC-5', 'YYYY/MM/DD'), ']') factura_vencimiento_fecha_completa,
            CONCAT('Sem ', to_char(invoice_date_due at time zone 'UTC-5', 'IW')) factura_vencimiento_fecha_semana,
            case
                when am.amount_residual = 0 then null
                else to_char(invoice_date_due at time zone 'UTC-5'- current_date at time zone 'UTC-5' , 'DD')
            end dias_vencidos,
            aml.quantity,
            aml.price_unit,
            aml.price_subtotal,
            aml.price_total,
            aml.quantity *(1 / uu.factor) litros,
            aml.product_uom_id,
            aml.product_id,
            am.partner_id,
            rp."name" cliente,
            am.partner_shipping_id,
            am.state factura_status,
            am.amount_residual factura_cxc,
            aml.currency_id moneda_id,
            rc."name" moneda,
            uu."name" unidad_medida,
            uu.factor cdv,
            so."name" pedido,
            so.reference referencia,
            so.partner_reference referencia_cliente,
            rp.ref cliente_clave,
            am.l10n_mx_edi_pac_status status_PAC,
            am.l10n_mx_edi_sat_status status_SAT,
            am.uuid_str folio_fiscal,
            am.l10n_mx_edi_partner_bank_id banco_asociado,
            am.l10n_mx_edi_usage uso_cfid,
            am.l10n_mx_edi_time_invoice edi_time
        from
            account_move_line aml
        inner join account_move am on
            aml.move_id = am.id
        inner join res_partner rp on
            am.partner_id = rp.id
        inner join uom_uom uu on
            aml.product_uom_id = uu.id
        left join account_payment_term apt on
            am.invoice_payment_term_id = apt.id
        inner join res_currency rc on
            aml.currency_id = rc.id
        inner join sale_order_line_invoice_rel solir on
            solir.invoice_line_id = aml.id
        inner join sale_order_line sol on
            sol.id = solir.order_line_id
        inner join sale_order so on
            sol.order_id = so.id
        left join stock_move sm on
            sm.sale_line_id = sol.id
        inner join stock_picking sp on
            sm.picking_id = sp.id
        where
            am.invoice_date at time zone 'UTC-5'>= '$f1 00:00:00' 
            AND am.invoice_date at time zone 'UTC-5'<='$f2 23:59:59'
SQL;
    $respuesta = resultQueryPG($sql);
    $respuesta["dates"] = array("f1" => $_GET["f1"], "f2" => $_GET["f2"]);

    $respuesta["formats"] = json_decode(
        '[
            {
                "name": "",
                "thousandsSeparator": ",",
                "decimalSeparator": ".",
                "decimalPlaces": 2,
                "currencySymbol": "$",
                "currencySymbolAlign": "left",
                "nullValue": "",
                "textAlign": "right",
                "isPercent": false
            },
            {
                "name": "55x2iejk",
                "thousandsSeparator": ",",
                "decimalSeparator": ".",
                "decimalPlaces": 2,
                "currencySymbol": "",
                "currencySymbolAlign": "left",
                "nullValue": "",
                "textAlign": "right",
                "isPercent": false
            },
            {
                "name": "5aknt849",
                "thousandsSeparator": ",",
                "decimalSeparator": ".",
                "decimalPlaces": 0,
                "currencySymbol": "",
                "currencySymbolAlign": "left",
                "nullValue": "",
                "textAlign": "right",
                "isPercent": false
            }
        ]'
    );

    $respuesta["slice"] = json_decode(
        '{
            "reportFilters": [
                {
                    "uniqueName": "status_pago"
                },
                {
                    "uniqueName": "factura_status",
                    "filter": {
                        "members": [
                            "factura_status.posted"
                        ]
                    }
                }
            ],
            "rows": [
                {
                    "uniqueName": "cliente",
                    "filter": {
                        "members": [
                            "cliente.ALVEG DISTRIBUCIÓN QUÍMICA S.A. DE C.V."
                        ]
                    },
                    "sort": "unsorted"
                },
                {
                    "uniqueName": "factura",
                    "sort": "unsorted"
                },
                {
                    "uniqueName": "remision",
                    "sort": "unsorted"
                },
                {
                    "uniqueName": "price_total"
                },
                {
                    "uniqueName": "factura_cxc",
                    "filter": {
                        "members": [
                            "factura_cxc.0.00"
                        ],
                        "negation": true
                    },
                    "sort": "desc"
                },
                {
                    "uniqueName": "referencia_cliente",
                    "sort": "unsorted"
                },
                {
                    "uniqueName": "pedido",
                    "sort": "unsorted"
                },
                {
                    "uniqueName": "litros",
                    "sort": "unsorted"
                },
                {
                    "uniqueName": "dias_vencidos",
                    "sort": "unsorted"
                },
                {
                    "uniqueName": "termino_pago",
                    "sort": "unsorted"
                },
                {
                    "uniqueName": "status_pago"
                },
                {
                    "uniqueName": "factura_status",
                    "filter": {
                        "members": [
                            "factura_status.posted"
                        ]
                    }
                }
            ],
            "columns": [
                {
                    "uniqueName": "Measures"
                }
            ],
            "measures": [
                {
                    "uniqueName": "litros",
                    "aggregation": "sum",
                    "format": "55x2iejk"
                },
                {
                    "uniqueName": "dias_vencidos",
                    "aggregation": "sum",
                    "active": false,
                    "format": "5aknt849"
                }
            ],
            "expands": {
                "rows": []
            },
            "flatOrder": [
                "cliente",
                "factura",
                "remision",
                "price_total",
                "factura_cxc",
                "referencia_cliente",
                "pedido",
                "litros",
                "dias_vencidos",
                "termino_pago",
                "status_pago",
                "factura_status"
            ]
        }'
    );
    $respuesta["options"] = json_decode('
    {
        "grid": {
            "type": "flat",
            "showTotals": "off",
            "showGrandTotals": "off"
        }
    }');
    return json_encode($respuesta);
}
function sellOut()
{
    $result["sellOutRaloy"] = salesFromRaloy();
    // $result["sellOutRaloy"] = salesRaloyOdoo();
    $result["sellOutZkSCP"] = salesZKSCP(true);
    $result["sellOutZkOdoo"] = salesZKOdoo(true);

    $respuesta["data"] = array_merge(
        $result["sellOutRaloy"]["data"],
        $result["sellOutZkSCP"]["data"],
        $result["sellOutZkOdoo"]["data"]
    );

    $respuesta["status"] = ($result["sellOutRaloy"]["status"] == 1 || $result["sellOutZkSCP"]["status"] == 1 || $result["sellOutZkOdoo"]["status"] == 1) ? 1 : 0;
    $respuesta["dates"] = array("f1" => $_GET["f1"], "f2" => $_GET["f2"]);

    $respuesta["formats"] = json_decode(
        '[
            {
                "name": "numeric",
                "thousandsSeparator": ",",
                "decimalSeparator": ".",
                "decimalPlaces": 2,
                "currencySymbol": "",
                "currencySymbolAlign": "left",
                "nullValue": "",
                "textAlign": "right",
                "isPercent": false
            }
        ]'
    );

    $respuesta["slice"] = json_decode(
        '{
            "rows": [
                {
                    "uniqueName": "clave"
                }
            ],
            "columns": [
                {
                    "uniqueName": "Measures"
                }
            ],
            "measures": [
                {
                    "uniqueName": "litros",
                    "aggregation": "sum",
                    "format": "numeric"
                }
            ]
        }'
    );

    return json_encode($respuesta);
}

function salesFromRaloy()
{
    $f1 = $_GET["f1"];
    $f2 = $_GET["f2"];

    $serviceUrl = "https://odoo-bi.raloy.com.mx/services/total_cliente_determinante_adblue.php?from=$f1&to=$f2";

    $result = resultFromJsonService($serviceUrl);
    $data = [];
    foreach ($result["data"] as $row) {
        $newRow = [];


        if (stripos($row["descripcion"], "MBADBLUE") !== false) {
            $newRow["producto_marca"] = "MB";
        } else if (stripos($row["descripcion"], "TRP ") !== false) {
            $newRow["producto_marca"] = "PACCAR";
        } else if (stripos($row["descripcion"], "FLRT") !== false) {
            $newRow["producto_marca"] = "INTERNATIONAL";
        } else if (stripos($row["descripcion"], "CUMMINS") !== false) {
            $newRow["producto_marca"] = "CUMMINS";
        } else if (stripos($row["cliente"], "DAIMLER TRUCKS") !== false) {
            $newRow["producto_marca"] = "MB FF";
        } else if (stripos($row["cliente"], "AUDI") !== false) {
            $newRow["producto_marca"] = "AUDI FF";
        } else if (stripos($row["cliente"], "Volvo") !== false) {
            $newRow["producto_marca"] = "VOLVO";
        } else {
            $newRow["producto_marca"] = "SKYBLUE";
        }

        $newRow["movimiento_fecha"] = $row["fecha"];
        $newRow["pedido_fecha"] = $row["fechaso"];
        $newRow["producto_familia"] = $row["familia"];
        $newRow["comercial"] = $row["equipo"];
        $newRow["cliente"] = $row["cliente"];
        $newRow["determinante"] = $row["enviado"];
        $newRow["determinante_id"] = $row["id_enviado"];
        $newRow["determinante_municipio"] = $row["geo_municipio"];
        $newRow["determinante_estado"] = $row["geo_estado"];
        $newRow["determinante_pais"] = $row["geo_country"];
        $newRow["remision"] = $row["albaran"];
        $newRow["pedido"] = $row["origin"];
        $newRow["pedido_ref_cliente"] = $row["pedidocliente"];
        $newRow["pedido_ref_cliente_2"] = $row["referenciacliente"];
        $newRow["factura"] = $row["factura"];
        $newRow["factura_tasa"] = $row["x_invoice_line_tax_str"];
        $newRow["factura_precio_tax"] = $row["price_tax"];
        $newRow["factura_moneda"] = $row["name"];
        $newRow["factura_precio_unitario"] = $row["price_unit"];
        $newRow["factura_subtotal"] = $row["subtotal"];
        $newRow["factura_fecha"] = $row["fechafactura"];
        $newRow["producto_clave"] = $row["clave"];
        $newRow["producto_nombre_variante"] = $row["descripcion"];
        $newRow["producto_unidad_medida"] = $row["udm"];
        $newRow["producto_empaque"] = $row["empaque"];
        $newRow["cantidad"] = $row["cantidad"];
        $newRow["litros"] = $row["litros"];
        $newRow["fuente"] = "Odoo Raloy";

        $data[] = $newRow;
    }
    $respuesta["data"] = $data;
    $respuesta["status"] = $result["status"];
    return $respuesta;
}

function salesZKSCP($isSellOut = false)
{
    $whereSellOut = ($isSellOut) ? "AND r.Cliente NOT IN ('1','1P')" : "";
    $f1 = $_GET["f1"];
    $f2 = $_GET["f2"];

    // var_dump("hola");
    $sql = <<<SQL
        SELECT 
            r.NumRemi                                       remision,
            r.CantiDada                                     cantidad,
            CONCAT(r.Producto, " ", r.Acabado)              producto_clave,
            IFNULL(c.nombreOdoo,c.NomCliente)               cliente,
            c.CveCliente                                    cliente_id,
            r.DescArti                                      producto_nombre_variante,
            pt.Marca2                                       producto_empaque,
            pt.Marca                                        producto_marca,
            e.Determinante                                  determinante,
            e.Sucursal                                      determinante_sucursal,
            IF(c.Vendedor2="","Zar Kruse",c.Vendedor2)      comercial,
            CONCAT(r.Factura, r.SerieF)                     factura,
            pt.PTCatalogo                                   producto_familia,
            r.FechElabo                                     movimiento_fecha,
            r.FechFactura                                   factura_fecha,
            r.totalSinIVA                                   factura_subtotal,
            r.totalSinIVA/r.CantiDada                       factura_precio_unitario,
            p.FechElabo                                     pedido_fecha,
            "Mexico"                                        determinante_pais,
            IFNULL(e.Estado, c.Estado)                      determinante_estado,
            IFNULL(e.Ciudad, c.Municipio)                   determinante_municipio,
            CONCAT(r.Cliente, "@@", r.Enviado)              determinante_id,
            (r.CantiDada * pt.CDV)                          litros,
            p.NumRemi                                       pedido,
            p.PedCli                                        pedido_ref_cliente,
            r.AlmacenR                                      planta_id,
            alm.DescripcionOdoo                             planta,
            pt.PTUniMedida                                  producto_unidad_medida,
            r.Divisa                                        factura_moneda,
            "SCP ZK"                                        fuente,
            ""                                              ruta
        FROM
            FRemision r 
            INNER JOIN
            InvProdTerm pt 
            ON r.Producto = pt.PTNumArticulo 
            AND r.Acabado = pt.PTTipo 
            INNER JOIN
            FClientes c 
            ON r.Cliente = c.CveCliente 
            LEFT JOIN
            FClienteEnvio e 
            ON r.Cliente = e.Cliente 
            AND r.Enviado = e.Determinante 
            LEFT JOIN
            FPedidos p 
            ON r.PedidoSist = p.NumPedido 
            AND r.Cliente = p.Cliente 
            AND r.Pedido = p.PedCli 
            AND r.Producto = p.Producto 
            LEFT JOIN FAlmacen alm ON r.AlmacenR=alm.ClaveAlmacen
        WHERE r.FechElabo >= "$f1" 
            AND r.FechElabo <= "$f2" 
            AND r.FechElabo <= "2022-08-18" 
            AND pt.PTCatalogo = "SKYBLUE" 
            AND r.STATUS <> "C"
            $whereSellOut
        UNION
        SELECT 
            r.Remision                                      remision,
            -r.Cantidad                                     cantidad,
            CONCAT(r.Producto, " ", r.Acabado)              producto_clave,
            IFNULL(c.nombreOdoo,c.NomCliente)               cliente,
            c.CveCliente                                    cliente_id,
            pt.PTDesc                                       producto_nombre_variante,
            pt.Marca2                                       producto_empaque,
            pt.Marca                                        producto_marca,
            ''                                              determinante,
            ''                                              determinante_sucursal,
            ''                                              comercial,
            CONCAT(r.Factura, r.SerieFacD)                  factura,
            pt.PTCatalogo                                   producto_familia,
            r.Fecha                                         movimiento_fecha,
            ''                                              factura_fecha,
            0                                               factura_subtotal,
            0                                               factura_precio_unitario,
            ''                                              pedido_fecha,
            ''                                              determinante_pais,
            ''                                              determinante_estado,
            ''                                              determinante_municipio,
            ''                                              determinante_id,
            (-r.Cantidad * pt.CDV)                          litros,
            ''                                              pedido,
            ''                                              pedido_ref_cliente,
            ''                                              planta_id,
            ''                                              planta,
            pt.PTUniMedida                                  producto_unidad_medida,
            ''                                              factura_moneda,
            "SCP ZK"                                        fuente,
            ""                                              ruta 
        FROM
            FDevolCliente r 
            INNER JOIN
            InvProdTerm pt 
            ON r.Producto = pt.PTNumArticulo 
            AND r.Acabado = pt.PTTipo 
            INNER JOIN
            FClientes c 
            ON r.Cliente = c.CveCliente 
        WHERE 
            r.Fecha >= "$f1" 
            AND r.Fecha <= "$f2" 
            AND r.Fecha <= "2022-08-18" 
            AND pt.PTCatalogo = "SKYBLUE"
        $whereSellOut
SQL;
    return resultQueryMariaDB("scpzar", $sql);
}
function ordersZKOdoo()
{
    $f1 = $_GET["f1"];
    $f2 = $_GET["f2"];

    $sql = <<<SQL
        SELECT          
            sml.product_id                              producto_id,
            pp.default_code                             producto_clave,
            LEFT(pp.default_code,2)                     producto_marca,
            RIGHT(LEFT(pp.default_code,4),2)            producto_empaque,
            pt.NAME                                     producto_nombre_padre,
            sm.NAME                                     producto_nombre_variante,
            pt.categ_id                                 producto_categoria_id,
            sml.product_uom_id                          producto_unidad_medida_id,
            uom.name                                    producto_unidad_medida,
            (1/uom.factor)                              producto_cdv,
            pp.id                                       producto_id,
            pt.id                                       producto_template_id,
            sml.picking_id                              picking_id,
            sml.id                                      movimiento_id_linea,
            sml.product_qty                             movimiento_cantidad_original,
            sml.product_uom_qty                         movimiento_cantidad_uom,
            to_char(sml.DATE at time zone 'UTC-5','YYYY-MM-DD')              movimiento_fecha,
            sml.state                                   movimiento_status_linea,
            sml.reference                               movimiento_referencia_linea,
            sml.production_id                           movimiento_production_id,
            sm.id                                       movimiento_id,
            sm.to_refund                                movimiento_es_retorno,
            sp.NAME                                     remision,
            sp.id                                       remision_id,
            so.NAME                                     pedido,
            sp.sale_id                                  pedido_id,
            sol.id                                      pedido_linea_id,
            sol.product_uom                             pedido_unidad_medida_id,
            uom_order.name                              pedido_unidad_medida,
            so.client_order_ref                         pedido_ref_cliente,
            so.plant_id                                 planta_id,
            CONCAT('[',plantas.id,'] ',plantas.name)    planta,
            so.route                                    ruta,
            rp.name                                     cliente,
            rp.id                                       cliente_id,
            CONCAT(
                '[',so.partner_shipping_id,'] ',
                determinantes.name
            )                                           determinante,
            geo_pais.name                               determinante_pais,
            geo_estado.name                             determinante_estado,
            determinantes.city                          determinante_municipio,
            so.partner_shipping_id                      determinante_id,
            fact.move_name                              factura,
            fact.move_id                                factura_id,
            fact.quantity                               factura_cantidad,
            fact.price_unit                             factura_precio_pieza,
            fact.price_unit*uom.factor                  factura_precio_litro,
            fact.currency_id                            factura_moneda,
            fact.fecha_factura                          factura_fecha,
            fact.id                                     factura_linea_id,
            fact.amount_residual_currency               factura_adeudo,
            fact.parent_state                           factura_status,
            CASE
                WHEN 
                    sm.to_refund='t'
                THEN
                    0
                ELSE
                    fact.price_unit*sml.qty_done
            END                                         factura_subtotal,
            CASE
                WHEN 
                    sm.to_refund='t'
                THEN
                    -sml.qty_done
                ELSE
                    sml.qty_done
            END                                         cantidad,
            CASE
                WHEN 
                    sm.to_refund='t'
                THEN
                    -sml.qty_done*(1/uom.factor)
                ELSE
                    sml.qty_done*(1/uom.factor)
            END                                         litros,
            'Odoo ZK'                                   fuente
        FROM
            sale_order_line sol 
            LEFT JOIN 
            stock_move_line sml on sol.id=sm.sale_line_id
            left JOIN
            stock_move sm 
            ON sml.move_id = sm.id 
            INNER JOIN
            stock_picking sp 
            ON sml.picking_id = sp.id 
            LEFT JOIN
            ON sm.sale_line_id = sol.id 
            AND sp.sale_id = sol.order_id 
            INNER JOIN
            sale_order so 
            ON sol.order_id = so.id 
            LEFT JOIN
            product_product pp 
            ON sml.product_id = pp.id 
            INNER JOIN
            product_template pt 
            ON pp.product_tmpl_id = pt.id 
            INNER JOIN uom_uom uom ON sml.product_uom_id=uom.id
            INNER JOIN uom_uom uom_order ON sol.product_uom=uom_order.id
            LEFT JOIN res_partner rp ON so.partner_id=rp.id
            LEFT JOIN res_partner determinantes ON so.partner_shipping_id=determinantes.id
            LEFT JOIN base_plant bp ON so.plant_id=bp.id
            LEFT JOIN res_partner plantas ON bp.partner_id=plantas.id
            LEFT JOIN res_country geo_pais ON determinantes.country_id=geo_pais.id
            LEFT JOIN res_country_state geo_estado ON determinantes.state_id=geo_estado.id
            /* LEFT JOIN sale_order_line_invoice_rel solir ON sol.id=solir.order_line_id */
            /* LEFT JOIN account_move_line aml ON solir.invoice_line_id=aml.id AND aml.parent_state='posted' */
            LEFT JOIN 
                (SELECT 
                    solir.order_line_id,
                    aml.move_name,
                    aml.move_id,
                    aml.quantity,
                    aml.price_subtotal,
                    aml.price_unit,
                    aml.currency_id,
                    aml.id,
                    aml.parent_state,
                    aml.amount_residual_currency,
                    to_char(aml.create_date at time zone 'UTC-5','YYYY-MM-DD') fecha_factura
                FROM    
                    account_move_line aml
                    INNER JOIN 
                    sale_order_line_invoice_rel solir
                    ON solir.invoice_line_id=aml.id
                WHERE
                    aml.parent_state='posted'
                    AND aml.price_subtotal>0
                ) fact
            ON sol.id=fact.order_line_id
        WHERE 
            sp.state <> 'cancel' 
            AND sm.state='done'
            AND sml.done_move='t' 
            AND sp.date_done at time zone 'UTC-5'>= '$f1 00:00:00' 
            AND sp.date_done at time zone 'UTC-5' <='$f2 23:59:59'
SQL;

    return resultQueryPG($sql);
}
function salesZKOdoo($isSellOut = false)
{
    //Ventas ZK Historico
    $whereSellOut = ($isSellOut) ? "AND rp.id NOT IN ('4004')" : ""; //Raloy

    $f1 = $_GET["f1"];
    $f2 = $_GET["f2"];

    $sql = <<<SQL
        SELECT          
            sml.product_id                              producto_id,
            pp.default_code                             producto_clave,
            LEFT(pp.default_code,2)                     producto_marca,
            RIGHT(LEFT(pp.default_code,4),2)            producto_empaque,
            pt.NAME                                     producto_nombre_padre,
            sm.NAME                                     producto_nombre_variante,
            pt.categ_id                                 producto_categoria_id,
            sml.product_uom_id                          producto_unidad_medida_id,
            uom.name                                    producto_unidad_medida,
            (1/uom.factor)                              producto_cdv,
            pp.id                                       producto_id,
            pt.id                                       producto_template_id,
            sml.picking_id                              picking_id,
            sml.id                                      movimiento_id_linea,
            sml.product_qty                             movimiento_cantidad_original,
            sml.product_uom_qty                         movimiento_cantidad_uom,
            to_char(sml.DATE at time zone 'UTC-5','YYYY-MM-DD')    movimiento_fecha,
            sml.state                                   movimiento_status_linea,
            sml.reference                               movimiento_referencia_linea,
            sml.production_id                           movimiento_production_id,
            sm.id                                       movimiento_id,
            sm.to_refund                                movimiento_es_retorno,
            sp.NAME                                     remision,
            sp.id                                       remision_id,
            so.NAME                                     pedido,
            sp.sale_id                                  pedido_id,
            sol.id                                      pedido_linea_id,
            sol.product_uom                             pedido_unidad_medida_id,
            uom_order.name                              pedido_unidad_medida,
            so.client_order_ref                         pedido_ref_cliente,
            so.plant_id                                 planta_id,
            CONCAT('[',plantas.id,'] ',plantas.name)    planta,
            so.route                                    ruta,
            rp.name                                     cliente,
            rp.id                                       cliente_id,
            CONCAT(
                '[',so.partner_shipping_id,'] ',
                determinantes.name
            )                                           determinante,
            geo_pais.name                               determinante_pais,
            geo_estado.name                             determinante_estado,
            determinantes.city                          determinante_municipio,
            so.partner_shipping_id                      determinante_id,
            fact.move_name                              factura,
            fact.move_id                                factura_id,
            fact.quantity                               factura_cantidad,
            fact.price_unit                             factura_precio_pieza,
            fact.price_unit*uom.factor                  factura_precio_litro,
            fact.currency_id                            factura_moneda,
            fact.fecha_factura                          factura_fecha,
            fact.id                                     factura_linea_id,
            fact.amount_residual_currency               factura_adeudo,
            fact.parent_state                           factura_status,
            CASE
                WHEN 
                    sm.to_refund='t'
                THEN
                    0
                ELSE
                    fact.price_unit*sml.qty_done
            END                                         factura_subtotal,
            CASE
                WHEN 
                    sm.to_refund='t'
                THEN
                    -sml.qty_done
                ELSE
                    sml.qty_done
            END                                         cantidad,
            CASE
                WHEN 
                    sm.to_refund='t'
                THEN
                    -sml.qty_done*(1/uom.factor)
                ELSE
                    sml.qty_done*(1/uom.factor)
            END                                         litros,
            'Odoo ZK'                                   fuente
        FROM
            stock_move_line sml 
            INNER JOIN
            stock_move sm 
            ON sml.move_id = sm.id 
            INNER JOIN
            stock_picking sp 
            ON sml.picking_id = sp.id 
            LEFT JOIN
            sale_order_line sol 
            ON sm.sale_line_id = sol.id 
            AND sp.sale_id = sol.order_id 
            INNER JOIN
            sale_order so 
            ON sol.order_id = so.id 
            LEFT JOIN
            product_product pp 
            ON sml.product_id = pp.id 
            INNER JOIN
            product_template pt 
            ON pp.product_tmpl_id = pt.id 
            INNER JOIN uom_uom uom ON sml.product_uom_id=uom.id
            INNER JOIN uom_uom uom_order ON sol.product_uom=uom_order.id
            LEFT JOIN res_partner rp ON so.partner_id=rp.id
            LEFT JOIN res_partner determinantes ON so.partner_shipping_id=determinantes.id
            LEFT JOIN base_plant bp ON so.plant_id=bp.id
            LEFT JOIN res_partner plantas ON bp.partner_id=plantas.id
            LEFT JOIN res_country geo_pais ON determinantes.country_id=geo_pais.id
            LEFT JOIN res_country_state geo_estado ON determinantes.state_id=geo_estado.id
            /* LEFT JOIN sale_order_line_invoice_rel solir ON sol.id=solir.order_line_id */
            /* LEFT JOIN account_move_line aml ON solir.invoice_line_id=aml.id AND aml.parent_state='posted' */
            LEFT JOIN 
                (SELECT 
                    solir.order_line_id,
                    aml.move_name,
                    aml.move_id,
                    aml.quantity,
                    aml.price_subtotal,
                    aml.price_unit,
                    aml.currency_id,
                    aml.id,
                    aml.parent_state,
                    aml.amount_residual_currency,
                    to_char(aml.create_date at time zone 'UTC-5','YYYY-MM-DD') fecha_factura
                FROM    
                    account_move_line aml
                    INNER JOIN 
                    sale_order_line_invoice_rel solir
                    ON solir.invoice_line_id=aml.id
                WHERE
                    aml.parent_state='posted'
                    AND aml.price_subtotal>0
                ) fact
            ON sol.id=fact.order_line_id
        WHERE 
            sp.state <> 'cancel' 
            AND sm.state='done'
            AND sml.done_move='t' 
            AND sp.date_done at time zone 'UTC-5'>= '$f1 00:00:00' 
            AND sp.date_done at time zone 'UTC-5' <='$f2 23:59:59'
            $whereSellOut
SQL;

    return resultQueryPG($sql);
}

function salesZKOdooJson()
{
    return json_encode(salesZKOdoo());
}
function salesZKSCPJson()
{
    return json_encode(salesZKSCP());
}

function competitividad()
{
    $sql = "SELECT * FROM db";
    return json_encode(resultQueryMariaDB("competitividad", $sql));
}

function streamData()
{
    set_time_limit(0);
    ob_implicit_flush(true);
    ob_end_flush();
    $f1 = $_GET["f1"];
    $f2 = $_GET["f2"];

    $serviceUrl = "https://odoo-bi.raloy.com.mx/services/total_cliente_determinante_adblue.php?from=$f1&to=$f2";

    $result = file_read_json($serviceUrl);
    $totalRecords = count($result["data"]);
    for ($counter = 0; $counter < 10; $counter++) {
        //Hard work!
        sleep(1);
        $processed = ($counter + 1) * 10; //Progress
        $response = array('message' => $processed . '% complete. server time: ' . date("h:i:s", time()) . ' ' . $totalRecords, 'progress' => $processed);
        echo json_encode($response);
    }
    sleep(1);
    $response = array('message' => 'Complete', 'progress' => 100);
    echo json_encode($response);
}

function kardex()
{
    $sql = <<<SQL
    select
        sml.id move_id,
        sl."name" location_name,
        sl.complete_name location_complete_name,
        sl_ref.complete_name location_ref_complete_name,
        pp.id product_id,
        pt.name product_name,
        pp.default_code product_key,
        sm.name move_name,
        sm.reference move_reference,
        sp.origin origin,
        uu."name" uom,
        sml."date" move_date,
        concat(sml."date", '@') move_date_str,
        spl."name" batch,
        smlst.qty quantity,
        smlst.move_type,
        userDone."name" user_done,
        SUM(smlst.qty) over(partition by pp.id order by sml.id) end_inventory
    from
        (
            (
            select
                smls.location_dest_id location_id,
                smls.location_id location_id_ref,
                smls.id smlid,
                smls.product_id,
                smls.lot_id,
                'in' move_type,
                sum(smls.qty_done) qty
            from
                stock_move_line smls
            where
                smls.state = 'done'
            group by
                smls.location_dest_id,
                smls.id,
                smls.product_id,
                smls.lot_id
            )
        union
            (
            select
                smls.location_id location_id,
                smls.location_dest_id location_id_ref,
                smls.id smlid,
                smls.product_id,
                smls.lot_id,
                'out' move_type,
                sum(-smls.qty_done) qty
            from
                stock_move_line smls
            where
                smls.state = 'done'
            group by
                smls.location_id,
                smls.id,
                smls.product_id,
                smls.lot_id
            )
        ) smlst
    left join product_product pp on
            smlst.product_id = pp.id
    left join product_template pt on
            pp.product_tmpl_id = pt.id
    inner join stock_location sl on 
            smlst.location_id = sl.id
    inner join stock_move_line sml on
        smlst.smlid = sml.id
        and sml.product_id = smlst.product_id
    inner join stock_location sl_ref on 
        smlst.location_id_ref = sl_ref.id
    inner join stock_move sm on
        sm.id = sml.move_id
    left join stock_production_lot spl on
        sml.lot_id = spl.id
    left join stock_picking sp on
        sml.picking_id = sp.id
    inner join res_users ru on
        sp.done_user_id = ru.id
    inner join res_partner userDone on
        ru.partner_id = userDone.id
    left join uom_uom uu on
        sml.product_uom_id = uu.id
    where
        sl.active = true
        and sl.usage = 'internal'
    order by
        pt.name,
        sml.id
SQL;
    return json_encode(resultQueryPG($sql));
}

function sale_orders()
{
    $sql = <<<SQL
    select
        sol.name producto_linea,
        sol.invoice_status,
        sol.price_unit,
        sol.price_subtotal ,
        sol.price_tax ,
        sol.price_total,
        sol.price_Reduce,
        sol.price_reduce_taxinc,
        sol.price_reduce_taxexcl,
        sol.discount,
        sol.product_id,
        pt.name producto,
        pp.default_code clave,
        pt.categ_id categoria,
        pt.id ptid,
        pp.id ppid,
        sol.product_uom_qty,
        sol.product_uom uom_id,
        uu.name uom,
        uu.factor cdv,
        sol.qty_delivered,
        sol.qty_delivered_manual,
        sol.qty_invoiced,
        sol.qty_to_invoice,
        sol.untaxed_amount_invoiced,
        sol.untaxed_amount_to_invoice,
        sol.salesman_id,
        sol.currency_id,
        sol.company_id,
        sol.order_partner_id,
        sol.is_expense,
        sol.is_downpayment,
        sol.state,
        sol.customer_lead,
        sol.create_date,
        sol.product_qty_liter,
        so.id soid,
        so.name pedido,
        so.client_order_ref,
        so.reference,
        so.date_order fecha_pedido,
        so.partner_id cliente_id,
        rp.name cliente,
        so.partner_invoice_id,
        rpd.name determinante_s,
        rpd.zip,
        rpd.state_id,
        rcs."name" geo_estado,
        city."name" geo_ciudad,
        country."name" geo_pais,
        rpd.city_id,
        so.partner_shipping_id,
        so.pricelist_id,
        so.invoice_status,
        so.currency_rate,
        so.effective_date,
        so.partner_reference ,
        so.usd_rate,
        so.exceeded_amount,
        so.picking_raloy,
        so.is_raloy,
        so.determinant determinante,
        so.to_logistics,
        so.picking_zarkruse,
        so.route,
        so.date_route,
        so.pending_liters,
        so.liters_routed ruteado,
        sol.product_uom_qty -sol.qty_delivered xentregar
    from
        sale_order_line sol
    inner join sale_order so on
        sol.order_id = so.id
    inner join
        product_product pp on
        sol.product_id = pp.id
    inner join 
        product_template pt on
        pp.product_tmpl_id = pt.id
    inner join uom_uom uu on
        sol.product_uom = uu.id
    inner join res_partner rp on
        so.partner_id = rp.id
    inner join res_partner rpd on
        so.partner_shipping_id = rpd.id
    left join res_country country on
        rpd.country_id = country.id
    left join res_country_state rcs on
        rpd.state_id = rcs.id
    left join res_city city on
        rpd.city_id = city.id
    inner join res_currency rc on
        sol.currency_id = rc.id
    where
        so.state <> 'cancel'
        and so.date_order >= '2022-12-01'
        and so.date_order <= '2022-12-28'
SQL;
    return json_encode(resultQueryPG($sql));
}
