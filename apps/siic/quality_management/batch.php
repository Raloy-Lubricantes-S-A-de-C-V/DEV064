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
        <script type="text/javascript" src="js/functions_calidad_lang.js"></script>
        <script type="text/javascript" src="js/functions_calidad.js"></script>

    </head>
    <body>
        <span id='lang' style='display:none;'><?php
            if ($_GET["lang"]) {
                echo $_GET["lang"];
            } else {
                echo "esp";
            }
            ?>
        </span>
        <input id='nl' type="hidden" value="<?php
        if ($_GET["f"]) {
            echo $_GET["f"];
        }
        ?>" 
               />
        <div id='cuerpo'>
            <div id='headDiv'><img src='images/cabecera1.jpg'/></div>
            <div id='busqueda'  style="text-align:left">
                <div class='subt1 langC'>CONSULTA DE CERTIFICADO DE ANÁLISIS</div>
                <hr/>
                <div id="datos">
                    <!--                    <div id='divConsulta'>
                                            <span class='langC'><b>Lote</b></span>
                                            <br/>
                                            <br/>
                                            <input type='text' id='numLoteIn'/>
                                            <button id='consultar'><i class='fa fa-search'></i></button>
                                        </div>-->

                        <input type='hidden' id='numLote'/>
                        <table>
                            <thead>
                                <tr>
                                    <th colspan='2'>Datos del lote</th>
                                    <th colspan='2'>Datos del análisis:</th>
                                    <th>Referencias normativas:</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Número de Lote:</td>
                                    <td><span id='numLoteT'></span></td>
                                    <td class='langC'>Fecha de Ingreso:</td>
                                    <td class='tdValor' id='f1'></td>
                                    <td>ISO 22241</td>
                                </tr>
                                <tr>
                                    <td>Tamaño de Lote:</td>
                                    <td><span id='tamaLoteT'></span></td>
                                    <td class='langC'>Fecha de análisis:</td>
                                    <td class='tdValor' id='f2'></td>
                                    <td>NMX-D-316-IMNC-2016</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td class='langC'>Analista:</td>
                                    <td class='tdValor' id='an'></td>
                                </tr> 
                            </tbody>

                        </table>
                </div>
                <br style='clear:both;'/>
            </div>
            <hr/>
            <table id="tblResults">
                <thead>
                    <tr><th colspan='5' class='langC'>RESULTADOS DEL ANÁLISIS</th></tr>
                    <tr>
                        <th rowspan='2' class='langC'>Parámetro</th>
                        <th rowspan='2' class='langC'>Unidad</th>
                        <th colspan='2' class='langC'>Límites</th>
                        <th rowspan='2' class='langC'>Resultado</th>
                    </tr>
                    <tr>
                        <th class='langC'>Min.</th>
                        <th class='langC'>Máx.</th>
                    </tr>
                </thead>
                <tbody  style='text-align: center;'>

                    <tr>
                        <td class='langC'>Contenido de Urea</td>
                        <td>%(m/m)</td>
                        <td class='numeric'>31.80</td>
                        <td class='numeric'>33.20</td>
                        <td class='tdValor numeric' id='ureaVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Densidad a 20°C</td>
                        <td>kg/m<sup>3</sup></td>
                        <td class='numeric'>1087.00</td>
                        <td class='numeric'>1093.00</td>
                        <td class='tdValor numeric' id='densVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Índice de Refracción a 20°C</td>
                        <td>--</td>
                        <td class='numeric'>1.3814</td>
                        <td class='numeric'>1.3843</td>
                        <td class='tdValor numeric' id='IrVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Alcalinidad como NH<sub>3</sub></td>
                        <td>%(m/m)</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td id="NH3Val"  class='tdValor numeric'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Biuret</td>
                        <td>%(m/m)</td>
                        <td>--</td>
                        <td class='numeric'>0.30</td>
                        <td class='tdValor numeric' id='biuretVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Aldehídos</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>5.00</td>
                        <td class='tdValor numeric' id='aldVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Materia insoluble</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>20.00</td>
                        <td class='tdValor numeric' id='insolVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Fosfatos (PO<sub>4</sub>)</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='PO4Val'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Calcio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='CaVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Hierro</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='HeVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Cobre</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td class='tdValor numeric' id='CuVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Zinc</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td class='tdValor numeric' id='ZVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Cromo</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td class='tdValor numeric' id='CrVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Níquel</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.20</td>
                        <td class='tdValor numeric' id='NiVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Aluminio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='AlVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Magnesio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='MgVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Sodio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='NaVal'></td>
                    </tr>

                    <tr>
                        <td class='tdLabel langC'>Potasio</td>
                        <td>mg/kg</td>
                        <td>--</td>
                        <td class='numeric'>0.50</td>
                        <td class='tdValor numeric' id='KVal'></td>
                    </tr>

                    <tr>
                        <td class='langC'>Identidad</td>
                        <td>--</td>
                        <td colspan='2'>Idéntico al estándar</td>
                        <td class='tdValor' id='identidadVal'></td>
                    </tr>

                </tbody>


            </table>
            <hr/>
            <span class='langC'>Documento generado electrónicamente bajo responsabilidad de Raloy Lubricantes, S.A. de C.V. Av. Del Convento 111, Parque Industrial, Tianguistenco, Estado de México, C.P. 52600. <br/>ÚNICAMENTE PARA CONSULTA. No imprimir, distribuir o modificar sin autorización de quien lo emite</span>
            <hr/>
        </div>
    </body>
</html>

