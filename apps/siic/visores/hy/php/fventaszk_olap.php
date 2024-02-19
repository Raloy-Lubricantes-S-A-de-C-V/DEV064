<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
$check = session_check($_GET["t"]);

if ($check != 1) {
    echo json_encode(array("status" => "0", "error" => "Session: " . $check));
    return;
}

$fase = "";
$fase = $_GET["fase"];
if ($fase == "getDataMontos") {
    $fieldCurr = ",SUM(totalSinIVA*((100-DescTot)/100)) monto,SUM(totalSinIVA*((100-DescTot)/100)*RTipoCambio) MXN,r.Divisa Moneda";
    // $fieldCurrNC = ",SUM(-r.Precio * Cantidad) monto,SUM(-r.Precio * Cantidad * RTipoCambio) MXN,rd.Divisa Moneda ";
    $fieldCurrNC = ",0 monto,0 MXN,'' Moneda ";
    $groupbyCurr = ",Moneda";
} else {
    $fieldCurr = " ";
    $fieldCurrNC = " ";
    $groupbyCurr = "";
}
$response = getData($fieldCurr, $fieldCurrNC, $groupbyCurr);
echo $response;
function getWarehouses()
{
    $respuesta = [];
    $dataconn = dataconn("intranet");
    $sql = <<<SQL
    SELECT AlmacenR,siic_ventas FROM smartRoad_plantas
SQL;
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_assoc()) {
        $respuesta[$row["AlmacenR"]] = $row["siic_ventas"];
    }
    return $respuesta;
}

function getData($fieldCurr, $fieldCurrNC, $groupbyCurr)
{
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
            CONCAT(r.Producto, ' ', r.Acabado) cve,
            pt.PTDesc producto,
            SUM(CantiDada) Pzas,
            CDV,
            GROUP_CONCAT(r.numRemi) docto,
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
        GROUP BY tipotransaccion,fecha,
                planta,
                pt.Marca,
                pt.Marca2,
                cve,
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
            CONCAT(r.Producto, ' ', r.Acabado) cve,
            pt.PTDesc producto,
            SUM(- Cantidad) Pzas,
            CDV,
            GROUP_CONCAT(r.Remision) docto,
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
            cve,
            pt.PTDesc,
            c.CveCliente,
            determinante,
            CDV $groupbyCurr
SQL;
    $respuesta["status"] = 0;
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($query)) {
        $arrWhs = getWarehouses();
        while ($row = $result->fetch_assoc()) {
            $array = [];
            foreach ($row as $key => $value) {
                if ($key == "planta") {
                    $value = (count($arrWhs) > 0) ? $arrWhs[$value] : $value;
                }
                $array[$key] = utf8_encode($value);
            }
            $respuesta["jsondata"][] = $array;
        }
        $respuesta["status"] = 1;
    } else {
        $respuesta["error"] = $mysqli->error;
    };
    return json_encode($respuesta);
}

