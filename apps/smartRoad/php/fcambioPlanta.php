<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
$ruteados = [];

require_once("../../../php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    echo array("status"=>0,"error"=>"Sesión Expirada");
    return;
}

require_once("../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function get_orders(){
    $sql="SELECT NumRemi,CantiOrden,PedCli,Almacen,Usuario,FechElabo FROM FPedidos where cantiDada=0";
    
}