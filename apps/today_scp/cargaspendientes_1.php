<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]) || !in_array(10, $_SESSION["sessionInfo"]["idsModulos"])) {
    if (!in_array(11, $_SESSION["sessionInfo"]["idsModulos"])) {
        if (!in_array(12, $_SESSION["sessionInfo"]["idsModulos"])) {
            header('Location: ../../login.html?app=smartRoad');
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Solicitudes de Carga en Granel</title>
        <meta charset="UTF-8">
        <link rel="icon" type="image/png" href="../../img/cargas.png" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="images/icon.png">

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
        <script type="text/javascript" src="js/functions_cargas.js"></script>
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/style.css">

    </head>
    <body>
        <div id='cuerpo'>
            <div id="divCertificados" class='modalContainer'>
                <div class="cuerpoModal">
                    <div class="menuModal">
                        <div class='leftMenuModal'><button class='closeModal'><i class="fa fa-close"></i> Cerrar</button></div>
                        <div class='centerMenuModal'><div class='avisos'></div></div>
                        <div class='rightMenuModal'></div>
                    </div>
                    <div class="contentModal">
                        <h3>CERTIFICADOS DE CALIDAD</h3>
                        <h3>FOLIO <span id="foliocerts"></span></h3>
                    </div>
                    <div class='respCtr'>Responsable de la Carga: <input type='text' id='responsableCarga'/><button id='saveRespCarga' class='btn'><i class='fa fa-save'></i></button></div>
                    <table id='certs'>
                        <thead>
                            <tr>
                                <th>Entrega</th>
                                <th>Lote EPT</th>
                                <th>Lote PT</th>
                                <th>Sellos E</th> 
                                <th>Sellos D</th>
                                <th>Remisiones ZK</th>
                                <th>Guardar</th>
                                <th>Certificado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div id="divDoctos" class='modalContainer'>
                <div class="cuerpoModal">
                    <div class="menuModal">
                        <div class='leftMenuModal'><button class='closeModal'><i class="fa fa-close"></i> Cerrar</button></div>
                        <div class='centerMenuModal'><div class='avisos'></div></div>
                        <div class='rightMenuModal'></div>
                    </div>
                    <div class="contentModal">
                        <h3>CERTIFICADOS DE CALIDAD</h3>
                        <h3>FOLIO <span id="foliorems"></span></h3>
                    </div>
                    <div class="contentModal">
                        <div id='uplOcs' class='docSection' encabezado='Referencia Órdenes Cliente' folder='ocs'></div>
                        <div id='uplRems' class='docSection' encabezado='Remisiones' folder='remisiones'></div>
                        <div id='uplPesaje' class='docSection' encabezado='Pesaje' folder='pesaje'></div>
                    </div>
                </div>
            </div>
            <header>
                <div id='leftHeader'>
                    <a id="logo" href="../../index.php"><img src="../../img/Logo Skyblue horizontal.png" alt="SkyBlue"/></a>
                    <span id="appName" ><i class='fas fa-truck'></i> Cargas</span>
                </div>
                <div id='rightHeader'>
                    <tab><i class="fas fa-user"></i> <span id="userSession"><?php echo $_SESSION["sessionInfo"]["userName"]; ?></span></tab>
                    <tab><i class="fa fa-clock-o"></i> <span><?php echo $_SESSION["sessionInfo"]["sessionDate"]; ?></tab>
                    <tab><a href='../../login.html'><i class="fa fa-sign-out-alt"></i></a></tab>
                </div>
            </header>
            <div id="filtros"><input type='text' placeholder='Buscar' id="txtfilter"/><button><i class='fas fa-times'></i></button> Cargas Pendientes: <span id="conteoCargas"></span></div>
            <div id="datos">
                <div id="resumen">
                    <table>
                        <thead>
                            <tr>
                                <th>Planta</th>
                                <th>Fecha de Carga</th>
                                <th>Folio</th>
                                <th>Litros</th>
                                <th>Placas</th>
                                <th>Detalle de Envíos</th>
                                <th>Papeleta</th>
                                <th>APT ZK</th>
                                <th>Documentos</th>
                                <th>AMP RALOY</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <!--                <div id="detalle">
                
                                </div>-->
            </div>
            <br style="clear:both;"/>
        </div>

    </body>
</html>

