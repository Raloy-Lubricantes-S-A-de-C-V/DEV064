<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]["userSession"]) || $_SESSION["sessionInfo"]["userSession"] == "") {
    header('Location: ../../login.html');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo$_GET["folio"]; ?></title>
        <link rel="icon" type="image/png" href="img/icono_camiones.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

       <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--Propias-->
        <link rel="stylesheet" href="css/styleCarga.css">
        <script type="text/javascript" src="js/fSolCarga.js"></script>

    </head>
    <body>
        <div id='options'>
            <button id='print'><i class='fa fa-print'></i></button>
            <button id='sendEmail'><i class='fa fa-envelope'></i></button>
        </div>
        <div id='hojaSolicitud'>
            <div id='encabezado'>
                <div class='logo'><img src='img/RaloyHorizontalPNG.png'/></div>
                    <!--<img class="toSend" src='cid:logo_Raloy'/></div>-->

                <div id='folioContainer'>
                    FOLIO No. <span id='folio'><?php echo$_GET["folio"]; ?></span>
                </div>
                <div style='clear:both;'></div>
            </div>
            <div id='title'>SOLICITUD DE CARGA DE AUTOTANQUE</div>
            <div class='datosCarga'>
                <table>
                    <tr>
                        <td class='label'>Placas de la unidad:</td><td class='valor' id='placasUnidad'></td><td class='spacer'></td><td class='label'>Capacidad (Utilización):</td><td  class='valor'><span id='capacidadUnidad'></span> (<span id='utilizUnid'></span>)</td>
                    </tr>

                    <tr>
                        <td class='label'>Fecha de carga:</td><td class='valor' id='fechaCarga'></td><td class='spacer'></td><td class='label'>De planta:</td><td  class='valor' id='plantaCarga'></td>
                    </tr>
                    <tr>
                        <td class='label'>Fecha de regreso:</td><td class='valor' id='fechaRegreso'></td><td class='spacer'></td><td class='label'>A planta:</td><td  class='valor' id='plantaRegreso'></td>
                    </tr>
                </table>
            </div>
            <div id='bodyp'>
                <div id='datosPapeleta'>
                    <table>
                        <thead>
                            <tr><th>Producto</th><th>Cant. (L)</th><th>Cliente</th><th>Destino</th><th>Fecha Entrega</th><th>Pedido</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total Lts.</th><th id="totalLts"></th><th  colspan="6"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div id='foot'>
                <span style='font-weight:bold;'>Observaciones:</span><span id='obs'></span><hr/><br/>
                Documento Generado electrónicamente.
                 <span>Fecha de solicitud: <span id='fechaSolicitud'></span></span>
                 <span style='float:right;'>Solicitante: <span id='solicitante'></span></span> 
            </div>
        </div>

    </body>
</html>
