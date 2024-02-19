<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
include "../php/connParam.php";
require_once '../../../php/conexion.php';

$fase = $_GET["f"];
$response = call_user_func($fase);
echo $response;

//Los usuarios activos en SCP pueden ingresar al sistema, sin embargo, es necesario otorgar permisos en la tabla z_linker_users
function dologin() {

    $usuario = $_GET["user"];
    $password = $_GET["pass"];

    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    
    if ($mysqli->connect_errno) {
        $respuesta["mensaje"] = $mysqli->connect_error; //Error
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }

    $query = <<<SQL
    SELECT 
        cte_cveUsuario AS userSesion,
        cte_nombre AS nomUsuario,
        NOW() AS dateSesion,
        MD5(CONCAT(NOW(), cte_cveUsuario)) AS nomSesion,
        u.edicion,
        u.visualizacion 
    FROM
        FCtesSistema FCS 
        LEFT JOIN
        $dblinker.z_linker_users u 
        ON FCS.cte_cveUsuario = u.usuario 
     WHERE cte_cveUsuario = '$usuario' 
       AND cte_password = '$password'
SQL;
//    echo $query;

    $result = $mysqli->query($query) or die($mysqli->error);

    if ($result->num_rows > 0) {
        $rs = $result->fetch_assoc();

        $userSesion = utf8_encode($rs["userSesion"]);
        $nomUsuario = utf8_encode($rs["nomUsuario"]);
        $dateSesion = utf8_encode($rs["dateSesion"]);
        $nomSesion = utf8_encode($rs["nomSesion"]);
        $edicion = $rs["edicion"];
        $visualizacion = $rs["visualizacion"];


        $query = <<<SQL
				INSERT INTO $dblinker.z_linker_sesiones (
					userSesion,
					nomUsuario,
					dateSesion,
					nomSesion,
					statusSesion
				)
				VALUES(
					"$userSesion",
					"$nomUsuario",
					"$dateSesion",
					"$nomSesion",
					"1"
				)
SQL;
        $mysqli->query($query) or die($myqli->error);

        if ($mysqli->affected_rows > 0) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION["userinfo"] = array(
                "userSesion" => $userSesion,
                "nomUsuario" => $nomUsuario,
                "dateSesion" => $dateSesion,
                "nomSesion" => $nomSesion,
                "edicion" => $edicion,
                "visualizacion" => $visualizacion
            );
            $respuesta["edicion"]=$edicion;
            $respuesta["visualizacion"]=$visualizacion;
            $respuesta["status"] = 1;
        } else {
            $_SESSION["userinfo"] = [];
            $respuesta["status"] = 0;
        }
    } else {
        $_SESSION["userinfo"] = [];
        $respuesta["status"] = 0;
    }
    return json_encode($respuesta);
}

function login() {
    $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["mensaje"] = $mysqli->connect_error; //Error
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }

    $userx = $_GET["user"];
    $password = $_GET["pass"];
    $query1 = "select * from z_linker_users where user='$userx' and password='$password'";
//    $respuesta["query"]=$query1;
//    return json_encode($respuesta);
    if ($result = $mysqli->query($query1)) {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $respuesta["mensaje"] = "login";
            $query2 = "insert into z_linker_login_log (user) values ('$userx')";
            if ($mysqli->query($query2)) {
                $respuesta["status"] = 1;
                $respuesta["nombre"] = utf8_encode($row["name"]);
            } else {
                $respuesta["mensaje"] = $result->error . $query;
                $respuesta["status"] = 0;
            }
        } else {
            $respuesta["mensaje"] = "Login Incorrect";
            $respuesta["status"] = 0;
        }
    } else {
        $respuesta["mensaje"] = $result->error . $query;
        $respuesta["status"] = 0;
    }
    return json_encode($respuesta);
}
