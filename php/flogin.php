<?php
date_default_timezone_set('America/Mexico_City');
require_once("conexion.php");
$fase = $_POST["f"];
$response = call_user_func($fase);
echo $response;

function clearSession()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(), '', 0, '/');
    $_SESSION["sessionInfo"] = [];
    rememberMe_kill();
    return "Clear";
}

function rememberMe_get()
{
    $dataconn = dataconn("intranet");

    if (isset($_COOKIE['intranetZarKruse']['user'])) {
        $iduser = $_COOKIE['intranetZarKruse']['user'];
        $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
        $query = "SELECT usuario,passw FROM framework_usuarios where id_usuario=$iduser";
        $result = $mysqli->query($query);
        $row = $result->fetch_assoc();
        $respuesta["username"] = $row["usuario"];
        $respuesta["password"] = $row["passw"];
        $respuesta["status"] = 1;
    } else {
        $respuesta["username"] = "";
        $respuesta["password"] = "";
        $respuesta["status"] = 0;
    }
    clearSession();
    return json_encode($respuesta);
}

function rememberMe_kill()
{
    unset($_COOKIE['intranetZarKruse']);
    setcookie("intranetZarKruse[user]", "", time() - 3600);
    return 1;
}

function rememberMe_set($shouldIrememberYou, $user)
{
    if ($shouldIrememberYou == true) {
        if (!isset($_COOKIE['intranetZarKruse']['user'])) {
            setcookie("intranetZarKruse[user]", $user, time() + 604800);
        }
    } else {
        rememberMe_kill();
    }
}

function dologin()
{
    //kill previous sessions
    clearSession();

    //Try to start new session
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];
    $shouldIrememberyou = $_POST["shouldIrememberyou"];
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $query = <<<SQL
        SELECT 
            usuarios.id_usuario,
            usuario AS userSesion,
            nombre AS userName,
            COUNT(
                DISTINCT modulos.id_modulo_padre
            ) numModulos,
            GROUP_CONCAT(DISTINCT modulos.id_modulo_padre) strIdsMods,
            GROUP_CONCAT(DISTINCT permisos.id_permiso) strIdsPerms,
            stridsplantas
        FROM
            framework_usuarios usuarios 
            LEFT JOIN
            framework_usuarios_permisos permisos 
            ON usuarios.id_usuario = permisos.id_usuario 
            LEFT JOIN
            framework_modulos_permisos modulos 
            ON permisos.id_permiso = modulos.id_permiso 
        WHERE usuarios.passw = '$password' 
            AND usuario = '$usuario'  
        GROUP BY id_usuario 
SQL;
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows > 0) {
            $rs = $result->fetch_assoc();
            if ($rs["numModulos"] > 0) {
                //start new session
                session_start();
                $_SESSION["sessionInfo"]["userSession"] = $rs["id_usuario"];
                $_SESSION["sessionInfo"]["user"] = $rs["userSesion"];
                $_SESSION["sessionInfo"]["userName"] = $rs["userName"];
                $_SESSION["sessionInfo"]["sessionDate"] = date("Y-m-d H:i:s");
                $_SESSION["sessionInfo"]["strIdsMods"] = $rs["strIdsMods"];
                $_SESSION["sessionInfo"]["idsModulos"] = explode(",", $rs["strIdsMods"]);
                $_SESSION["sessionInfo"]["strIdsPerms"] = $rs["strIdsPerms"];
                $_SESSION["sessionInfo"]["strIdsPlantas"] = $rs["stridsplantas"];
                $strIdsPlantas = $rs["stridsplantas"];
                $result->free();

                // Resto del código para configurar la sesión...

                $respuesta["sesion"] = $_SESSION["sessionInfo"];
                $respuesta["status"] = 1;
                $respuesta["error"] = "";
                
                // Resto del código...

            } else {
                $respuesta["sesion"] = [];
                $respuesta["status"] = 0;
                $respuesta["error"] = "Usuario sin permisos asignados";
            }
        } else {
            $respuesta["sesion"] = [];
            $respuesta["status"] = 0;
            $respuesta["error"] = "Usuario o password incorrectos";
        }
    } else {
        $respuesta["sesion"] = [];
        $respuesta["status"] = 0;
        $respuesta["error"] = "Error " . $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}