function mrpData($fieldCurr, $fieldCurrNC, $groupbyCurr)
{
    $fec1 = $_GET["f1"];
    $fec2 = $_GET["f2"];
    $query = <<<SQL
SELECT 
    boms.cve ptPadre_cve,
    boms.PTDesc ptPadre_descr,
    boms.CDV ptPadre_cdv,
    boms.PTCatalogo ptPadre_familia,
    boms.PTUniMedida ptPadre_unidad,
    boms.PTEnsamble ptmpHijo_cve,
    boms.chdescr ptmpHijo_descr,
    boms.cantidad ptmpHijo_req,
    boms.chunimed ptmpHijo_unidad,
    ventas.planta,
    ventas.litrosQ/3 avgM_lts,
    ventas.pzasQ/3 avgM_pzas,
    boms.cantidad*ventas.pzasQ/3 reqxmes,
 (boms.cantidad*ventas.pzasQ/3)/2 min_15d,
 ((boms.cantidad*ventas.pzasQ/3)/30)*23 pr_23d,
 (boms.cantidad*ventas.pzasQ/3) max_30d
FROM
    (SELECT 
        CONCAT(pt.PTNumArticulo, " ", pt.PTTipo) cve,
        pt.CDV,
        pt.PTCatalogo,
        pt.PTDesc,
        pt.PTUniMedida,
        bom.PTEnsamble,
        bom.Cantidad,
        bom.PToMP,
        IF(
            bom.PToMP = 0,
            ptbom.PTDesc,
            mpbom.MPDesc
        ) chdescr,
        IF(
            bom.PToMP = 0,
            ptbom.PTUniMedida,
            mpbom.MPUniMedida
        ) chunimed 
    FROM
        InvProdTerm pt 
        INNER JOIN
        FListaEnsamble bom 
        ON pt.PTNumArticulo = bom.CodPT 
        AND pt.PTTipo = bom.AcabaEnsam 
        LEFT JOIN
        InvProdTerm ptbom 
        ON bom.PTEnsamble = ptbom.PTNumArticulo 
        LEFT JOIN
        InvMatPrima AS mpbom 
        ON bom.PTEnsamble = mpbom.MPNumArticulo) boms 
    INNER JOIN
    (SELECT 
        planta,
        cve,
        SUM(pzas) pzasQ,
        SUM(litros) litrosQ 
    FROM
        (SELECT 
            "ventas" AS tipotransaccion,
            DATE_FORMAT(r.FechElabo, '%Y-%m-%d') fecha,
            COALESCE(
                IF(
                    AlmacenR = "",
                    "1 STG",
                    IF(
                        AlmacenR = "GDL",
                        "2 GDL",
                        IF(AlmacenR = "MTY", "3 MTY", IF(AlmacenR = "SAL", "4 SAL", AlmacenR))
                    )
                ),
                "Subtotal Planta"
            ) planta,
            CONCAT(c.CveCliente, ' ', c.NomCliente) cliente,
            IFNULL(
                CONCAT(e.Determinante, ' ', e.Nombre),
                ''
            ) determinante,
            IFNULL(e.ciudad, c.CiudadCliente) mpio,
            IFNULL(e.Estado, c.Estado) edo,
            IFNULL(e.Pais, c.PaisCliente) paisscp,
            pt.Marca Imagen,
            pt.Marca2 Presentacion,
            CONCAT(r.Producto, ' ', r.Acabado) cve,
            pt.PTDesc producto,
            SUM(CantiDada) Pzas,
            CDV,
            GROUP_CONCAT(r.numRemi) docto,
            SUM(CantiDada * CDV) litros,
            IF(
                c.NomCliente LIKE "%Raloy%",
                "Raloy",
                "ZK"
            ) fuente 
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
            ON c.CveCliente = e.Cliente 
            AND e.Determinante = r.Enviado 
        WHERE r.FechElabo >= "2020-04-01" 
            AND r.FechElabo <= "2020-06-30" 
            AND r.STATUS <> "C" 
            AND PTCatalogo IN ("SKYBLUE") 
        GROUP BY tipotransaccion,
            fecha,
            planta,
            pt.Marca,
            pt.Marca2,
            cve,
            pt.PTDesc,
            c.CveCliente,
            e.Determinante,
            CDV 
            UNION
            SELECT 
                "Devoluciones" AS tipotransaccion,
                DATE_FORMAT(r.Fecha, '%Y-%m-%d') fecha,
                COALESCE(
                    IF(
                        AlmacenR = "",
                        "1 STG",
                        IF(
                            AlmacenR = "GDL",
                            "2 GDL",
                            IF(AlmacenR = "MTY", "3 MTY", IF(AlmacenR = "SAL", "4 SAL", AlmacenR))
                        )
                    ),
                    "Subtotal Planta"
                ) planta,
                CONCAT(c.CveCliente, ' ', c.NomCliente) cliente,
                IFNULL(
                    CONCAT(e.Determinante, ' ', e.Nombre),
                    ''
                ) determinante,
                IFNULL(e.ciudad, '') mpio,
                IFNULL(e.Estado, '') edo,
                IFNULL(e.Pais, '') paisscp,
                pt.Marca Imagen,
                pt.Marca2 Presentacion,
                CONCAT(r.Producto, ' ', r.Acabado) cve,
                pt.PTDesc producto,
                SUM(- Cantidad) Pzas,
                CDV,
                GROUP_CONCAT(r.Remision) docto,
                SUM(- Cantidad * CDV) litros,
                IF(
                    c.NomCliente LIKE "%Raloy%",
                    "Raloy",
                    "ZK"
                ) fuente 
            FROM
                FDevolCliente r 
                INNER JOIN
                InvProdTerm pt 
                ON r.Producto = pt.PTNumArticulo 
                AND r.Acabado = pt.PTTipo 
                INNER JOIN
                FClientes c 
                ON r.Cliente = c.CveCliente 
                LEFT JOIN
                FRemision rd 
                ON r.Remision = rd.numRemi 
                LEFT JOIN
                FClienteEnvio e 
                ON c.CveCliente = e.Cliente 
                AND e.Determinante = rd.Enviado 
            WHERE r.Fecha >= "2020-04-01" 
                AND r.Fecha <= "2020-06-30" 
                AND PTCatalogo IN ("SKYBLUE") 
            GROUP BY tipotransaccion,
                fecha,
                planta,
                pt.Marca,
                pt.Marca2,
                cve,
                pt.PTDesc,
                c.CveCliente,
                e.Determinante,
                CDV) vyd 
        GROUP BY planta,
            cve) ventas 
        ON boms.cve = ventas.cve 
SQL;

    $respuesta["status"] = 0;
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $array = [];
            foreach ($row as $key => $value) {
                $array[$key] = utf8_encode($value);
            }
            $respuesta["jsondata"][] = $array;
        }
        $respuesta["status"] = 1;
    } else {
        $respuesta["error"] = $mysqli->error;
    };
    return json_encode($respuesta);
}
