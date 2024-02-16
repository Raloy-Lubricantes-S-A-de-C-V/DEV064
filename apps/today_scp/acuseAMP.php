<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]["userSession"]) || $_SESSION["sessionInfo"]["userSession"] == "") {
    header('Location: ../../login.html?app=smartRoad');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>COA <?php echo$_GET["folio"]; ?></title>
        <link rel="icon" type="image/png" href="img/icono_camiones.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--QR CODES-->
        <script type="text/javascript" src="../../libs/jquery-qrcode-0.16.0/jquery-qrcode-0.16.0.min.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="css/stylereciboAMP.css">
        <script type="text/javascript" src="js/functions_AMP.js"></script>

        <!--<link href="https://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet">-->
    </head>
    <body>
        <div id='cuerpo'>
            <div id='logoctr'><img src='../../img/logoZK.png' style='height:10mm;'/></div>
            <input id='folio' type='hidden' value='<?php echo $_GET["folio"]; ?>'/>
            <input id='iprs' type='hidden' value='<?php echo $_GET["iprs"]; ?>'/>
            <h2>ACUSE DE EMBARQUE SKYBLUE</h2>
            <h3 id='producto'></h3>
            <div id='info'>
                <table>
                    <tr>
                        <td class='etiqueta'>Folio</td>
                        <td class='valor'><?php echo $_GET["folio"]; ?></td>
                        <td class='etiqueta'>Placas</td>
                        <td class='valor' id='placas'></td>
                    </tr>
                    <tr>
                        <td class='etiqueta'>Fecha de Carga</td>
                        <td class='valor' id='fechaCarga'></td>
                        <td class='etiqueta'>Planta de Carga</td>
                        <td class='valor' id='plantaCarga'></td>
                    </tr>
                    <tr>
                        <td class='etiqueta'>Fecha de Regreso</td>
                        <td class='valor' id='fechaRegreso'></td>
                        <td class='etiqueta'>Planta de Regreso</td>
                        <td class='valor' id='plantaRegreso'></td>
                    </tr>
<!--                    <tr>
                        <td class='etiqueta'>Status Almacén Raloy</td>
                        <td class='valor' id='status'></td>
                        <td class='etiqueta'>Fecha de Ingreso Almacén Raloy</td>
                        <td class='valor' id='fechaAMP'></td>
                        <td class='etiqueta'>Usuario Ingreso Almacén Raloy</td>
                        <td class='valor' id='usuarioAMP'></td>
                    </tr>-->
                </table>
            </div>
            <br/>
            <div id='datos'>
                <table>
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Determinante</th>
                            <th>Destino</th>
                            <th>Producto</th>
                            <th>ETA</th>
                            <th>Ruteo</th>
                            <th>Lote ZK</th>
                            <th>COA</th>
                            <th>Litros</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    <tfoot><tr><th colspan="9">Total Litros</th><th class='ltsTot'></th></tr></tfoot>
                </table>
                <br/>
                <table id='tblRemisiones'>
                    <thead>
                        <tr><th colspan='3'>Documentos de entrega Zar Kruse</th></tr>
                        <tr>
                            <th>Remisión</th>
                            <th>Referencia Pedido</th>
                            <th>Cantidad L</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    <tfoot>
                        <tr><th colspan='2'>Total Litros</th><th class='ltsTot'></th></tr>
                    </tfoot>
                </table>
            </div>
            <br style='clear:both;'/>
            <div id='obs'>
                Recepción:<br/>
                Usuario: <span id='usuario'></span><br/>
                Fecha y Hora: <span id='fechahoraValAMP'></span><br/>

            </div>
            <hr/>
            <div id='firma'>
                <span>Este documento tiene los mismos efectos que los recibos en soporte de papel sellados ya que ha sido firmado electrónicamente mediante el portal Intranet Zar Kruse. www.skyblue.mx/intranetZK/index.php.</span><br/>
                <div id='selloqr'></div><br/>
                <span id='selloAMP'></span>
            </div>
            <hr/>
        </div>
    </body>
</html>


