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

        <!--Propias-->
        <link rel="stylesheet" href="css/styleCertificadoCalidad.css">
        <script type="text/javascript" src="js/fCertificadoCalidad.js"></script>

        <!--<link href="https://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet">-->
    </head>
    <body>
        <div id='cuerpo'>
            <input id='folio' type='hidden' value='<?php echo$_GET["folio"]; ?>'/>
            <input id='iprs' type='hidden' value='<?php echo$_GET["iprs"]; ?>'/>
            <h2>CERTIFICADO DE CALIDAD</h2>
            <h3 id='producto'></h3>
            <div id='info'>
                <b>Lote:</b> <span id='lote'></span><br/>
                <b>Cantidad:</b> <span id='qtyL'></span><br/>
                <b>Emisión:</b> <span id='fechaHoraCertificado'></span><br/>
                <b>Pedido Interno:</b> <span><span id='pedInt'></span></span>
            </div>
            <br/>
            <div id='datos'>
                <table>
                    <thead>
                        <tr>
                            <th>Propiedades</th>
                            <th>Método</th>
                            <th colspan='2'>Especificación</th>
                            <th>Resultado</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th>Mín.</th>
                            <th>Máx.</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
<!--                        <tr>
                            <td>Densidad a 20°C,Kg/m3</td>
                            <td>ASTM D4052-16 (ISO 12185-1996)</td>
                            <td>1087.0</td>
                            <td>1093.0</td>
                            <td id='densidad'></td>
                        </tr>
                        <tr>
                            <td>Contenido de urea,%(m/m)</td>
                            <td>ISO 22241-2 Anexo C:2006</td>
                            <td>31.80</td>
                            <td>33.20</td>
                            <td id='concentracion'></td>
                        </tr>
                        <tr>
                            <td>Índice de Refracción a 20°C</td>
                            <td>ISO 22241-2 Anexo C:2006</td>
                            <td>1.3814</td>
                            <td>1.3843</td>
                            <td id='indicer'></td>
                        </tr>
                        <tr>
                            <td>Identidad</td>
                            <td>ISO 22241-2 Anexo J</td>
                            <td colspan='2'>Idéntico a Referencia</td>
                            <td id='apariencia'></td>
                        </tr>-->
<!--                        <tr>
                            <td>Infrarrojo contra patrón, S/U</td>
                            <td>IT-08-93</td>
                            <td colspan='3'>Idéntica a la referencia</td>
                            <td id='infrarrojo'></td>
                        </tr>-->

                    </tbody>
                </table>
                
                <div id='legend'>En conformidad con ISO 22241:2006 / NMX-D-316-IMNC-2016</div>
            </div>
            <br style='clear:both;'/>
            <div id='obs'>
                Observaciones:<br/>
                Sello(s) fijo(s): <span id='sellosFijos'></span><br/>
                Sello(s) Escotila: <span id='sellosEscot'></span><br/>
                Sello(s) Descarga: <span id='sellosDescar'></span><br/>
                Placas: <span id='placas'></span><br/><br/>
                
            </div>
            <br/>
            <div id='firma'><span>Documento generado electrónicamente.</span></div>
        </div>
    </body>
</html>
