<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "Historial de Resultados";
$path = "<a href='index.php?t=" . $_GET["t"] . "'>Merit</a> / " . $title;
$modulo = 20;

require_once($_SERVER['DOCUMENT_ROOT']."/today_zk/php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    header('Location: /intranet/login.html?app=today/index.php');
}

if (!in_array($modulo, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: /intranet/index.php?t=' . $_GET["t"]);
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
    <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

    <!--Fonts Awesome-->
    <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">
    
    <!-- bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!--Datatables-->
    <link href="../../libs/datatables/datatables.min.css" rel="stylesheet" />
    <script src="../../libs/datatables/datatables.js"></script>
    <link href="../../libs/datatables/Buttons-1.5.1/css/buttons.dataTables.min.css" rel="stylesheet" />
    <script src="../../libs/datatables/Buttons-1.5.1/js/dataTables.buttons.min.js"></script>

    <!--Tablas dinámicas-->
    <!--        <link href="../../libs/webdatarocks-1.0.2/webdatarocks.min.css" rel="stylesheet"/>
        <link href="../../libs/webdatarocks-1.0.2/theme/skyblue/webdatarocks.css" rel="stylesheet"/>
        <script src="../../libs/webdatarocks-1.0.2/webdatarocks.toolbar.min.js"></script>
        <script src="../../libs/webdatarocks-1.0.2/webdatarocks.js"></script>-->

    <!--Propias-->
    <link rel="stylesheet" href="../../css/sIndex.css">
    <!--<link rel="stylesheet" href="css/header.css">-->
    <link rel="stylesheet" href="css/solapResults.css">
    <script type="text/javascript" src="js/folapResults.js"></script>
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
</head>
<!--    //b6c0d2 9dabc4 24427a 3c5789-->

<body>
    <div id='loading' style='z-index:10000;padding:0;text-align:center;box-sizing:border-box;position:fixed;top:0;width:100vw;height:100vh;background:rgba(255,255,255,0.7);'>
        <img style='margin:40vh auto;height:20vh;' src='../../img/loading.gif' />
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
                        <a class="dropdown-item" href="/intranet/login.html"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div id="cuerpo" style="height:90vh;">

        <div id="datesPicker">
            <input id='from' class="dates" type="text" value="<?php $m = date("m");
                                                                echo date("Y-" . $m . "-01"); ?>" />
            <input id='to' class="dates" type="text" value="<?php echo date("Y-m-d"); ?>" />
            <button id="changeDates">Mostrar</button> <span id="waitasec"><i class="fas fa-spinner fa-spin"></i> Creando reporte</span>
        </div>
        <div id="searchDiv">
            <input id="searchInput" class="dates" type="text" value="" placeholder="Buscar" />
            <button id="copyBtn">Copiar</button>
            <button id="exportBtn">Exportar XLS</button>
        </div>
        <div id='reportContainer'>
            <table id='tblReport'>
                <thead>
                    <tr>
                        <th colspan="12">Resultados de Análisis de Lotes de EPT SkyBlue del <span id="spanfec1"><?php $m = date("m");
                                                                                                                echo date("Y-" . $m . "-01"); ?></span> al <span id="spanfec2"><?php echo date("Y-m-d"); ?></span></th>
                    </tr>
                    <tr>
                        <th rowspan="2">Lote</th>
                        <th rowspan="2">F. Producción</th>
                        <th rowspan="2">Planta</th>
                        <th rowspan="2">Tanque</th>
                        <th rowspan="2">F. Ingreso Lab.</th>
                        <th rowspan="2">F. Resultado Lab.</th>
                        <th rowspan="2">Lapso</th>
                        <th rowspan="2">Parámetro</th>
                        <th colspan="2">ISO 22241</th>
                        <th colspan="2">Resultado</th>
                    </tr>
                    <tr>
                        <th>Mín.</th>
                        <th>Máx.</th>
                        <th>Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</body>