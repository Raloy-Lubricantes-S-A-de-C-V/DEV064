<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("../../../../../php/conexion.php");
$fase = "";
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function getData() {
    $fec1=$_GET["f1"];
    $fec2=$_GET["f2"];
    $query = <<<SQL
            SELECT 
                f.propia_externa,
                pr.albaran,
                pr.apariencia,
                pr.causa_cambio_fecha,
                pr.cliente,
                pr.concentracion,
                pr.cveclientezk,
                pr.cveProducto,
                pr.cveCliente,
                pr.densidad,
                pr.determinante,
                pr.eta,
                pr.fechaCompromiso,
                pr.fechaHoraCertificado,
                pr.fechaPedido,
                pr.id_determinanteSmartRoad,
                pr.id_det_origen id_determinante,
                pr.id_edoMpio,
                pr.id_pre_ruteo,
                pr.loteEPT,
                pr.loteZK,
                pr.ltsPedido,
                pr.ltsSurtir,
                pr.nombreProducto,
                pr.numCamion,
                pr.occliente,
                pr.pedido,
                pr.remisionZK,
                pr.sellosDescarga,
                pr.sellosEscotilla,
                pr.soid,
                pr.STATUS,
                pr.ventas,
                c.totalFijos / ltsTotFolio.lts cfu,
                (c.totalFijos / ltsTotFolio.lts) * pr.ltsSurtir costoFijo,
                (
                    c.totalVariables / ltsTotFolio.lts
                ) cvu,
                (
                    c.totalVariables / ltsTotFolio.lts
                ) * pr.ltsSurtir costoVariable,
                (c.costototal / ltsTotFolio.lts) ctu,
                (c.costototal / ltsTotFolio.lts) * pr.ltsSurtir costoEntrega,
                e.placas,
                e.planta_carga,
                e.planta_regreso,
                e.densidad,
                e.concentracion,
                e.fechahoraValAMP,
                e.usuarioCierre,
                e.fecha_carga,
                e.fecha_regreso,
                e.fechaSolicitud,
                e.litros,
                e.responsableCarga,
                em.edoCor Estado,
                em.mpio Municipio 
            FROM
                smartRoad_pre_ruteo pr 
                INNER JOIN
                smartRoad_costeo c 
                ON pr.id_entrega = c.id_entrega 
                INNER JOIN
                smartRoad_stdEdosMpios em 
                ON pr.id_edoMpio = em.id 
                INNER JOIN
                smartRoad_entregas e 
                ON pr.id_entrega = e.id_entrega 
                INNER JOIN
                smartRoad_flota f 
                ON e.placas = f.placas 
                INNER JOIN
                (SELECT 
                    id_entrega,
                    SUM(ltsSurtir) lts 
                FROM
                    smartRoad_pre_ruteo 
                GROUP BY id_entrega) ltsTotFolio 
                ON ltsTotFolio.id_entrega = e.id_entrega 
            WHERE e.STATUS = "Terminado" 
                AND pr.STATUS = "Terminado" 
                AND e.fecha_carga >= "$fec1" 
                AND e.fecha_carga <= "$fec2"   
SQL;

    $respuesta["status"] = 0;
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $array=[];
            foreach($row as $key=>$value){
                $array[$key]=utf8_encode($value);
            }
            $respuesta["jsondata"][] = $array;
        }
        $respuesta["status"] = 1;
    };
    return json_encode($respuesta);
}
function getData2() {
    $fec1=$_GET["f1"];
    $fec2=$_GET["f2"];
    $query = <<<SQL
SELECT 
    f.propia_externa,
    pr.albaran,
    pr.apariencia,
    pr.causa_cambio_fecha,
    pr.cliente,
    pr.concentracion,
    pr.cveclientezk,
    pr.cveProducto,
    pr.cveCliente,
    pr.densidad,
    pr.determinante,
    pr.eta,
    pr.fechaCompromiso,
    pr.fechaHoraCertificado,
    pr.fechaPedido,
    pr.id_determinanteSmartRoad,
    pr.id_det_origen id_determinante,
    pr.id_edoMpio,
    pr.id_pre_ruteo,
    pr.loteEPT,
    pr.loteZK,
    pr.ltsPedido,
    pr.ltsSurtir,
    pr.nombreProducto,
    pr.numCamion,
    pr.occliente,
    pr.pedido,
    pr.remisionZK,
    pr.sellosDescarga,
    pr.sellosEscotilla,
    pr.soid,
    pr.STATUS,
    pr.ventas,
    c.*,
    e.placas,
    e.planta_carga,
    e.planta_regreso,
    e.densidad,
    e.concentracion,
    e.fechahoraValAMP,
    e.usuarioCierre,
    e.fecha_carga,
    e.fecha_regreso,
    e.fechaSolicitud,
    e.litros,
    e.responsableCarga,
    em.edoCor Estado,
    em.mpio Municipio
FROM
    smartRoad_pre_ruteo pr 
    INNER JOIN
    smartRoad_costeo c 
    ON pr.id_entrega = c.id_entrega 
    INNER JOIN
    smartRoad_stdEdosMpios em 
    ON pr.id_edoMpio = em.id 
    INNER JOIN
    smartRoad_entregas e 
    ON pr.id_entrega = e.id_entrega 
    INNER JOIN
    smartRoad_flota f 
    ON e.placas = f.placas 
WHERE e.STATUS = "Terminado" 
    AND pr.STATUS = "Terminado" 
    AND e.fecha_carga >= "$fec1" 
    AND e.fecha_carga <= "$fec2" ;   
SQL;

    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $array=[];
            foreach($row as $key=>$value){
                $array[$key]=utf8_encode($value);
            }
            $data[] = $array;
        }
        $respuesta["dataSource"]["data"]=$data;
        $respuesta["slice"]["rows"]=array("uniqueName"=>"id_entrega");
        $respuesta["slice"]["columns"]=array("uniqueName"=>"Measures");
        $respuesta["slice"]["Measures"]=array("uniqueName"=>"LtsSurtir","aggregation"=>"sum","format"=>"3hc1hq2k");
        $respuesta["slice"]["sorting"]=array("type"=>"desc","tuple"=>array(),"measure"=>"LtsSurtir");
    };
    return json_encode($respuesta);
}