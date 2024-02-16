<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT']."/intranet/php/session_check.php");
$check=session_check($_GET["t"]);
if ($check != 1) {
    echo json_encode(array("status"=>"0","error"=>"Sesión expirada"));
    return;
}

require_once("../../../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function getData()
{
    $respuesta["status"]=1;
    $respuesta["error"]="";
    $query = <<<SQL
        SELECT 
            da.Cliente,
            c.NomCliente,
            pt.Marca,
            pt.Marca2 presentacion,
            da.CodArtPT,
            da.Acabado,
            pt.PTDesc Producto,
            da.Descuento,
            da.Monto,
            c.CiudadEnvio Moneda,
            da.DescPedio,
            da.MontoPedido 
        FROM
            FCliDesArt da 
            LEFT JOIN
            FClientes c 
            ON da.Cliente = c.CveCliente 
            LEFT JOIN
            InvProdTerm pt 
            ON da.CodArtPT = pt.PTNumArticulo 
            AND da.Acabado = pt.PTTipo 
        ORDER BY c.NomCliente,
            pt.PTDesc    
SQL;

    $dataconn = dataconn("scpzar");
    
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if($mysqli->connect_errno){
        $respuesta["status"]=0;
        $respuesta["error"]=$mysqli->connect_error;
        return json_encode($respuesta);
    }
    
    $mysqli->set_charset("utf8");
    $result = $mysqli->query($query);

    if($mysqli->errno){
        $respuesta["status"]=0;
        $respuesta["error"]=$mysqli->error;
        return json_encode($respuesta);
    }
    
    $respuesta["data"]=[];
    
    while ($row = $result->fetch_assoc()) {
        $respuesta["data"][]=$row;
    }
    $mysqli->close();

    return json_encode($respuesta);
}
