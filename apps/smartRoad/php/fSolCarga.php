<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once("../../../php/conexion.php");
$fase = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fase = $_POST["fase"];
} else {
    $fase = $_GET["fase"];
}
$response = call_user_func($fase);
echo $response;

function cargaDatos() {
    $folio = $_GET["folio"];
    $query = <<<SQL
  SELECT 
    c.usuario solicitante,
    c.placas,
    f.capacidad capac,
    c.fecha_carga,
    c.fecha_regreso,
    c.planta_carga,
    c.planta_regreso,
    c.obs,
    c.fechaSolicitud,
    c.loteZK,
    c.remisionZK,
    c.sellosEscotilla,
    c.sellosDescarga,
    c.numEnvioRaloy,
    c.pesoNeto,
    c.responsableCarga,
    c.densidad,
    c.concentracion conc,
    c.apariencia
  FROM
    smartRoad_entregas c 
    INNER JOIN
    smartRoad_flota f 
    ON c.placas = f.placas
   WHERE c.id_entrega=$folio
SQL;
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    if ($result = $mysqli->query($query)) {
        //CON DATOS
        if ($result->num_rows > 0) {
            $respuesta["status"] = 1;
            $row = $result->fetch_assoc();
            $array = [];
            $array["solicitante"] = utf8_encode($row["solicitante"]);
            $array["placas"] = utf8_encode($row["placas"]);
            $array["capac"] = $row["capac"];
            $array["fecha_carga"] = $row["fecha_carga"];
            $array["fecha_regreso"] = $row["fecha_regreso"];
            $array["planta_carga"] = utf8_encode($row["planta_carga"]);
            $array["planta_regreso"] = utf8_encode($row["planta_regreso"]);
            $array["obs"] = utf8_encode($row["obs"]);
            $array["fechaSolicitud"] = $row["fechaSolicitud"];
            $array["loteZK"] = utf8_encode($row["loteZK"]);
            $array["remisionZK"] = utf8_encode($row["remisionZK"]);
            $array["sellosEscotilla"] = $row["sellosEscotilla"];
            $array["sellosDescarga"] = $row["sellosDescarga"];
            $array["numEnvioRaloy"] = $row["numEnvioRaloy"];
            $array["pesoNeto"] = $row["pesoNeto"];
            $array["responsableCarga"] = $row["responsableCarga"];
            $array["densidad"] = $row["densidad"];
            $array["concentracion"] = $row["conc"];
            $array["apariencia"] = $row["apariencia"];
            $respuesta["data"] = $array;
            $capacidad = $respuesta["data"]["capac"];
        } else {
            $respuesta["status"] = 2;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }


    //detalles*******

    $query = <<<SQL
    SELECT 
        r.id_entrega,
        r.eta fechEntr,
        r.cveCliente,
        r.determinante,
        r.cliente cliente,
        r.pedido ped,
        em.mpio mpio,
        em.edoCor edo,
        r.cveProducto,
        nombreProducto prod,
        SUM(r.ltsSurtir) lts 
      FROM
        smartRoad_pre_ruteo r 
        INNER JOIN
        smartRoad_stdEdosMpios em 
        ON r.id_edoMpio = em.id 
      WHERE r.id_entrega = $folio 
      GROUP BY r.id_entrega,
        r.eta, 
        r.cveCliente,
        r.determinante,
        r.cliente,
        r.pedido,
        em.edoCor,
        em.mpio,
        r.cveProducto,
        r.nombreProducto
      ORDER BY r.eta,em.edoCor,em.mpio,r.cveProducto,lts desc
SQL;
    if ($result = $mysqli->query($query)) {
        //CON DATOS
        if ($result->num_rows > 0) {
            $respuesta["status"] = 1;
            $suma = 0;
            $tableDetalles = "";
            while ($row = $result->fetch_assoc()) {
                $tableDetalles.="<tr><td>" . $row["cveProducto"] . " " . $row["prod"] . "</td><td class='numeric'>" . number_format($row["lts"]) . "</td><td>" . $row["cveCliente"] . " " . utf8_encode($row["cliente"]) . " " . utf8_encode($row["determinante"]) . "</td><td>" . utf8_encode($row["mpio"]) . ", " . utf8_encode($row["edo"]) . "</td><td>" . $row["fechEntr"] . "</td><td>" . $row["ped"] . "</td></tr>";
                $tableDetalles.="</tr>";
                $suma+=$row["lts"];
            }
            $respuesta["data"]["tablaDatos"] = $tableDetalles;
            $respuesta["data"]["utilizUnid"] = number_format(($suma / $capacidad) * 100, 2) . " %";
            $respuesta["data"]["totalLts"] = number_format($suma);
            $respuesta["data"]["capac"] = number_format($respuesta["data"]["capac"]) . " L";
            $respuesta["data"]["pesoNeto"] = number_format($respuesta["data"]["pesoNeto"], 2) . " Kg";
        } else {
            $respuesta["status"] = 2;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function sendMail() {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = 2;
    $mail->Host = "smtp.uservers.net";
    $mail->Port = 587;
//    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = "logistica.raloy@raloycrm.com.mx";
    $mail->Password = "Logistica.2018";
    $mail->setFrom('logistica.raloy@raloycrm.com.mx', 'Logística SkyBlue');
    $mail->Subject = "Solicitud de Carga SkyBlue";
    $toArr = explode(",", $_POST["address"]);
    foreach ($toArr as $to) {
        $mail->AddAddress($to);
    }
    $mail->AddEmbeddedImage('img/RaloyHorizontalPNG.png', 'logo_Raloy');
    $mail->Body = $_POST["data"];
//    $mail->msgHTML(file_get_contents("http://www.skyblue.mx/distribucion/php/sendMail.php?folio=" . $_POST["folio"]), __DIR__);
    $mail->AltBody = "http://www.skyblue.mx/distribucion/solicitudCarga.php?folio=" . $_POST["folio"];
    $mail->CharSet = 'UTF-8';

    $exito = $mail->Send();
    $errores = $mail->ErrorInfo;
    if (!$exito) {
        $respuesta["status"] = 0;
        $respuesta["errores"] = "ERRORES: " . $errores;
    } else {
        $respuesta["status"] = 1;
    }
    return json_encode($respuesta);
}
