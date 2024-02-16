<?php ini_set('default_charset', 'UTF-8');?>
<!DOCTYPE html>
<html>
    <head>
        <title>Despliegue de ventas SkyBlue</title>
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

        <!--Propias-->
        <link rel="stylesheet" href="css/styles.css">
        <script type="text/javascript" src="js/functions.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                r_ventasEvol();
            });
        </script>
    </head>
    <body>
        <header class="mdl-layout__header is-casting-shadow" style="width:100% !important;">
            <div class="mdl-layout__header-row">
                <span class="mdl-layout-title">SkyBlue</span>
                <div class="mdl-layout-spacer"></div>
                <span class="mdl-layout-title mdl-layout--large-screen-only">6 Digits Code: 310210</span>
            </div>
        </header>
        <div id="cuerpo">

            <input id="fec1" type="hidden" value="<?php echo $_GET["fec1"]; ?>"/>
            <input id="fec2" type="hidden" value="<?php echo $_GET["fec2"]; ?>"/>
            <div class="chartContainer">
                <div class="title">
                    EVOLUCIÓN ANUAL DE VENTAS POR MES (REMISIONES RALOY)
                </div>
                <div id="salesChart" class="chart">

                </div> 

            </div>
            <div id="salesLegend" class="legend"></div>
            <div style="clear:both"></div>

        </div>
    </body>
</html>