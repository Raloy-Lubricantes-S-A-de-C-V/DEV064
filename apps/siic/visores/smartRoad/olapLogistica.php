<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]) || !in_array(3, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: ../../../../login.html?app=smartRoad/index.php');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Costo Logístico</title>
        <link rel="icon" type="image/png" href="../hy/images/skico.ico" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--Tablas dinámicas-->
        <link href="../../../../libs/webdatarocks-1.0.2/webdatarocks.min.css" rel="stylesheet"/>
        <link href="../../../../libs/webdatarocks-1.0.2/theme/skyblue/webdatarocks.css" rel="stylesheet"/>
        <script src="../../../../libs/webdatarocks-1.0.2/webdatarocks.toolbar.js"></script>
        <script src="../../../../libs/webdatarocks-1.0.2/webdatarocks.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="../../../../css/bhc.css">
        <!--<link rel="stylesheet" href="css/header.css">-->
        <link rel="stylesheet" href="css/sOlapLogistica.css">
        <script type="text/javascript" src="js/fOlapLogistica.js"></script>
        <style>
            #wdr-pivot-view .wdr-grid-layout .wdr-empty {
                border-color: transparent !important;
            }

            #wdr-pivot-view .wdr-grid-layout #wdr-data-sheet {
                border-color: transparent !important;
            }
            #wdr-pivot-view .wdr-grid-layout div.wdr-cell{
                border-color: transparent !important;
            }
        </style>
    </head>
    <!--    //b6c0d2 9dabc4 24427a 3c5789-->
    <body>
        <div id='loading' style='z-index:10000;padding:0;text-align:center;box-sizing:border-box;position:fixed;top:0;width:100vw;height:100vh;background:rgba(255,255,255,0.7);'>
            <img style='margin:40vh auto;height:20vh;' src='../../../../img/loading.gif'/>
        </div>
        <header>
            <div id='leftHeader'>
                <a id="logo" href="../../../../index.php"><img src="../../../../img/Logo Skyblue horizontal.png" alt="SkyBlue"/></a>
                <span id="appName" >Ventas de SkyBlue (Raloy)</span>
            </div>
            <div id='rightHeader'>
                <tab><i class="fas fa-user"></i> <span id="userSession"><?php echo $_SESSION["sessionInfo"]["userName"]; ?></span></tab>
                <tab><i class="fa fa-clock-o"></i> <span><?php echo $_SESSION["sessionInfo"]["sessionDate"]; ?></tab>
                <tab><a href='../../../../login.html'><i class="fa fa-sign-out-alt"></i></a></tab>
            </div>
        </header>
        <div id="cuerpo" style="height:90vh;">

            <input id='from' type="hidden" value="<?php echo $_GET["fec1"]; ?>"/>
            <input id='to' type="hidden" value="<?php echo $_GET["fec2"]; ?>"/>
            
            <div id='reportContainer'>
            </div>
        </div>
    </body>
