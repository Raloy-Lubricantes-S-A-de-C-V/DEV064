<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function allData() {
     $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $folio=$_GET["folio"];
    $queryGral=<<<SQL
    SELECT 
        e.placas,
        e.fecha_carga fechaCarga,
        e.planta_carga plantaCarga,
        e.fecha_regreso fechaRegreso,
        e.planta_regreso plantaRegreso,
        u.nombre usuario,
        e.fechahoraValAMP,
        e.selloAlmacenCliente selloAMP,
        e.litros ltsTot
    FROM
        smartRoad_entregas e 
        INNER JOIN
        framework_usuarios u 
        ON e.usuarioValAMP = u.id_usuario 
    WHERE id_entrega = $folio 
        AND e.validacionAMP = 1 
        AND NOT ISNULL(e.selloAlmacenCliente)           
SQL;
    $resultx=$mysqli->query($queryGral);
    if($resultx->num_rows<=0){
        $respuesta["status"]=0;
        $respuesta["error"]="El folio es inválido";
        return json_encode($respuesta);
    }
    $row=$resultx->fetch_assoc();
    $respuesta["grales"]["placas"]=utf8_encode($row["placas"]);
    $respuesta["grales"]["fechaCarga"]=utf8_encode($row["fechaCarga"]);
    $respuesta["grales"]["plantaCarga"]=utf8_encode($row["plantaCarga"]);
    $respuesta["grales"]["fechaRegreso"]=utf8_encode($row["fechaRegreso"]);
    $respuesta["grales"]["plantaRegreso"]=utf8_encode($row["plantaRegreso"]);
    $respuesta["grales"]["usuario"]=utf8_encode($row["usuario"]);
    $respuesta["grales"]["fechahoraValAMP"]=utf8_encode($row["fechahoraValAMP"]);
    $respuesta["grales"]["selloAMP"]=utf8_encode($row["selloAMP"]);
    $respuesta["grales"]["ltsTot"]=number_format($row["ltsTot"]);
    $queryDetalles = <<<SQL
        SELECT 
    r.pedido,
    CONCAT(r.cveCliente, ' ', r.cliente) cliente,
    r.determinante,
    CONCAT(em.mpio,', ', em.edoCor) destino,
    CONCAT(
        r.cveProducto,
        ' ',
        r.nombreProducto
    ) producto,
    r.eta,
    CONCAT(
        r.usuario_modif,
        ' ',
        r.fechaHr_modif
    ) ruteo,
    r.loteZK,
    CONCAT(
        'Dens: ',
        r.densidad,
        ', Conc:',
        r.concentracion
    ) coa,
    r.ltsSurtir,
    remisionZK remision,
    occliente,
    cveclientezk
FROM
    smartRoad_pre_ruteo r 
    LEFT JOIN
    smartRoad_stdEdosMpios em 
    ON r.id_edoMpio = em.id 
WHERE id_entrega = $folio AND r.fuente_pedido="Odoo Raloy"
SQL;
    $resultx->free();
    
    if ($result = $mysqli->query($queryDetalles)) {
        $tbody = "";
        $remisiones=[];
        $sumaRems=[];
        while ($row = $result->fetch_assoc()) {
            $tbody.="<tr>"
                    . "<td>".$row["pedido"]."</td>"
                    . "<td>".utf8_encode($row["cliente"])."</td>"
                    . "<td>".utf8_encode($row["determinante"])."</td>"
                    . "<td>".utf8_encode($row["destino"])."</td>"
                    . "<td>".utf8_encode($row["producto"])."</td>"
                    . "<td>".$row["eta"]."</td>"
                    . "<td>".utf8_encode($row["ruteo"])."</td>"
                    . "<td>".str_replace("-", "", $row["loteZK"])."</td>"
                    . "<td>".$row["coa"]."</td>"
                    . "<td>".number_format($row["ltsSurtir"],2)."</td>"
                    . "</tr>";
            $sumaRems[$row["remision"]]=array_key_exists($row["remision"],$sumaRems)?$sumaRems[$row["remision"]]+$row["ltsSurtir"]:$row["ltsSurtir"];
            $remisiones[]=$row["remision"];
            $ocs[$row["remision"]]=$row["occliente"];
        }
        $remisiones=array_unique($remisiones);
        $respuesta["remisiones"]="";
        foreach($remisiones as $remision){
            $respuesta["remisiones"].="<tr>"
                    . "<td>$remision</td>"
                    . "<td>".$ocs[$remision]."</td>"
                    . "<td>".number_format($sumaRems[$remision],2)."</td>"
                    . "</tr>";
        }
        $respuesta["status"]=1;
        $respuesta["error"]="";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        exit(json_encode($respuesta));
    }
    $mysqli->close();
    $respuesta["detalles"]["tbody"] = $tbody;
    return json_encode($respuesta);
}