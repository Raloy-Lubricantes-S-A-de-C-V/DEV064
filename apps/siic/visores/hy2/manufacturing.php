<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]) || !in_array(3, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: ../../login.html');
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Reportes del SIIC">
    <link rel="shortcut icon" href="images/raloy.ico">
    <title>Dashboard Skyblue</title>

    <!--jQuery-->
    <script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>

    <!--mdl-->
    <!-- <link rel="stylesheet" media="screen" type="text/css" href="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.css">
        <script src="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.js"></script> -->

    <link href="/today_zk/libs/bootstrap-4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" src="/today_zk/libs/bootstrap-4.3.1/js/bootstrap.min.js"></script>

    <!--fonts-->
    <link href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=es" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!--DataTables (Tablas)-->
    <link rel="stylesheet" href="../hy/libs/DataTables-1.10.10/media/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../hy/libs/DataTables-1.10.10/extensions/Buttons/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="../hy/libs/DataTables-1.10.10/extensions/FixedHeader/css/fixedHeader.dataTables.min.css">

    <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/media/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/extrasrequeridos/jszip.min.js"></script>
    <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/extrasrequeridos/pdfmake.min.js"></script>
    <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/extrasrequeridos/vfs_fonts.js"></script>
    <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/Buttons/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/Buttons/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/Buttons/js/buttons.print.js"></script>
    <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/FixedHeader/js/dataTables.fixedHeader.min.js"></script>

    <!--Charts-->
    <script type="text/javascript" src="js/gChart/jquery.plugin.min.js"></script>
    <script type="text/javascript" src="js/gChart/jquery.gchart.min.js"></script>

    <!--Propias-->
    <link rel="stylesheet" type="text/css" href="css/estilos.css">
    <script type="text/javascript" src="js/functionsManuf.js"></script>

    <style>
        #view-source {
            position: fixed;
            display: block;
            right: 0;
            bottom: 0;
            margin-right: 40px;
            margin-bottom: 40px;
            z-index: 900;
        }

        .oneframe {
            width: 99%;
            margin: 0 0.5%;
            height: 100%;
        }

        .twoframes {
            max-width: 49.5% !important;
            margin: 0 0.1%;
            height: 100%;
        }

        .threeframes {
            width: 32%;
            margin: 0 0.5%;
            height: 100%;
        }

        .label {
            /*width:140px;*/
            font-weight: bold;
        }

        .detalleConsumoPdn th {
            position: relative;
            vertical-align: bottom;
            text-overflow: ellipsis;
            font-weight: 700;
            line-height: 24px;
            letter-spacing: 0;
            height: 48px;
            font-size: 12px;
            color: rgba(0, 0, 0, .54);
            padding-bottom: 8px;
            box-sizing: border-box;
        }

        .detalleConsumoPdn td:last-of-type {
            padding-right: 24px;
        }

        .detalleConsumoPdn td:first-of-type {
            padding-left: 24px;
        }

        th {
            text-align: center !important;
        }

        .blueButtonDiv {
            float: right;
            margin-top: 15px;
            margin-left: 10px;
        }

        .mdl-data-table tbody tr,
        .mdl-data-table tbody tr td {
            height: 38px !important;
        }

        .pdnInputInv {
            font-size: 11px !important;
        }

        .highlight {
            font-weight: bold !important;
            background: #fafafa !important;
        }

        .small {
            font-size: 10px !important;
        }
    </style>
</head>

