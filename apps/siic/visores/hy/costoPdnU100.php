<!DOCTYPE html>
<html>
    <head>
        <title>Costos de U100 Utilizada en Producción</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="images/skico.ico">

        <!--jQuery-->
        <script type="text/javascript" src="libs/jquery-1.11.3.min.js"></script>

        <!--mdl-->
        <link rel="stylesheet" media="screen" type="text/css" href="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.css">
        <script src="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.js"></script>

        <!--Morris (Gráficas)-->
        <link rel="stylesheet" href="libs/morris.js-0.5.1/morris.css">
        <script type="text/javascript" src="libs/morris.js-0.5.1/raphael-min.js"></script>
        <script type="text/javascript" src="libs/morris.js-0.5.1/morris.min.js"></script>
        
        
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


        <!--Propias-->
        <link rel="stylesheet" href="css/styles.css">
        <script type="text/javascript" src="js/functionsCostoPdnU100.js"></script>
    </head>
    <body>
        <header class="mdl-layout__header is-casting-shadow" style="width:100% !important;">
            <div class="mdl-layout__header-row">
                <span class="mdl-layout-title">SkyBlue</span>
                <div class="mdl-layout-spacer"></div>
                <span class="mdl-layout-title mdl-layout--large-screen-only">6 Digits Code: 310210</span>
            </div>
        </header>
        <div id="cuerpo">
            <input id="fec1" type="hidden" value="<?php echo $_GET["fec1"]; ?>"/>
            <input id="fec2" type="hidden" value="<?php echo $_GET["fec2"]; ?>"/>

            <!--            <div class="chartContainer">
            
                                            <div id="salesChart" class="chart">
                            
                                            </div> 
            
                        </div>-->
            <div class="title">
                COSTO DE U-100 UTILIZADA EN PRODUCCIÓN
                <div id='tiposdeCambio'></div>
                <br style='clear:both'/>
            </div>
            <div id='loadingDiv'><img src='images/loading.gif' id="loadingImg"/><br/><span id="loadingMsg" style="font-size:10pt;color:#444;">Recopilando información. <br/>Este proceso puede tardar, dependiendo de la cantidad de datos y la velocidad de la conexión a internet</span></div>
            <div style="clear:both"></div>
            <div class="tableContainer" style="height:130px;">
                <div id="titleTipo"></div>
                <table id="tblTipo" class="compact">
                    <tfoot><tr><th id="tfTipo"></th></tr></tfoot>
                </table>
            </div>
            <div class="tableContainer" style="height:240px;">
                <div id="titleTipoNivel"></div>
                <table id="tblTipoNivel" class="compact">
                    <tfoot><tr><th id="tfTipoNivel" colspan="7"></th></tr></tfoot>
                </table>
            </div>
            <div class="tableContainer" style="height:240px;">
                <div id="titleProvTipoNivel"></div>
                <table id="tblProvTipoNivel" class="compact">
                    <tfoot><tr><th id="tfProvTipoNivel" colspan="7"></th></tr></tfoot>
                </table>
            </div>
            <div class="tableContainer">
                <div id="titleDetalle"></div>
                <table id="tblDetalle" class="compact">
                    <tfoot><tr><th id="tfDetalle" colspan="7"></th></tr></tfoot>
                </table>
            </div>
        </div>
    </body>
</html>
