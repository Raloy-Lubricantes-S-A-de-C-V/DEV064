<!DOCTYPE html>
<html>
    <head>
        <title>Despliegue de ventas por área</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="libs/morris.js-0.5.1/morris.css">
        <link rel="stylesheet" href="css/styles.css">

        <script type="text/javascript" src="libs/jquery-1.11.3.min.js"></script>
        <script type="text/javascript" src="libs/morris.js-0.5.1/raphael-min.js"></script>
        <script type="text/javascript" src="libs/morris.js-0.5.1/morris.min.js"></script>
        <script type="text/javascript" src="js/functions.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                r_ventasxArea();
            });
        </script>
    </head>
    <body>

        <div id="cuerpo">
            <input id="fec1" type="hidden" value="<?php echo $_GET["fec1"]; ?>"/>
            <input id="fec2" type="hidden" value="<?php echo $_GET["fec2"]; ?>"/>
            <div class="chartContainer">
                <div class="title">
                    DESPLIEGUE DE VENTAS POR ÁREA <br/>(REMISIONES RALOY)
                </div>
                <div id="salesChart" class="chart">

                </div> 

            </div>
            <div id="salesLegend" class="legend"></div>
            <div style="clear:both"></div>

        </div>
    </body>
</html>