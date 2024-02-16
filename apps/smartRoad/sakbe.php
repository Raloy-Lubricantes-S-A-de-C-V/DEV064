<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]) || !in_array(2, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: ../../login.html?app=smartRoad/index.php');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>SAKBE</title>
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

        <!--Propias-->
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/style.css">
        <script type="text/javascript" src="js/sakbe.js"></script>
    </head>
    <body>
        <div id='selector'><select id='tipoRuteo'></select></div>
        <input type='text' id='id_entrega'/><button id='buscarentrega'>Buscar</button>
        <div id='mapa'></div>
        <div id='resultados'>Resultados</div>
        <table id='tbldestinos'>
            <thead></thead>
            <tbody>
                
            </tbody>
        </table>
        <button id='identificardestinos'>Identificar Destinos</button>
        <button id='dimeruta'>Ruta</button>
        <button id='dimetotales'>Totales</button>
        <div id='totalpeajes'></div>
        <div id='totalkms'></div>
    </body>
</html>
