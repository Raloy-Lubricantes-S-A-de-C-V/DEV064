<?php ini_set('default_charset', 'UTF-8');?>
<!DOCTYPE html>
<html>
    <head>
        <title>DASHBOARD | VENTAS ZK</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="images/skico.ico">
        <!--jQuery-->
        <script type="text/javascript" src="libs/jquery-1.11.3.min.js"></script>

        <!--mdl-->
        <link rel="stylesheet" media="screen" type="text/css" href="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.css">
        <script src="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.js"></script>

        <!--Morris (Gráficas)-->
        <!--        <link rel="stylesheet" href="libs/morris.js-0.5.1/morris.css">
                <script type="text/javascript" src="libs/morris.js-0.5.1/raphael-min.js"></script>
                <script type="text/javascript" src="libs/morris.js-0.5.1/morris.min.js"></script>-->

        <!--CanvasJs Gráficas apilables-->
        <!--<script type='text/javascript' src='libs/canvasjs-1.9.10/jquery.canvasjs.min.js'></script>-->

        <!--Autonumeric-->
        <script type="text/javascript" src="libs/autonumeric/numberformatter/libs/jshashtable-3.0.js"></script>
        <script type="text/javascript" src="libs/autonumeric/numberformatter/src/numberformatter.js"></script>
        <script type="text/javascript" src="libs/autonumeric/autoNumeric.js"></script>

        <!--DataTables (Tablas)-->
        <link rel="stylesheet" href="libs/DataTables-1.10.10/media/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="libs/DataTables-1.10.10/extensions/Buttons/css/buttons.dataTables.min.css">
        <link rel="stylesheet" href="libs/DataTables-1.10.10/extensions/FixedHeader/css/fixedHeader.dataTables.min.css">

        <script type="text/javascript" src="libs/DataTables-1.10.10/media/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/extrasrequeridos/jszip.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/extrasrequeridos/pdfmake.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/extrasrequeridos/vfs_fonts.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/Buttons/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/Buttons/js/buttons.html5.min.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/Buttons/js/buttons.print.js"></script>
        <script type="text/javascript" src="libs/DataTables-1.10.10/extensions/FixedHeader/js/dataTables.fixedHeader.min.js"></script>

        <!--jQueryPivot (Tablas pivote)-->
        <script type="text/javascript" src="libs/jqueryPivot/lib.js"></script>
        <script type="text/javascript" src="libs/jqueryPivot/jquery.pivot.js"></script>
        <link rel="stylesheet" href="libs/jqueryPivot/stylesheet.css">

        <!-- GOOGLE CHARTS-->
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="css/styles.css">
        <script type="text/javascript" src="js/ventasZK.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                zk_ventasxFam(<?php echo $_GET["id"]; ?>);//con detalle de fechas
            });
        </script>

    </head>
    <body>

        <input id="fec1" type="hidden" value="<?php echo $_GET["fec1"]; ?>"/>
        <input id="fec2" type="hidden" value="<?php echo $_GET["fec2"]; ?>"/>
        <input id="uSe" type="hidden" value="<?php echo $_POST["userSesion"]; ?>"/>

        <div id="cuerpo">

            <div class="topBar">
                <div>
                    <span class="inline" style="text-align:left;">VENTAS DE SKYBLUE ZAR/KRUSE</span>
                </div>

            </div>


            <div class="title1" style='font-size:15pt;font-weight:bold;'>
                Del <span id="fec1Sp"></span> Al <span id="fec2Sp"></span>
            </div>

            <div id='totalLts' class='title'>VENTAS POR PLANTA Y PRESENTACI&Oacute;N</div>
            <div id="contenido" class="contenido">
                <div id='totalLts' class='title2'>LITROS</div>
                <div id="res" class="pivotContainer">
                    <table class="pivot"></table>
                </div>
                <br style='clear:both;'/>
                
                <div class='title2'>USD</div>
                <div id="res_USD" class="pivotContainer">
                    <table class="pivot"></table>
                </div>
                <br style='clear:both;'/>
                <div class='title2'>DEVOLUCIONES</div>
                <div id="tabladevs"></div>
                <br style='clear:both;'/>
                <div class='title2'>RESUMEN DE PRECIO REAL</div>
                <div>
                    <table id="tblResumenIncome">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th>Lts</th>
                                <th>Ventas USD</th>
                                <th>NC USD</th>
                                <th>Real USD</th>
                                <th>Ventas USD/L</th>
                                <th>NC USD/L</th>
                                <th>Real USD/L</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot></tfoot>
                    </table>
                </div>
                <br style='clear:both;'/>
                <hr/>

                <div class='title'>CHART PLANTAS</div>
                <div style="width:100%;">
                    <div id="chart_plantas" class="charts chartLeft"></div>
                    <div id="chart_plantas_perc" class="charts chartRight"></div>
                    <br style="clear:both;"/>
                </div>
                <br style='clear:both;'/>

                <div class='title'>CHART PRESENTACIONES</div>
                <div style="width:100%;">
                    <div id="chart_presentaciones" class="charts chartLeft"></div>
                    <div id="chart_presentaciones_perc" class="charts chartRight"></div>
                    <br style="clear:both;"/>
                </div>
                <br style='clear:both;'/>

                <div class='title'>CHART IM&Aacute;GENES</div>
                <div style="width:100%;">
                    <div id="chart_imagenes" class="charts chartLeft"></div>
                    <div id="chart_imagenes_perc" class="charts chartRight"></div>
                    <br style="clear:both;"/>
                </div>
                <br style='clear:both;'/>
                
                <div class='title'>ANAL&Iacute;TICO DE VENTAS (REMISIONES RALOY)</div>
                <select id='selAnalitico'>
                    <option value='NomCliente'>Clientes</option>
                    <option value='Zona'>&Aacute;reas</option>
                    <option value='CDV'>Presentaciones</option>
                </select>
                <div id='tblAnContainer' style="width:100%;">
                    
                </div>
                <br style='clear:both;'/>


                <!--<div id='percVal'></div>-->
                <!--                <div>
                                    <div  class='donutContainer'>
                                        <div class="title">
                                            VENTAS POR PLANTA
                                        </div>
                                        <div id="plantaChart" class="chart">
                
                                        </div> 
                                    </div>
                
                                    <div  class='donutContainer'>
                                        <div class="title">
                                            VENTAS POR IMAGEN
                                        </div>
                                        <div id="imgChart" class="chart">
                
                                        </div> 
                
                                    </div>
                                    <div class='donutContainer'>
                                        <div class="title">
                                            VENTAS POR PRESENTACIÓN
                                        </div>
                                        <div id="ptnChart" class="chart">
                                            <table class="pivot"></table>
                                        </div> 
                
                                    </div>
                                    <br style='clear:both;'/>
                                </div>-->
            </div>
        </div>
    </body>
</html>