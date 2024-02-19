<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');

include("conexion.php");


$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function changePass()
{
    $id_usuario = $_SESSION["sessionInfo"]["userSession"];
    $pass = $_GET["pass"];
    $npass = $_GET["npass"];

    $sqlCheck = "SELECT passw FROM framework_usuarios WHERE id_usuario=$id_usuario AND passw='$pass'";
    $dataconn = dataconn("intranet");

    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        return "Failed to connect to MySQL: " . $mysqli->connect_error;
    }

    $mysqli->set_charset("utf8");
    if ($result = $mysqli->query($sqlCheck)) {
        if ($result->num_rows > 0) {
            $sqlChange = "UPDATE framework_usuarios SET  passw='$npass' WHERE id_usuario=$id_usuario";
            
            if ($mysqli->query($sqlChange)) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }else{
        return 0;
    }
}
