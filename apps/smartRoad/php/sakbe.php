<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
require_once("../../../php/conexion.php");

$ruteados = [];

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["sessionInfo"]) || !in_array(2, $_SESSION["sessionInfo"]["idsModulos"])) {
    echo "Forbbiden";
} else {
    $fase = $_GET["fase"];
    $response = call_user_func($fase);
    echo $response;
}

function dimeDestinosEntrega(){
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $id_entrega=$_GET["id_entrega"];
    $query = <<<SQL
       SELECT e.planta_carga origen,e.planta_regreso destino,em.mpio,em.edoMed,sum(ltsSurtir) lts from smartRoad_pre_ruteo pr INNER JOIN smartRoad_stdEdosMpios em on pr.id_edoMpio=em.id INNER JOIN smartRoad_entregas e on pr.id_entrega=e.id_entrega WHERE pr.id_entrega=$id_entrega GROUP BY pr.id_edoMpio;
SQL;
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $tabla="";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $origen=utf8_encode($row["origen"]);
            $destino=utf8_encode($row["destino"]);
            $tabla.= "<tr class='ruteable'><td>".number_format($row["lts"],2)."</td><td class='mpio'>".utf8_encode($row["mpio"])."</td><td class='edo'>".utf8_encode($row["edoMed"])."</td><td class='iddestino'></td></tr>";
        }
        if($origen=="STG"){
            $tablatosend="<tr class='ruteable'><td></td><td class='mpio'>Tianguistenco</td><td class='edo'>Estado de Mexico</td><td class='iddestino'></td></tr>";
        }
        $tablatosend.=$tabla;
        if($destino=="STG"){
            $tablatosend.="<tr class='ruteable'><td></td><td class='mpio'>Tianguistenco</td><td class='edo'>Estado de Mexico</td><td class='iddestino'></td></tr>";
        }
        $respuesta["tabla"]=$tablatosend;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}


function reportetodo(){
    $query="SELECT pr.*,c.*,pr.ltsSurtir/c.ltsentrega porcentaje,c.costototal*(pr.ltsSurtir/c.ltsentrega) costodestino,(c.costototal*(pr.ltsSurtir/c.ltsentrega))/pr.ltsSurtir costoudestino FROM smartRoad_pre_ruteo pr INNER JOIN smartRoad_costeo c ON pr.id_entrega=c.id_entrega";
}