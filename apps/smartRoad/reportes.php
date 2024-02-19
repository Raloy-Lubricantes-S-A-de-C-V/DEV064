<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["sessionInfo"]["userSession"]) || $_SESSION["sessionInfo"]["userSession"] == "") {
    header('Location: ../../login.html?app=smartRoad');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Reportes Logística SkyBlue</title>
        <link rel="icon" type="image/png" href="img/icono_camiones.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

       <link rel="icon" type="image/png" href="img/icono_camiones.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--DataTables-->
        <link rel="stylesheet" href="../../libs/DataTables-1.10.16/media/css/jquery.dataTables.min.css">
        <script type="text/javascript" src="../../libs/DataTables-1.10.16/media/js/jquery.dataTables.min.js"></script>

        <!--JqueryUI-->
        <link rel="stylesheet" href="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.css">
        <script type="text/javascript" src="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>

        <!--moment -->
        <script type="text/javascript" src="../../libs/moment.js"></script>

        <!-- Chart.js https://github.com/chartjs/Chart.js/releases/tag/v2.7.2 -->
        <script type="text/javascript" src="../../libs/Chart/Chart.min.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/styleReportes.css">
        <script type="text/javascript" src="js/fReportes.js"></script>
    </head>
    <body>
        <input type='hidden' id='reporteMostrar' value='<?php echo $_GET["r"] ?>'/>
        <div id="cover" class='modalContainer'>
            <div class="cuerpoModal"><i class='fa fa-spinner fa-spin fa-5x'></i></div>
        </div>
        <header>
            <div id='leftHeader'>
                <a id="logo" href="../../index.php"><img src="img/Logo Skyblue horizontal.png" alt="SkyBlue"/></a>
                <span id="appName" ><i class='fas fa-lightbulb'></i> SmartRoad</span>
            </div>
            <div id='rightHeader'>
                <tab><i class="fas fa-user"></i> <span id="userSession"><?php echo $_SESSION["sessionInfo"]["userName"]; ?></span></tab>
                <tab><i class="fa fa-clock-o"></i> <span><?php echo $_SESSION["sessionInfo"]["sessionDate"]; ?></tab>
                <tab><a href='../../login.html'><i class="fa fa-sign-out-alt"></i></a></tab>
            </div>
        </header>
        <div id="subHeader2">
            <div class="shLeft">
                <button id='reload'><i class='fa fa-sync-alt'></i> Actualizar</button>
            </div>
            <div class="shRight">
                <ul>
                    <li><a valor='dashboard'><i class='fa fa-chart-bar'></i> Dashboard</a></li>
                    <li><a valor='cargas'><i class='fa fa-calendar-alt'></i> Cargas</a></li>
                    <li><a valor='entregas'><i class='fa fa-calendar-alt'></i> Fech. Entrega</a></li>
                    <li><a valor='costoxciudad'><i class="fa fa-hand-holding-usd"></i> Costos</a></li>
                </ul>
            </div>
            <hr style="clear:both;"/>
        </div>
        
        <div id='descripcionReporte'></div>
        
        <div id='dashboard' class='reporter'>
            <div class='card' id='card_chart1'>
                <div class='tableCtr'><div id="tableCtr_Chart1" class="tbl"></div></div>
                <div class="chartOptions">
                    <button id="btnOpts_chart1"><i class="fa fa-table"></i></button>
                </div>
                <div class='chartCtr' id='chartCtr_chart1'>
                    <canvas id="chart1" width="90" height="90"></canvas>
                </div>

                <br style="clear:both;"/>
            </div>
        </div>
        
        <div  id='cargas' class='reporter'>
            <div id="calCargas" style='width:80%;margin:0 auto;'></div>
        </div>
        
        <div  id='entregas' class='reporter'>
            <div id="calEntregas" style='width:80%;margin:0 auto;'></div>
        </div>
        <div  id='costoxciudad' class='reporter'>
            <div id="costoxciudadTblCtr"></div>
        </div>
        
    </body>
</html>
