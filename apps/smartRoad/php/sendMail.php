<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["nomUsuario"]) || $_SESSION["nomUsuario"] == "") {
    header("Location: login.php");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>CARGA SKYBLUE</title>
        <link rel="icon" type="image/png" href="img/icono_camiones.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="libs/font-awesome-4.6.3/css/font-awesome.min.css">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.debug.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="css/styleCarga.css">
        <script type="text/javascript" src="js/fSolCarga.js"></script>
        <style type='text/css'>

            body{
                text-align:center;
                width:100%;
                padding:0;
                margin:0;
                /*font-family: 'Inconsolata', monospace;*/
            }
            #hojaSolicitud{
                width:180mm;
                padding:2mm 0;
                font-size:10.5pt;
                margin:0 auto;
            }
            #hojaSolicitud #encabezado{
                width:100%;
                height:18mm;
            }
            #encabezado .logo img{
                float:left;
                height:18mm;
            }

            #encabezado .title{
                float:right;
                display:block;
                padding-top:5mm;
                width:70%;
                text-align:center;
                font-size:12pt;
                font-weight:bold;

            }
            #folioContainer{
                width:100%; 
                text-align:right;
                font-weight:bold;
            }
            #folio{
                width:40mm;
                font-size:12pt;
                color:red;
                text-align:center;
                border:solid 1px #333;
                margin:10px;
                padding:1mm 2.5mm;
                display:inline-block;
            }
            .datosCarga{
                width:100%;
                background:#fff;
                text-align:left;
                display:block;
            }
            .datosCarga table{
                width:100%;
                font-size:10.5pt;
                border-collapse:collapse;
                border:none;
            }
            .datosCarga table td{
                height:3mm;
                padding-top:3mm;
            }
            .datosCarga table .label{
                border-bottom:none !important;
                width:23mm;
                font-size:10pt;
                padding-left:7mm;
            }
            .datosCarga table .valor{
                width:35mm;
                border-bottom:solid 1px #17206B;
            }    
            .datosCarga table .spacer{
                width:10mm;

            }

            #datosPapeleta{
                width:100%;
                text-align:center;
                margin:0 auto;
                display:block;
            }
            #datosPapeleta table{
                width:100%;
                margin:0 auto;
                border-collapse: collapse;
                border:solid 1px grey;
            }
            #datosPapeleta table tr th {
                border:solid 1px grey;
                padding:7px;
                font-size:10pt;
            }
            #datosPapeleta table tr td {
                border:solid 1px grey;
                padding:7px;
                font-size:9pt;
            }
            #datosPapeleta table tr td:last-child {
                min-width:80px;
            }
            .numeric{
                text-align:right;
            }
            .container100{
                width:100%;
                text-align:left;
                font-weight:bold;
                font-size:13pt;
                box-sizing:border-box;
                padding:5px;
                background:#f2f2f2;
            }
            .container100 div{
                width:40%;
                float:right;
                text-align:right;
            }
            #options{
                position:absolute;
                margin:5px;
                box-sizing:border-box;
                width:80%;
                padding:0;
                text-align:right;
            }
            #options button{
                background:rgba(23,32,107,0.5);
                color:#fff;
                border:none;
                padding:5px 10px;
                cursor:pointer;
            }
            #options button:hover{
                background:rgba(23,32,107,0.9);
                color:#fff;
                border:none;
                padding:5px 10px;
            }
        </style>
        <!--<link href="https://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet">-->
    </head>
    <body>
        <div id='options'>
            <button id='print'><i class='fa fa-print'></i></button>
            <button id='sendEmail'><i class='fa fa-envelope'></i></button>
        </div>
        <div id='hojaSolicitud'>
            <div id='encabezado'>
                <div class='logo'><img src='cid:logo_Raloy'/></div>
                <h1>SOLICITUD DE CARGA DE AUTOTANQUE</h1>
                <br style='clear:both;'/>
            </div>
            <div id='folioContainer'>
                FOLIO No. <span id='folio'><?php echo$_GET["folio"]; ?></span>
            </div>
            <br/>
            <div class='datosCarga'>
                <table>
                    <tr>
                        <td class='label'>Fecha de solicitud:</td><td class='valor' id='fechaSolicitud'>15/01/2018</td><td class='spacer'></td><td class='label'>Solicitante:</td><td  class='valor' id='solicitante'>Héctor Yescas Orozco</td>
                    </tr>
                    <tr>
                        <td class='label'>Placas de la unidad:</td><td class='valor' id='placasUnidad'></td><td class='spacer'></td><td class='label'>Capacidad:</td><td  class='valor' id='capacidadUnidad'>33,000 L </td>
                    </tr>

                    <tr>
                        <td class='label'>Fecha de carga:</td><td class='valor' id='fechaCarga'></td><td class='spacer'></td><td class='label'>De planta:</td><td  class='valor' id='plantaCarga'></td>
                    </tr>
                    <tr>
                        <td class='label'>Fecha de regreso:</td><td class='valor' id='fechaRegreso'></td><td class='spacer'></td><td class='label'>A planta:</td><td  class='valor' id='plantaRegreso'></td>
                    </tr>
                </table>
            </div>
            <br/>
            <div id='datosPapeleta'>
                <table>
                    <thead>
                        <tr><th>Fecha Entrega</th><th>Cliente</th><th>Pedido</th><th>Destino</th><th>Producto</th><th>Cant. (L)</th></tr>
                    </thead>
                    <tbody>

                    </tbody>
                    <tfoot></tfoot>
                </table>
                <br/>

            </div>
            <br/>
            <div class='container100'>TOTAL LTS. <div id='totalLts'></div></div>
            <div class='container100'>UTILIZACIÓN DE UNIDAD. <div id='utilizUnid'></div></div>
            <br/><br/>
            <div>
                <span style='font-weight:bold;text-align:left;width:100%;'>Observaciones</span>
                <div style='border:solid 1px grey;width:100%;height:15mm;'></div>
            </div>
            <br/>
            <hr/>
            <div class='datosCarga'>
                <h3>Datos de la carga</h3>
                <table>
                    <tr>
                        <td class='label'>Lote ZK:</td><td class='valor' id='lote'></td>
                        <td class='label'>Remisiones ZK:</td><td class='valor' id='remisionesZK'></td>
                    </tr>
                    <tr>
                        <td class='label'>Sellos Escotilla:</td><td class='valor' id='sellosEscotilla'></td>
                        <td class='label'>Sellos Descarga:</td><td class='valor' id='sellosDescarga'></td>
                    </tr>
                    <tr>
                        <td class='label'>Num. Embarque Raloy:</td><td class='valor' id='numEnvio'></td>
                        <td class='label'>Peso Neto:</td><td  class='valor' id='pesoNeto'></td>
                    </tr>
                    <tr>
                        <td class='label'>Responsable:</td><td  class='valor'></td>
                        <td></td><td></td>
                    </tr>
                </table>
                <h3>Datos de calidad</h3>
                <table>
                    <tr>
                        <td class='label'>Densidad a 20°C, kg/m3</td><td class='valor' id='densidad'></td>
                        <td class='label'>Contenido de urea, %(m/m):</td><td class='valor' id='concentracion'></td>
                    </tr>
                    <tr>
                        <td class='label'>Apariencia, S/U:</td><td class='valor' id='apariencia'></td>
                        <td></td><td></td>
                    </tr>

                </table>
            </div>
        </div>
    </body>
</html>
