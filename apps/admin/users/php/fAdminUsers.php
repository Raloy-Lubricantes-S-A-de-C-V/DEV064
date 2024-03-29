<?php

if (session_status() == PHP_SESSION_NONE) {

    session_start();

}



date_default_timezone_set('America/Mexico_City');

header('Content-Type: text/html; charset=utf-8');



include("../../../../php/conexion.php");





$fase = (array_key_exists("fase",$_GET))?$_GET["fase"]:$_POST["fase"];

$response = call_user_func($fase);

echo $response;



function dimeUsuarios()

{



    $dataconn = dataconn("intranet");



    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    if ($mysqli->connect_errno) {

        return "Failed to connect to MySQL: " . $mysqli->connect_error;

    }



    $mysqli->set_charset("utf8");



    $sql = "SELECT 

                id_usuario,

                usuario,

                nombre,

                area,

                email,

                stridsplantas,

                (SELECT 

                    GROUP_CONCAT(id_permiso) 

                FROM

                    framework_usuarios_permisos p 

                WHERE p.id_usuario = u.id_usuario) strPermisos,

                (SELECT 

                        GROUP_CONCAT(IDReporte) 

                    FROM

                        siic_perfiles r 

                    WHERE r.id_usuario = u.id_usuario) strReportes  

            FROM

                framework_usuarios u ORDER BY nombre";



    $result = $mysqli->query($sql);

    $usuarios = "<div class='d-flex justify-content-end w-100'>";

    $usuarios .= "<button id='addNewBtn' type='button' class='btn btn-primary m-2'>Nuevo</button>";

    $usuarios .= "</div>";

    $usuarios .= "<table class='table w-100'>";

    $usuarios .= "<thead><th>Nombre</th><th>Usuario</th><th>Área/Puesto</th><th>e-mail</th><th></th></thead>";

    while ($row = $result->fetch_assoc()) {



        $usuarios .= '<tr class="w-100" >';



        $usuarios .= "<td>" . $row["nombre"] . "</td>";

        $usuarios .= "<td>" . $row["usuario"] . "</td>";

        $usuarios .= "<td>" . $row["area"] . "</td>";

        $usuarios .= "<td>" . $row["email"] . "</td>";

        $usuarios .= "<td><button type='button' class='editBtn btn btn-secondary' idu='" . $row["id_usuario"] . "' permisos='" . $row["strPermisos"] . "' reportes='" . $row["strReportes"] . "' plantas='" . $row["stridsplantas"] . "' nombre='" . $row["nombre"] . "' usuario='" . $row["usuario"] . "' email='" . $row["email"] . "' area='" . $row["area"] . "'>Editar</button></td>";

        $usuarios .= '</tr>';

    }

    $mysqli->close();

    return $usuarios;

}



function guardarUsuario()

{

    $idu = intval($_POST["idu"]);
    $usuario = $_POST["Usuario"];
    $passw = $_POST["Password"];
    $nombre = $_POST["Nombre"];
    $area = $_POST["Area"];
    $email = $_POST["Email"];
    $fecha = date("Y-m-d");
    $stridsplantas = implode(",", $_POST["Plantas"]);



    if ($idu == 0) {

        $sql = "INSERT INTO framework_usuarios(usuario,passw,nombre,area,email,confirmado,claveConfirmacion,stridsplantas) VALUES('$usuario','$passw','$nombre','$area','$email',1,'AltaIntranet$fecha','$stridsplantas')";

    } else {

        if ($passw == "") {

            $sql = "UPDATE framework_usuarios SET nombre='$nombre',area='$area',email='$email',confirmado=1,claveConfirmacion='ModificadoIntranet$fecha',stridsplantas='$stridsplantas' WHERE id_usuario=$idu";

        } else {

            $sql = "UPDATE framework_usuarios SET passw='$passw',nombre='$nombre',area='$area',email='$email',confirmado=1,claveConfirmacion='ModificadoIntranet$fecha',stridsplantas='$stridsplantas' WHERE id_usuario=$idu";

        }

    }







    $dataconn = dataconn("intranet");

    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    if ($mysqli->connect_errno) {

        return "Failed to connect to MySQL: " . $mysqli->connect_error;

    }

    $mysqli->set_charset("utf8");
    $mysqli->autocommit(FALSE);
    $errors = "";
    if (!$mysqli->query($sql))
        $errors .= "insertUs:" . $mysqli->error;
    if ($idu == 0) {
        $idu=$mysqli->insert_id;
    }else{

        if (!$mysqli->query("DELETE FROM framework_usuarios_permisos WHERE id_usuario=$idu"))

            $errors .= "delPerm:" . $mysqli->error;

        if (!$mysqli->query("DELETE FROM siic_perfiles WHERE id_usuario=$idu"))

            $errors .= "delRep:" . $mysqli->error;

    }



    $arrPermisos = [];

    foreach ($_POST["Permisos"] as $id_permiso) {

        $arrPermisos[] = "(" . $idu . "," . $id_permiso . ",1)";

    }

    $valuesPermisos = implode(",", $arrPermisos);



    $arrReportes = [];

    foreach ($_POST["Reportes"] as $id_reporte) {

        $arrReportes[] = "('" . $usuario . "'," . $id_reporte . ",1,6,'" . $stridsplantas . "',$idu)";

    }

    $valuesReportes = implode(",", $arrReportes);





    if (count($_POST["Permisos"]) > 0) {



        $sqlPermisos = "INSERT INTO framework_usuarios_permisos(id_usuario,id_permiso,edicion) VALUES $valuesPermisos";

        if (!$mysqli->query($sqlPermisos))

            $errors .= $sqlPermisos . " insPerm: " . $mysqli->error;

    }



    if (count($_POST["Reportes"]) > 0) {

        $sqlReportes = "INSERT INTO siic_perfiles(UsrName,IDReporte,Rate,Freq,IDPlanta,id_usuario) VALUES $valuesReportes";

        if (!$mysqli->query($sqlReportes))

            $errors .= $sqlReportes . " insRep: " . $mysqli->error;

    }



    if ($errors == "") {

        $mysqli->commit();

        $respuesta = 1;

    } else {

        $mysqli->rollback();

        $respuesta = $errors;

    }



    $mysqli->close();

    return $respuesta;

}

