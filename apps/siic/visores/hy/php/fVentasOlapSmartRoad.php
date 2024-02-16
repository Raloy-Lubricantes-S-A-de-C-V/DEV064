<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("../../../../../php/conexion.php");
$fase = "";
$fase = $_GET["fase"];
echo call_user_func($fase);
function ventasGranelSmartRoad()
{
    $fec1 = $_GET["f1"];
    $fec2 = $_GET["f2"];
    $query = <<<SQL
    SELECT 
        DATE_FORMAT(e.fecha_carga, "%Y-%m-%d") fecha_carga,
        DATE_FORMAT(pr.eta, "%Y-%m-%d") fecha_entrega,
        DATE_FORMAT(pr.fechaCompromiso, "%Y-%m-%d") fecha_compromiso,
        DATE_FORMAT(pr.fechaPedido, "%Y-%m-%d") fecha_pedido,
        (WEEK(e.fecha_carga,5) - WEEK(DATE_SUB(e.fecha_carga, INTERVAL DAYOFMONTH(e.fecha_carga)-1 DAY)))+1 week_of_month_carga,
        e.planta_carga,
        pr.cliente cliente_nombre,
        pr.determinante cliente_determinante,
        em.edoCor ubic_edoCor,
        em.mpio ubic_mpio,
        em.locfor logistica_locfor,
        em.planta2021 logistica_planta_A,
        em.zona logistica_ruta,
        SUM(pr.ltsSurtir) litros 
    FROM
        smartRoad_pre_ruteo pr 
        INNER JOIN
        smartRoad_entregas e 
        ON pr.id_entrega = e.id_entrega 
        LEFT JOIN
        smartRoad_stdEdosMpios em 
        ON pr.id_edoMpio = em.id 
    WHERE e.fecha_carga >= "$fec1" and e.fecha_carga<="$fec2"
    GROUP BY pr.id_pre_ruteo,pr.id_entrega
SQL;

    $respuesta["status"] = 0;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"][] = $row;
        }
        $respuesta["status"] = 1;
    } else {
        $respuesta["error"] = $mysqli->error;
    };
    return json_encode($respuesta);
}