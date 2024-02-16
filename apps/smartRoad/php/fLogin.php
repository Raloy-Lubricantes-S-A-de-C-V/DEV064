<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');

$host = "34.68.173.102";
$user = "hyescas";
$pass = "dub+but";
$db = "scp9000";
$port = "3306";


$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function clearSession() {
    session_start();
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(), '', 0, '/');
    return "Clear";
}

function doLogin() {

    $usuario = $_GET["usuario"];
    $password = $_GET["password"];

    global $hostZK, $userZK, $passZK, $dbZK, $portZK;

    clearSession();

    $mysqli = new mysqli($hostZK, $userZK, $passZK, $dbZK, $portZK);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    $query = <<<SQL
                SELECT 
                  cte_cveUsuario AS userSession,
                  cte_nombre AS userName,
                  NOW() AS sessionDate,
                  MD5(CONCAT(NOW(), cte_cveUsuario)) AS nomSesion
                FROM
                  FCtesSistema
                WHERE cte_cveUsuario = '$usuario' 
                  AND cte_password = '$password' 			
SQL;

    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
        } else {
            //CON DATOS
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $respuesta["status"] = 1;
                session_start();
                $_SESSION["userSession"] = utf8_encode($row["userSession"]);
                $_SESSION["userName"] = utf8_encode($row["userName"]);
                $_SESSION["sessionDate"] = utf8_encode($row["sessionDate"]);
            } else {
                $respuesta = doLoginRaloy();
            }
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function doLoginRaloy() {

    $usuario = $_GET["usuario"];
    $password = $_GET["password"];

    global $host, $user, $pass, $db, $port;

    clearSession();

    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    $query = <<<SQL
               SELECT 
                  cte_cveUsuario AS userSession,
                  cte_nombre AS userName,
                  NOW() AS sessionDate
                FROM
                  FCtesSistema
                WHERE cte_cveUsuario = '$usuario' 
                  AND cte_password = '$password'		
SQL;

    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
        } else {
            //CON DATOS
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $respuesta["status"] = 1;
                session_start();
                $_SESSION["userSession"] = utf8_encode($row["userSession"]);
                $_SESSION["userName"] = utf8_encode($row["userName"]);
                $_SESSION["sessionDate"] = utf8_encode($row["sessionDate"]);
            } else {
                $respuesta["status"] = 2;
            }
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return $respuesta;
}