<?php
set_time_limit(60);
$json = file_get_contents("fuentes.json");
include("../php/conexion.php");

$data = json_decode($json, true);
$respuesta = [];
foreach ($data["pedidos"] as $fuente => $datos) {
    $output = obtenerDatos($fuente, $datos);
    $respuesta[] = array("fuente" => $fuente, "url" => $datos["url"], "respuesta" => $output);
}
$respuesta[] = "Proceso Terminado";

echo json_encode($respuesta);


function obtenerDatos($fuente, $datos)
{

    $datetime = date("Y-m-d H:i:s");
    //get new data from source
    $ip = gethostbyname($datos["url"]);
    $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
    $content = file_get_contents($ip, false, $context);
    $data = json_decode($content, true);
 

    $fechaHrLog = $datetime;


    //Save Data to DB
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        return "Error de conexión";
    }

    $affected = 0;
    $errors = [];

    if(count($data)==0){
        return "TimeOut";
    }
    if (count($data) > 0) {
        $arrColumns = array(
            "soid",
            "pedido",
            "create_date",
            "commitment_date2",
            "id_destino",
            "destino",
            "estado",
            "ciudad",
            "calle",
            "colonia",
            "obs",
            "id_cliente",
            "cliente",
            "cliente_nombre",
            "clave",
            "descripcion",
            "litros",
            "albaran",
            "estado_remision",
            "ventas",
            "fechaHrLog",
            "fuente"
        );
        $columns = implode(",", $arrColumns);

        foreach ($data as $row) {
            $destino = str_replace(array('"', "'"), "", $row["destino"]);
            $values = array(
                "'" . $row["soid"] . "'",
                "'" . $row["pedido"] . "'",
                trim(escape_values($row["create_date"])),
                trim(escape_values($row["commitment_date2"])),
                "'" . $row["id_destino"] . "'",
                "'" . $destino . "'",
                "'" . $row["estado"] . "'",
                "'" . $row["ciudad"] . "'",
                "'" . str_replace(array('"', "'"), "", $row["calle"]) . "'",
                "'" . str_replace(array('"', "'"), "", $row["colonia"]) . "'",
                "'" . str_replace("'", " ", $row["obs"]) . "'",
                "'" . $row["id_cliente"] . "'",
                "'" . $row["cliente"] . "'",
                "'" . str_replace(array('"', "'"), "", $row["cliente_nombre"]) . "'",
                "'" . $row["clave"] . "'",
                "'" . $row["descripcion"] . "'",
                str_replace(",", "", $row["litros"]),
                "'" . $row["albaran"] . "'",
                "'" . $row["estado_remision"] . "'",
                "'" . $row["ventas"] . "'",
                "'" . $fechaHrLog . "'",
                "'".$fuente."'"
            );
            $strvalues = implode(",", $values);
            $lts = str_replace(",", "", $row["litros"]);
            $commitmentDate = trim(escape_values($row["commitment_date2"]));

            //            $queries[] = "INSERT INTO smartRoad_a_logPedidos($columns) VALUES ($strvalues) ON DUPLICATE KEY UPDATE litros='$lts', commitment_date2=$commitmentDate,destino='$destino'";
            $query = "INSERT INTO smartRoad_a_logPedidos($columns) VALUES ($strvalues) ON DUPLICATE KEY UPDATE litros='$lts', commitment_date2=$commitmentDate,destino='$destino'";
            $mysqli->query($query);
            if ($mysqli->errno) {
                $errors[] = "Insert Error " . $mysqli->error;
            } else {
                $affected += $mysqli->affected_rows;
            }
        }
    }

    if (count($errors) == 0) {
        $queryUpdates = "INSERT INTO smartRoad_a_logUpdates(fechahora,fuente,cron,affected_rows) VALUES('$datetime','$fuente','consolidaFuentes.php',$affected)";

        if ($mysqli->query($queryUpdates)) {
            $mysqli->close();
            return array("mensaje"=>"Fuente $fuente Actualizada $affected Filas","datos"=>$data);
        } else {
            $mysqli->close();
            return "Log error " . $mysqli->error;
        }
    } else {
        $mysqli->close();
        return $errors;
    }

    agregaDeterminantes($mysqli);
}
function agregaDeterminantes($mysqli)
{
    $query = <<<SQL
    INSERT INTO smartRoad_stdDet (
        cliente,
        cliente_nombre,
        destino,
        ciudad,
        estado,
        calle,
        colonia,
        id_det_origen,
        fuenteDatos
    ) 
    SELECT 
        t.id_cliente,
        t.cliente_nombre,
        t.destino,
        t.ciudad,
        t.estado,
        t.calle,
        t.colonia,
        t.id_destino,
        fuente
    FROM
        smartRoad_a_logPedidos t 
    WHERE NOT ISNULL(t.id_destino) 
    GROUP BY t.id_destino 
        ON DUPLICATE KEY UPDATE 
            cliente = VALUES(cliente),
            calle = VALUES(calle),
            colonia = VALUES(colonia)        
SQL;

    if ($mysqli->query($query)) {
        echo "Determinantes Ok";
    } else {
        echo "Error determinantes";
    }
}

function escape_values($v)
{
    if (is_numeric($v)) {
        $str = "'" . $v . "'";
    } else {
        $v = str_replace("'", " ", $v);
        $pos = strpos($v, "/");
        if ($pos == true) {
            $datePart = substr($v, 0, 10);
            if (strlen($v) > 10) {
                $timePart = substr($v, -8, 8);
            } else {
                $timePart = "";
            }

            $y = substr($datePart, -4, 4);
            $m = substr($datePart, -7, 2);
            $d = substr($datePart, -10, 2);
            $v = $y . "-" . $m . "-" . $d . " " . $timePart;
        }
        $str = "'" . $v . "'";
    }
    return $str;
}
