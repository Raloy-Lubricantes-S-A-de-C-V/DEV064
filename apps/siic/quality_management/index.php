<!DOCTYPE html>
<html>
    <head>
        <title>QUALITY MANAGEMENT</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="images/icon.png">

        <!--jQuery-->
        <script type="text/javascript" src="libs/jquery-1.11.3.min.js"></script>

        <!--mdl-->
        <link rel="stylesheet" media="screen" type="text/css" href="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.css">
        <script src="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.js"></script>

        <!--jQuery UI-->
        <link rel="stylesheet" href="libs/jquery-ui-1.11.4.custom/jquery-ui.min.css">
        <link rel="stylesheet" href="libs/jquery-ui-1.11.4.custom/jquery-ui.structure.min.css">
        <link rel="stylesheet" href="libs/jquery-ui-1.11.4.custom/jquery-ui.theme.min.css">
        <script type="text/javascript" src="libs/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
        
        <!--Morris (Gráficas)-->
        <link rel="stylesheet" href="libs/morris.js-0.5.1/morris.css">
        <script type="text/javascript" src="libs/morris.js-0.5.1/raphael-min.js"></script>
        <script type="text/javascript" src="libs/morris.js-0.5.1/morris.min.js"></script>

        <!--Autonumeric-->
        <script type="text/javascript" src="libs/autonumeric/numberformatter/libs/jshashtable-3.0.js"></script>
        <script type="text/javascript" src="libs/autonumeric/numberformatter/src/numberformatter.js"></script>
        <script type="text/javascript" src="libs/autonumeric/autoNumeric.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="libs/font-awesome-4.6.3/css/font-awesome.css">

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

        <!--        jsPDF
                <script type="text/javascript" src="libs/jspdf.js"/>  -->

        <!--Propias-->
        <link rel="stylesheet" href="css/styles_calidad.css">
        <!--<script type="text/javascript" src="libs/html2canvas.js"></script>-->
        <script type="text/javascript" src="js/functions_calidad_lang.js"></script>
        <script type="text/javascript" src="js/functions_index.js"></script>

    </head>
    <body>
        <span id='lang' style='display:none;'><?php
            if ($_GET["lang"]) {
                echo $_GET["lang"];
            } else {
                echo "esp";
            }
            ?></span>
        <div id='cuerpo'>
            <div id='headDiv'><img src='images/cabecera1.jpg'/></div>
            <div id='busqueda'  style="text-align:left">
                <div class='subt1 langC'>SKYBLUE QUALITY MANAGEMENT</div>
                <hr/>
                <div id="allOpts">
                    <div id='divLogin'>
                        <b>Login</b>
                        <table>
                            <tr>
                                <td>email:</td><td><input type="text"/></td>
                            </tr>
                            <tr>
                                <td>Password:</td><td><input type="password"/></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align:right;"><button id="loginBtn" class="buttonA">Login</button></td>
                            </tr>
                        </table>
                    </div>
                    <div id='divParams'>
                        <dl style="display:none;">
                            <dt>Tests results</dt>
                            <dd><button class='buttonA' id='batchBtn'>Batch Number</button></dd>
                            <dd><button class='buttonA' id='datesBtn'>Dates</button></dd>
                            <dt>Documents</dt>
                            <dd><button class='buttonA'>Plan of Control</button></dd>
                        </dl>
                    </div>
                    <div id='divOptions'>
                        
                        <div id='batchOpts' class='hideable'>
                            <span class='langC'><b>Lote</b></span>
                            <br/>
                            <br/>
                            <select id='numLote'></select>
                            <button id='consultar'><i class='fa fa-search'></i></button>
                        </div>
                        
                        <div id='datesOpts' class='hideable'>
                            <table>
                                <tr>
                                    <td><span class='langC'><b>From:</b></span></td>
                                    <td><input type='text' id='fi'/></td>
                                </tr>
                                <tr>
                                    <td><span class='langC'><b>To:</b></span></td>
                                    <td><input type='text' id='ff'/></td>
                                </tr>
                                <tr>
                                    <td colspan='2' style='text-align:right;'><button id='searchByDate'><i class='fa fa-search'></i></button></td>
                                </tr>
                            </table>
                            
                        </div>
                        
                    </div>
                </div>
                <br style='clear:both;'/>
            </div>
            <hr/>
        </div>
    </body>
</html>

