<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "Usuarios";
$path = "<a href='../index.php?t='" . $_GET["t"] . ">Administración</a> / $title";
$modulo = 26;

require_once($_SERVER['DOCUMENT_ROOT']."/intranet/php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    header('Location: /intranet/login.html?app=today/index.php');
}

if (!in_array($modulo, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: /intranet/index.php?t=' . $_GET["t"]);
}

$dataconn = dataconn("intranet");

$mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
if ($mysqli->connect_errno) {
    return "Failed to connect to MySQL: " . $mysqli->connect_error;
}

$mysqli->set_charset("utf8");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Usuarios</title>
    <link rel="icon" type="image/png" href="../../../img/today.png" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--jQuery-->
    <script type="text/javascript" src="../../../libs/jquery-3.2.1.min.js"></script>

    <!--Fonts Awesome-->
    <link rel="stylesheet" href="../../../libs/fontawesome-free-5.4.2/css/all.min.css">

    <!-- bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>


    <!--Propias-->
    <link rel="stylesheet" href="../../../css/sIndex.css">
    <script type="text/javascript" src="../../../js/findex.js?v=1.0"></script>
    <script type="text/javascript" src="js/fAdminUsers.js"></script>

    <style>
        #formUsuarios {
            display: none;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark px-3 m-0 w-100" style="background:#024a74;">
        <a class="navbar-brand" href="/intranet/index.php">
            <img src="/intranet/img/zarkruse-logo-light.svg" style="height:30px;padding-right:10px;" alt="SkyBlue" />
        </a>
        <div class="navbar-brand">
            <?php echo $path; ?>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
            </ul>
            <div class="form-inline my-2 mr-3 my-lg-0">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-user" style="font-size:0.8em;"></i> <?php echo $_SESSION["sessionInfo"]["userName"]; ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <div class="dropdown-item" href="#"><?php echo $_SESSION["sessionInfo"]["sessionDate"]; ?></div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/intranet/password_change.php"><i class="fa fa-key" style="font-size:0.8em;"></i> Cambiar Password</a>
                        <a class="dropdown-item" href="/intranet/login.html"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div id="cuerpo" class="container-fluid p-2">

        <div id="main">

        </div>

        <div id="formUsuarios" class="w-100 m-0 p-0">

            <div class="w-100 d-flex py-2">
                <button id="backBtn" class="btn btn-secondary ml-auto">Regresar</button>
                <button id="guardarUsuario" class="btn btn-primary mx-2">Guardar</button>
            </div>

            <input type="hidden" id="idUsuario" value="0" />

            <div class="row w-100 p-2 m-0">

                <div class="input-group p-2 col-sm-12 col-md-6">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="tagNombre">Nombre</span>
                    </div>
                    <input id="inputNombre" type="text" class="form-control" placeholder="Nombre Completo" aria-label="Username" aria-describedby="tagNombre">
                </div>
                <div class="input-group p-2 col-sm-12 col-md-6">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Usuario</span>
                    </div>
                    <input id="inputUsuario" type="text" class="form-control" placeholder="Usuario" aria-label="Username" aria-describedby="basic-addon1">
                </div>

            </div>

            <div class="row w-100 p-2 m-0">
                <div class="input-group p-2 col-sm-12 col-md-6">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="tagArea">Área</span>
                    </div>
                    <input id="inputArea" type="text" class="form-control" placeholder="Área / Posición" aria-label="Userarea" aria-describedby="tagArea">
                </div>
                <div class="input-group p-2 col-sm-12 col-md-6">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">e-mail</span>
                    </div>
                    <input id="inputEmail" type="email" class="form-control" placeholder="e-mail" aria-label="Email" aria-describedby="basic-addon1">
                </div>


            </div>

            <div class="row w-100 p-2 m-0">
                <div class="input-group p-2 col-sm-12 col-md-6">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Password</span>
                    </div>
                    <input id="inputPassword" type="password" class="form-control" placeholder="Password" aria-label="Username" aria-describedby="basic-addon1">
                </div>

                <div class="input-group p-2 col-sm-12 col-md-6">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Password</span>
                    </div>
                    <input id="inputPassword2" type="password" class="form-control" placeholder="Password" aria-label="Username" aria-describedby="basic-addon1">
                </div>
            </div>

            <div class="row w-100 p-2 m-0">
                <div class="col-2 p-2">
                    <h3>Plantas</h3>
                    <?php
                    $sql = "SELECT id_planta,planta FROM smartRoad_plantas";
                    $mysqli->set_charset("utf8");
                    $result = $mysqli->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $plantas .= '<div class="form-check">';
                        $plantas .= '<input class="form-check-input checkPlantas" type="checkbox" value="" id="checkPlantas' . $row["id_planta"] . '" id_planta="' . $row["id_planta"] . '">';
                        $plantas .= '<label class="form-check-label" for="checkPlantas' . $row["id_planta"] . '">';
                        $plantas .= $row["planta"];
                        $plantas .= '</label>';
                        $plantas .= '</div>';
                    }
                    $result->free();
                    echo $plantas;
                    ?>
                </div>
                <div class="col-5 p-2">
                    <h3>Permisos</h3>
                    <?php
                    $sql = 'SELECT m.moduloPadre,id_permiso,IF(m.moduloPadre>0,(SELECT p.nombreModulo FROM framework_modulos p WHERE p.id_modulo=m.moduloPadre),nombreModulo) padre, IF(moduloPadre=0,"",nombreModulo) modulo,desc_permiso  permiso FROM framework_modulos_permisos p INNER JOIN framework_modulos m ON p.id_modulo_padre=m.id_modulo AND m.active=1 ORDER BY padre,modulo,permiso';
                    $result = $mysqli->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $permisos .= '<div class="form-check">';
                        $permisos .= '<input class="form-check-input checkPermisos" type="checkbox" value="" id="checkPermisos' . $row["id_permiso"] . '" id_permiso="' . $row["id_permiso"] . '">';
                        $permisos .= '<label class="form-check-label" for="checkPermisos' . $row["id_permiso"] . '">';
                        $permisos .= $row["padre"] . " " . $row["modulo"] . " " . $row["permiso"];
                        $permisos .= '</label>';
                        $permisos .= '</div>';
                    }
                    echo $permisos;
                    ?>
                </div>
                <div class="col-5 p-2">
                    <h3>Reportes</h3>
                    <div class="text-secondary w-100">Los permisos de Siic deben estar activados</div>
                    <?php
                    $sql = "SELECT IDReporte,NomReporte FROM siic_reportes WHERE Active=1 ORDER BY NomReporte";
                    $result = $mysqli->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $reportes .= '<div class="form-check">';
                        $reportes .= '<input class="form-check-input checkReportes" type="checkbox" value="" id="checkReportes' . $row["IDReporte"] . '" id_reporte="' . $row["IDReporte"] . '">';
                        $reportes .= '<label class="form-check-label" for="checkReportes' . $row["IDReporte"] . '">';
                        $reportes .= $row["NomReporte"];
                        $reportes .= '</label>';
                        $reportes .= '</div>';
                    }
                    $result->free();
                    $mysqli->close();
                    echo $reportes;
                    ?>
                </div>
            </div>

        </div>





    </div>
</body>

</html>