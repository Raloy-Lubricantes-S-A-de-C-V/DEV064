<?php

//header('Content-Type: text/event-stream');
header('Content-Type: text/event-stream, charset=UTF-8');
header('Cache-Control: no-cache');
date_default_timezone_set('America/Mexico_City');

//Framework
require_once ("../../../php/conexion.php");
echo "data:" . dimeCargas() . PHP_EOL;
echo PHP_EOL;
echo "retry: 36000000\n";
ob_flush();
flush();

function dimeCargas() {
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $query = <<<SQL
      SELECT 
        c.id_entrega folio,
        GROUP_CONCAT(DISTINCT descr.statCarga) statCarga
      FROM
        smartRoad_entregas c 
        INNER JOIN
        (SELECT 
          r.id_entrega,
          GROUP_CONCAT(IFNULL(statusCargaZK,0)) statCarga 
        FROM
          smartRoad_pre_ruteo r 
          INNER JOIN
          smartRoad_stdEdosMpios em 
          ON r.id_edoMpio = em.id 
        WHERE r.id_entrega > 0 
        GROUP BY r.id_entrega,
          em.id,
          r.eta 
        ORDER BY r.eta,
          eta) descr 
        ON c.id_entrega = descr.id_entrega 
      WHERE c.STATUS IN ("carga") 
      GROUP BY c.id_entrega   ORDER BY c.STATUS,c.id_entrega DESC
SQL;
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $arrStatus = explode(",", $row["statCarga"]);
            if (in_array(0, $arrStatus)) {
                if (in_array(1, $arrStatus)) {
                    $colorClass = "cargaIncompleta"; //incompletos - amarillo
                    $clase = "";
                } else {
                    $colorClass = "cargaNoIniciada"; //Sin datos - rojo
                    $clase = "";
                }
            } else {
                $colorClass = "cargaTerminada"; //completos-green
                $clase = "ponerEnCamino";
            }
            $respuesta["datos"][] = array("folio"=>$row["folio"],"colorClass" => $colorClass,"clase"=>$clase);
        }
         $respuesta["status"] = 1;
         $respuesta["error"] = $mysqli->error;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $respuesta["datos"] = "";
        return json_encode($respuesta);
    }
    return json_encode($respuesta);
}

?>
