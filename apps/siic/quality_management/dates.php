<!DOCTYPE html>
<html>
    <head>
        <title>ANÁLISIS DE CALIDAD ZAR/KRUSE</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="images/icon.png">

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
        <script type="text/javascript" src="js/functions_calidad.js"></script>

    </head>
    <body>
        <div id='cuerpo'>
            <h2>SKYBLUE TEST RESULTS FROM <span id='fi'><?php echo $_GET["fi"];?></span> to <span id='ff'><?php echo $_GET["ff"];?></span></h2>
            <div id='headDiv'><img src='images/cabecera1.jpg'/></div>
            <div id='busqueda'  style="text-align:left">
                <div class='subt1'>CONSULTA DE ANÁLISIS DE CALIDAD</div>
                <hr/>
                <div>
                    <div id='divConsulta'>
                        <b>Lote</b>
                        <br/>
                        <br/>
                        <input type='text' id='numLote'/>
                        <button id='consultar'><i class='fa fa-search'></i></button>
                    </div>
                    <div id='divDatosGrales'>
                        <table>
                            <tr>
                                <td>Ingreso de Muestra:</td>
                                <td class='tdValor' id='f1'></td>
                            </tr>
                            <tr>
                                <td>Análisis de Muestra:</td>
                                <td class='tdValor' id='f2'></td>
                            </tr>
                            <tr>
                                <td>Analista:</td>
                                <td class='tdValor' id='an'></td>
                            </tr>
                        </table>
                    </div>
                    <div id='divReferenicias'>
                        <b>Referencias Normativas</b>
                        <ul><li>ISO 22241-1:2006</li><li>NMX-D-316-IMNC-2016</li></ul>
                    </div>
                </div>
                <br style='clear:both;'/>
            </div>
            <hr/>
            <table id="tblResults">
                <thead>
                    <tr><th colspan='5'>RESULTADOS DEL ANÁLISIS</th></tr>
                    <tr>
                        <th rowspan='2'>Parámetros</th>
                        <th rowspan='2'>Unidades</th>
                        <th colspan='2'>Límites</th>
                        <th rowspan='2'>Resultados</th>
                    </tr>
                    <tr>
                        <th>Min.</th>
                        <th>Máx.</th>
                    </tr>
                </thead>
                <tbody  style='text-align: center;'>
                    
                    <tr>
                        <td>Contenido de Urea</td>
                        <td>%(m/m)</td>
                        <td class='numeric'>31.80</td>
                        <td class='numeric'>33.20</td>
                        <td class='tdValor numeric' id='ureaVal'></td>
                    </tr>

                    <tr>
                        <td>Densidad a 20°C</td>
                        <td>kg/m<sup>3</sup></td>
                        <td class='numeric'>1087.00</td>
                        <td class='numeric'>1093.00</td>
                        <td class='tdValor numeric' id='densVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Índice de Refracción a 20°C</td>
                        <td>--</td>
                        <td class='numeric'>1.3814</td>
                        <td class='numeric'>1.3843</td>
                        <td class='tdValor numeric' id='IrVal'></td>
                    </tr>

                    <tr>
                        <td>Alcalinidad como NH<sub>3</sub></td>
                        <td>%(m/m)</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td id="NH3Val"  class='tdValor numeric'></td>
                    </tr>
                    
                    <tr>
                        <td>Biuret</td>
                        <td>%(m/m)</td>
                        <td>--</td>
                        <td class='numeric'>0.30</td>
                        <td class='tdValor numeric' id='biuretVal'></td>
                    </tr>

                    <tr>
                        <td>Aldehídos</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>5.00</td>
                        <td class='tdValor numeric' id='aldVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Materia insoluble</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>20.00</td>
                        <td class='tdValor numeric' id='insolVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Fosfatos (PO<sub>4</sub>)</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='PO4Val'></td>
                    </tr>
                    
                    <tr>
                        <td>Calcio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='CaVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Hierro</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='HeVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Cobre</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td class='tdValor numeric' id='CuVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Zinc</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td class='tdValor numeric' id='ZVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Cromo</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td class='tdValor numeric' id='CrVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Níquel</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td class='tdValor numeric' id='NiVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Aluminio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='AlVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Magnesio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='MgVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Sodio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='NaVal'></td>
                    </tr>
                    
                    <tr>
                        <td class='tdLabel'>Potasio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='KVal'></td>
                    </tr>
                    
                    <tr>
                        <td>Identidad</td>
                        <td>--</td>
                        <td colspan='2'>Idéntico al estándar</td>
                        <td class='tdValor' id='identidadVal'></td>
                    </tr>
                    
                </tbody>


            </table>
        </div>
    </body>
</html>

