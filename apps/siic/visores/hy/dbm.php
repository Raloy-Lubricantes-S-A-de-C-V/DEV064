<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Queries</title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="images/skico.ico" />
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
    <link rel="stylesheet" href="css/sventasxdet.css">
    <script type="text/javascript" src="js/fdbm.js"></script>
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

<body>
    <div class='container-fluid'>
        <div class='p-2' style='max-height:30vh;'>
            <div class='py-2'>Query</div>
            <div class='row'>
                <div class='col'><textarea id='sql' class='w-100'></textarea></div>
                <div class='col col-auto'><button id='sqlSubmit'>Ejecutar</button></div>
            </div>
        </div>
        <div class='p-2' style='height:70vh'>
            <div class='py-2'>Resultado</div>
            <div id='resultMessage'></div>
            <div id='resultTable'>
                
            </div>
        </div>

    </div>

</body>

</html>