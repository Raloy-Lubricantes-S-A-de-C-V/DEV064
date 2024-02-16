<!DOCTYPE html>
<html>
    <head>
        <title>VENTAS (Remisiones ZK)</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="images/skico.ico">

        <!--jQuery-->
        <script type="text/javascript" src="libs/jquery-1.11.3.min.js"></script>

        <!--mdl-->
        <link rel="stylesheet" media="screen" type="text/css" href="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.css">
        <script src="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.js"></script>

        <!--Morris (Gráficas)-->
        <link rel="stylesheet" href="libs/morris.js-0.5.1/morris.css">
        <script type="text/javascript" src="libs/morris.js-0.5.1/raphael-min.js"></script>
        <script type="text/javascript" src="libs/morris.js-0.5.1/morris.min.js"></script>

        <!--Autonumeric-->
        <script type="text/javascript" src="libs/autonumeric/numberformatter/libs/jshashtable-3.0.js"></script>
        <script type="text/javascript" src="libs/autonumeric/numberformatter/src/numberformatter.js"></script>
        <script type="text/javascript" src="libs/autonumeric/autoNumeric.js"></script>

        <!--DataTables (Tablas)-->
        <link rel="stylesheet" href="libs/DataTables-1.10.10/media/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="libs/DataTables-1.10.10/extensions/Buttons/css/buttons.dataTables.min.css">
        <link rel="stylesheet" href="libs/DataTables-1.10.10/extensions/FixedHeader/css/fixedHeader.dataTables.min.css">

        <script type="text/javascript" src="libs/DataTables-1.10.10/media/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/extrasrequeridos/jszip.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/extrasrequeridos/pdfmake.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/extrasrequeridos/vfs_fonts.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/Buttons/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/Buttons/js/buttons.html5.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/Buttons/js/buttons.print.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/FixedHeader/js/dataTables.fixedHeader.min.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="css/styles.css">
        <script type="text/javascript" src="js/functions.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                zk_ventasVSobj(<?php echo $_GET["id"]; ?>);//con detalle de fechas
            });
        </script>

    </head>
    <body>

        <div id="cuerpo">
            <header class="mdl-layout__header is-casting-shadow">
                <div class="mdl-layout__header-row">
                    <span class="mdl-layout-title">VENTAS SKYBLUE</span>
                    <div class="mdl-layout-spacer"></div>
                    <span class="mdl-layout-title mdl-layout--large-screen-only">REMISIONES ZK</span>
                </div>
            </header>

            <input id="fec1" type="hidden" value="<?php echo $_GET["fec1"]; ?>"/>
            <input id="fec2" type="hidden" value="<?php echo $_GET["fec2"]; ?>"/>
            <input id="uSe" type="hidden" value="<?php echo $_POST["userSesion"]; ?>"/>
            <div class="title" style='font-size:15pt;font-weight:bold;'>
                Del <span id="fec1Sp"></span> Al <span id="fec2Sp"></span>
            </div>
            
            <div id="contenido" class="contenido">
                <hr/>
                <br/>
                <div class="title">
                    ANÁLISIS DE VENTAS VS OBJETIVOS
                </div>
                <table id="mainTable" class="compact">
                    <tfoot><tr><th id="tfootth" colspan="7"></th></tr></tfoot>
                </table>
                <br style="clear:both;"/>
            </div>
        </div>
    </body>
</html>