<body>
    <input id="uSe" type="hidden" value="<?php echo $_SESSION["sessionInfo"]["user"]; ?>" />
    <input id="idR" type="hidden" value="<?php echo $_GET["id"]; ?>" />
    <input type="hidden" id="fec1" value="<?php echo $_GET["fec1"]; ?>" />
    <input type="hidden" id="fec2" value="<?php echo $_GET["fec2"]; ?>" />
    <div class="d-flex w-100 text-white m-0 p-2" style="background:#0C2E6C;">
        <div class="col-12">
            <h2>Producción Zar Kruse</h2>
        </div>
    </div>
    <div class="container-fluid">
        <div class="d-flex w-100 text-success" id="messages">Procesando ...</div>
        <div class="row border-top w-100 p-0 m-0">
            <div class="col-12">
                <table id="resumenTbl" class="table table-responsive w-100">
                    <thead>
                        <tr>
                            <th class="">Resumen</th>
                            <th class="STG">STG</th>
                            <th class="GDL">GDL</th>
                            <th class="MTY">MTY</th>
                            <th class="SAL">SAL</th>
                            <th class="MER">MER</th>
                            <th class="BAJ">BAJ</th>
                            <th class="CUL">CUL</th>
                            <th class="MLI">MLI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="">PRODUCCIÓN TOTAL EN EL PERIODO (LTS)</td>
                            <td class="pdnInput STG" tag="Liters"></td>
                            <td class="pdnInput GDL" tag="Liters"></td>
                            <td class="pdnInput MTY" tag="Liters"></td>
                            <td class="pdnInput SAL" tag="Liters"></td>
                            <td class="pdnInput MER" tag="Liters"></td>
                            <td class="pdnInput APA" tag="Liters"></td>
                            <td class="pdnInput CUL" tag="Liters"></td>
                            <td class="pdnInput MLI" tag="Liters"></td>
                        </tr>
                        <tr>
                            <td>PRODUCCIÓN MENSUAL PROMEDIO (LTS)</td>
                            <td class="pdnInput STG" tag="LitersAvg"></td>
                            <td class="pdnInput GDL" tag="LitersAvg"></td>
                            <td class="pdnInput MTY" tag="LitersAvg"></td>
                            <td class="pdnInput SAL" tag="LitersAvg"></td>
                            <td class="pdnInput MER" tag="LitersAvg"></td>
                            <td class="pdnInput APA" tag="LitersAvg"></td>
                            <td class="pdnInput CUL" tag="LitersAvg"></td>
                            <td class="pdnInput MLI" tag="LitersAvg"></td>
                        </tr>
                        <tr>
                            <td class="">AGUA PERMEADA CONSUMIDA (LTS)</td>
                            <td class="pdnInput STG" tag="agua"></td>
                            <td class="pdnInput GDL" tag="agua"></td>
                            <td class="pdnInput MTY" tag="agua"></td>
                            <td class="pdnInput SAL" tag="agua"></td>
                            <td class="pdnInput MER" tag="agua"></td>
                            <td class="pdnInput APA" tag="agua"></td>
                            <td class="pdnInput CUL" tag="agua"></td>
                            <td class="pdnInput MLI" tag="agua"></td>
                        </tr>
                        <tr>
                            <td class="">CONSUMO DE U-100 EN EL PERIODO (KGS)</td>
                            <td class="pdnInput STG" tag="urea"></td>
                            <td class="pdnInput GDL" tag="urea"></td>
                            <td class="pdnInput MTY" tag="urea"></td>
                            <td class="pdnInput SAL" tag="urea"></td>
                            <td class="pdnInput MER" tag="urea"></td>
                            <td class="pdnInput APA" tag="urea"></td>
                            <td class="pdnInput CUL" tag="urea"></td>
                            <td class="pdnInput MLI" tag="urea"></td>
                        </tr>
                        <tr>
                            <td>CONSUMO DE U-100 POR LITRO (KGS/LT)</td>
                            <td class="pdnInput STG" tag="ureaxl"></td>
                            <td class="pdnInput GDL" tag="ureaxl"></td>
                            <td class="pdnInput MTY" tag="ureaxl"></td>
                            <td class="pdnInput SAL" tag="ureaxl"></td>
                            <td class="pdnInput MER" tag="ureaxl"></td>
                            <td class="pdnInput APA" tag="ureaxl"></td>
                            <td class="pdnInput CUL" tag="ureaxl"></td>
                            <td class="pdnInput MLI" tag="ureaxl"></td>
                        </tr>
                        <tr>
                            <td>MIX DE U-100 EN EL PERIODO</td>
                            <td class="mixInput STG" tag="ureamix"></td>
                            <td class="mixInput GDL" tag="ureamix"></td>
                            <td class="mixInput MTY" tag="ureamix"></td>
                            <td class="mixInput SAL" tag="ureamix"></td>
                            <td class="mixInput MER" tag="ureamix"></td>
                            <td class="mixInput APA" tag="ureamix"></td>
                            <td class="mixInput CUL" tag="ureamix"></td>
                            <td class="mixInput MLI" tag="ureamix"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <h4>CONSUMOS DE U-100 POR ORDEN DE PRODUCCIÓN</h4>
            </div>
        </div>
        <div class="row">
            <div class="col" id="detalleConsumoPdn">
                <table class="table w-100" id='tblDetallePdn' style='width:100%'>
                    <thead>
                        <tr>
                            <th>PLANTA</th>
                            <th>OP</th>
                            <th>FECHA Y HORA</th>
                            <th>KGS U-100</th>
                            <th>LTS AGUA</th>
                            <th>LTS EPT</th>
                            <th>UTILIZACIÓN</th>
                            <th>MIX</th>
                            <th>IF PLANTAS</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </section>
    </div>
    </div>

</body>

</html>