<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "Ventas ZK";
$path = "<a href='/intranet/apps/siic/index.php'>SIIC</a> / " . $title;
$modulo = 3;

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    header('Location: /today_zk/login.html?app=siic/index.php');
}

if (!in_array($modulo, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: /today_zk/index.php?t=' . $_GET["t"]);
}
?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo $title; ?></title>
    <link rel="icon" type="image/png" href="images/skico.ico" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--jQuery-->
    <script type="text/javascript" src="../../../../libs/jquery-3.2.1.min.js"></script>

    <!--Fonts Awesome-->
    <link rel="stylesheet" href="../../../../libs/fontawesome-free-5.4.2/css/all.min.css">

    <!--Tablas dinámicas-->
    <link href="../../../../libs/webdatarocks-1.0.2/webdatarocks.min.css" rel="stylesheet" />
    <link href="../../../../libs/webdatarocks-1.0.2/theme/skyblue/webdatarocks.css" rel="stylesheet" />
    <script src="../../../../libs/webdatarocks-1.0.2/webdatarocks.toolbar.min.js"></script>
    <script src="../../../../libs/webdatarocks-1.0.2/webdatarocks.js"></script>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>



    <!--Propias-->

    <link rel="stylesheet" href="../../../../css/bhc.css">
    <!--<link rel="stylesheet" href="css/header.css">-->
    <link rel="stylesheet" href="css/sventasxdet.css">
    <script type="text/javascript" src="js/fventaszk_olap.js"></script>
    <style>
        #wdr-pivot-view .wdr-grid-layout .wdr-empty {
            border-color: transparent !important;
        }

        #wdr-pivot-view .wdr-grid-layout #wdr-data-sheet {
            border-color: transparent !important;
        }

        #wdr-pivot-view .wdr-grid-layout div.wdr-cell {
            border-color: transparent !important;
        }
    </style>
    <link rel="stylesheet" href="/intranet/css/sIndex.css">
</head>
<!--    //b6c0d2 9dabc4 24427a 3c5789-->

<body>
    <div id='loading' style='z-index:10000;padding:0;text-align:center;box-sizing:border-box;position:fixed;top:0;width:100vw;height:100vh;background:rgba(255,255,255,0.7);'>
        <img style='margin:40vh auto;height:20vh;' src='../../../../img/loading.gif' />
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark px-3 m-0 w-100" style="background:#024a74;">
        <a class="navbar-brand" href="/intranet/index.php">
            <img src="/today_zk/img/zarkruse-logo-light.svg" style="height:30px;padding-right:10px;" alt="SkyBlue" />
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
                        <a class="dropdown-item" href="/today_zk/login.html"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div id="cuerpo" style="height:90vh;">
        <div id='dates' class='d-flex p-2 justify-content-end'>
            <input id='from' type="date" class='form-control' value="<?php echo $_GET["fec1"]; ?>" />
            <input id='to' type="date" class='form-control' value="<?php echo $_GET["fec2"]; ?>" />
            <button id='btn-update-data' type='button' class='btn btn-primary'>Mostrar</buton>
        </div>


        <div id='reportContainer'>
            <!--                <table id='tblReport'>
                    <thead>
                        <tr>
                            <th>Fecha P</th>
                            <th>Fecha R</th>
                            <th>Fecha F</th>
                            <th>Equipo</th>
                            <th>Estado</th>
                            <th>Ciudad</th>
                            <th>Cliente</th>
                            <th>Id Determinante</th>
                            <th>Determinante</th>
                            <th>Cve Prod</th>
                            <th>Producto</th>
                            <th>Empaque</th>
                            <th>Pzas</th>
                            <th>Lts</th>
                            <th>Albarán</th>
                            <th>Pedido</th>
                            <th>Pedido C</th>
                            <th>Factura</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>-->
        </div>
    </div>
</body>