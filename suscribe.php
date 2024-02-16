<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'libs/phpMailer/src/Exception.php';
require 'libs/phpMailer/src/PHPMailer.php';
require 'libs/phpMailer/src/SMTP.php';

require 'php/conexion.php';
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <?php
        $fase = $_GET["fase"];
        $response = call_user_func($fase);
        echo $response;

        function confirmRegistration() {
            $key = $_GET["k"];
            $id_user = $_GET["i"];
            $dataconn = dataconn("intranet");
            $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
            $query = "SELECT claveConfirmacion clave FROM framework_usuarios where id_usuario=$id_user";
           
            if ($result = $mysqli->query($query)) {
                $row = $result->fetch_assoc();
                if ($row["clave"] != $key) {
                    return "Clave Inválida";
                }
            } else {
                $mysqli->close();
                return $mysqli->error;
            }
            $queryUpdate = "UPDATE framework_usuarios SET confirmado=1 WHERE id_usuario=$id_user";
            $mysqli->query($queryUpdate);
            return "Su registro ha sido confirmado exitosamente.<br/> Por favor ingrese mediante el enlace y agregue la página a sus Marcadores (Favoritos)<br/><br/><a href='login.html'>Iniciar Sesión</a><br/>";
        }

        function sendEmails() {
            session_start();
            if (!isset($_SESSION["sessionInfo"]) || !in_array(3, $_SESSION["sessionInfo"]["idsModulos"])) {
                echo "Por favor inicie sesión en Intranet y vuelva a intentar <a href='login.html'>Ir a página de Login</a>";
                return "";
            }
            $query = "SELECT id_usuario,email,usuario,passw FROM framework_usuarios where confirmado=0";
            $dataconn = dataconn("intranet");
            $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
            $length = 10;
            $emails = [];
            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_assoc()) {
                    $length = rand(8, 18);
                    $passw = generateRandomString(4);
                    $emails[] = array("id_usuario" => $row["id_usuario"], "passw" => $passw, "usuario" => $row["usuario"], "to" => $row["email"], "str" => generateRandomString($length));
                }
            } else {
                $mysqli->close();
                return $mysqli->error;
            }
            $result->free();
            $id_usuario = "";
            $to = "";
            $str = "";
            $subject = "Bienvenido a Intranet";
//            $mail = new PHPMailer(TRUE);
            foreach ($emails as $datosemail) {
                $id_usuario = $datosemail["id_usuario"];
                $to = $datosemail["to"];
                $str = $datosemail["str"];
                $usuario = $datosemail["usuario"];
                $passw = $datosemail["passw"];

                $body = "<!DOCTYPE html><html><head></head><body>Bienvenido a Intranet SkyBlue.<br/> Su usuario:$usuario, su Password:$passw<br/> Por favor confirme su registro mediante el siguiente enlace:<br/><a href='www.skyblue.mx/intranetZK/suscribe.php?fase=confirmRegistration&k=$str&i=$id_usuario'>Confirmar Mi Registro</a></body></html>";
                $headers = "From: Intranet SkyBlue <hector.yescas@raloycrm.com.mx>" . "\r\n";
                $headers .= "Reply-To: hyescas@raloy.com.mx" . "\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                if (mail($to, $subject, $body, $headers)) {
                    $query = "UPDATE framework_usuarios SET claveConfirmacion='$str', passw='$passw' WHERE id_usuario=$id_usuario";
                    $mysqli->query($query);
                } else {
                    return "Errores de envío " . date("d-m-Y  H:i:s");
                }
            }
            return "Emails enviados:" . count($emails);
        }

        function generateRandomString($length = 10) {
            $characters = '23456789abcdefghijkmnopqrstuvwxyz';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }
        ?>
    </body>
</html>