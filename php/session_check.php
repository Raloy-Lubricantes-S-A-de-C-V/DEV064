<?php


date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/today.zar-kruse.com/php/conexion.php");



function session_check($token = "")

{

    if (session_status() == PHP_SESSION_NONE) {

        session_start();
    }



    if (isset($_SESSION["sessionInfo"])) {

        return 1;
    } else {



        if ($token == "" || $token == null || $token == "undefined") {

            return "Token Null";
        } else {

            return checkDB($token);
        }
    }
}



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
}



function checkDb($token)

{



    $respuesta = 0;

    $dataconn = dataconn("intranet");

    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    if ($mysqli->connect_errno) {

        return "Error de conexión";
    }

    $query = "SELECT 

                id_usuario,

                DATE_FORMAT(dateSesion, '%Y-%m-%d') diaSesion 

            FROM

                framework_sesiones 

            WHERE nomSesion = '$token' AND DATE(dateSesion)='" . date("Y-m-d") . "'

            ORDER BY id_sesion DESC 

            LIMIT 1";



    if ($result = $mysqli->query($query)) {

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();

            $result->free();

            $mysqli->close();

            $respuesta = dologin($row["id_usuario"], $token);
        } else {

            $respuesta = "El Token ha expirado";
        }
    } else {

        $respuesta = $mysqli->error;
    }

    return $respuesta;
}



function dologin($id_usuario, $token)

{

    $respuesta = 0;

    //kill previous sessions

    clearSession();



    //Try to start new session

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

        WHERE usuarios.id_usuario=$id_usuario

        GROUP BY id_usuario 

SQL;



    if ($result = $mysqli->query($query)) {

        if ($result->num_rows > 0) {

            $rs = $result->fetch_assoc();

            if ($rs["numModulos"] > 0) {

                //start new session

                session_start();

                $_SESSION["sessionInfo"]["userSession"] = utf8_encode($rs["id_usuario"]);

                $_SESSION["sessionInfo"]["user"] = utf8_encode($rs["userSesion"]);

                $_SESSION["sessionInfo"]["userName"] = utf8_encode($rs["userName"]);

                $_SESSION["sessionInfo"]["sessionDate"] = date("Y-m-d H:i:s");

                $_SESSION["sessionInfo"]["strIdsMods"] = $rs["strIdsMods"];

                $_SESSION["sessionInfo"]["idsModulos"] = explode(",", $rs["strIdsMods"]);

                $_SESSION["sessionInfo"]["strIdsPerms"] = $rs["strIdsPerms"];

                $_SESSION["sessionInfo"]["strIdsPlantas"] = $rs["stridsplantas"];

                $strIdsPlantas = $rs["stridsplantas"];

                $result->free();



                $queryParametros = "SELECT nombre_param,valor from framework_parametros";

                $result = $mysqli->query($queryParametros);

                while ($row = $result->fetch_assoc()) {

                    $_SESSION["parametros"][$row["nombre_param"]] = $row["valor"];
                }

                $result->free();



                $queryPlantas = "SELECT id_planta,nomenc_lab,planta,AlmacenR,siic_ventas,nomenc_pdn FROM smartRoad_plantas WHERE id_planta IN(" . $strIdsPlantas . ")";

                $result = $mysqli->query($queryPlantas);

                while ($row = $result->fetch_assoc()) {

                    $lab[] = "'" . $row["nomenc_lab"] . "'";

                    $wh[] = "'" . $row["AlmacenR"] . "'";

                    $sales[] = "'" . $row["siic_ventas"] . "'";

                    $manuf[] = "'" . $row["nomenc_pdn"] . "'";

                    $plants[] = "'" . $row["planta"] . "'";
                }

                $result->free();

                $_SESSION["sessionInfo"]["strPlantas"]["lab"] = implode(",", $lab);

                $_SESSION["sessionInfo"]["strPlantas"]["wh"] = implode(",", $wh);

                $_SESSION["sessionInfo"]["strPlantas"]["sales"] = implode(",", $sales);

                $_SESSION["sessionInfo"]["strPlantas"]["manuf"] = implode(",", $manuf);

                $_SESSION["sessionInfo"]["strPlantas"]["plants"] = implode(",", $plants);



                $id_usuario = $_SESSION["sessionInfo"]["userSession"];

                $user = $_SESSION["sessionInfo"]["user"];

                $nombre = $_SESSION["sessionInfo"]["userName"];

                $fechaSesion = $_SESSION["sessionInfo"]["sessionDate"];

                $respuesta["token"] = $token;



                $querySesion = <<<SQL

                INSERT INTO framework_sesiones (

                    id_usuario,

                    userSesion,

                    nomUsuario,

                    dateSesion,

                    nomSesion,

                    statusSesion

                ) 

                VALUES

                    (

                        $id_usuario,

                        '$user',

                        '$nombre',

                        '$fechaSesion',

                        '$token',

                         '1'

                    )

SQL;



                if ($mysqli->query($querySesion)) {

                    $respuesta = 1;
                } else {

                    $respuesta = "Error SessionRegister " . $mysqli->error;
                }
            }
        } else {

            $respuesta = "No se encontró el usuario";
        }
    } else {

        $respuesta = "Error Login " . $mysqli->error;
    }

    $mysqli->close();

    return $respuesta;
}
