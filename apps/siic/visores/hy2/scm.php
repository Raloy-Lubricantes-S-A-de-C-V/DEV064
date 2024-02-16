<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="Reportes del SIIC">
        
        <title>Abasto U-100</title>
        
        <link rel="shortcut icon" href="images/raloy.ico">
        
        <!--jQuery-->
        <script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
        
        <!--mdl-->
        <link rel="stylesheet" media="screen" type="text/css" href="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.css">
        <script src="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.js"></script>
        
        <!--fonts-->
        <link href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=es" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        
        <!--DataTables (Tablas)-->
        <link rel="stylesheet" href="../hy/libs/DataTables-1.10.10/media/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="../hy/libs/DataTables-1.10.10/extensions/Buttons/css/buttons.dataTables.min.css">
        <link rel="stylesheet" href="../hy/libs/DataTables-1.10.10/extensions/FixedHeader/css/fixedHeader.dataTables.min.css">

        <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/media/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/extrasrequeridos/jszip.min.js"></script>
        <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/extrasrequeridos/pdfmake.min.js"></script>
        <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/extrasrequeridos/vfs_fonts.js"></script>
        <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/Buttons/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/Buttons/js/buttons.html5.min.js"></script>
        <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/Buttons/js/buttons.print.js"></script>
        <script type="text/javascript" src="../hy/libs/DataTables-1.10.10/extensions/FixedHeader/js/dataTables.fixedHeader.min.js"></script>
        
        
        <script type="text/javascript" src="js/gChart/jquery.plugin.min.js"></script> 
        <script type="text/javascript" src="js/gChart/jquery.gchart.min.js"></script>
        
        <!--Propias-->
        <link rel="stylesheet" type="text/css" href="css/estilos.css">
        <script type="text/javascript" src="js/functionsSCM.js"></script>
        
        <style>
            #view-source {
                position: fixed;
                display: block;
                right: 0;
                bottom: 0;
                margin-right: 40px;
                margin-bottom: 40px;
                z-index: 900;
            }
            .oneframe{
                width:99%;
                margin:0 0.5%;
                height:100%;
            }
            .twoframes{
                max-width:49.5% !important;
                margin:0 0.1%;
                height:100%;
            }
            .threeframes{
                width:32%;
                margin:0 0.5%;
                height:100%;
            }
            .label{
                /*width:140px;*/
                font-weight:bold;
            }
            #detalleConsumoPdn th{
                position: relative;
                vertical-align: bottom;
                text-overflow: ellipsis;
                font-weight: 700;
                line-height: 24px;
                letter-spacing: 0;
                height: 48px;
                font-size: 12px;
                color: rgba(0,0,0,.54);
                padding-bottom: 8px;
                box-sizing: border-box;
            }
            #detalleConsumoPdn td:last-of-type{
                padding-right: 24px;
            }
            #detalleConsumoPdn td:first-of-type{
                padding-left: 24px;
            }
            th{
                text-align:center !important;
            }
            .blueButtonDiv{
                float: right;
                margin-top: 15px;
                margin-left: 10px;
            }
            .mdl-data-table tbody tr, .mdl-data-table tbody tr td{
                height:38px !important;
            }
            .pdnInputInv{
                font-size:11px !important;
            }
            .highlight{
                font-weight:bold !important;
                background: #fafafa !important;
            }
            .small{
                font-size:10px !important;
            }
        </style>
    </head>
    <body>
        <input id="uSe" type="hidden" value="<?php echo $_POST["userSesion"]; ?>"/>
        <input id="idR" type="hidden" value="<?php echo $_GET["id"]; ?>"/>
        <input type="hidden" id="fec1" value="<?php echo $_GET["fec1"]; ?>"/>
        <input type="hidden" id="fec2" value="<?php echo $_GET["fec2"]; ?>"/>
        <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
            <header class="mdl-layout__header">
                <div class="mdl-layout__header-row">
                    <span class="mdl-layout-title">ABASTO DE U-100 (ZK)</span>
                    <div class="mdl-layout-spacer"></div>
                    <div class="mdl-layout-spacer"></div>
                </div>
            </header>
            <div class="mdl-layout__drawer mdl-color--grey-900 mdl-color-text--blue-grey-50">
                <span class="mdl-layout-title">
                    <div class="siic-logo-h"></div>
                    <span class="siic-nomUsuario"></span>
                </span>
                <nav class="siic-navigation mdl-navigation">
                    <a class="mdl-navigation__link" href="#inventarios"><li class="material-icons">insert_chart</li>Inventarios</a>
                    <a class="mdl-navigation__link" href="#Flujo"><li class="material-icons">list</li>Inventario en tránsito</a>
                    <a class="mdl-navigation__link" href="#oc"><li class="material-icons">list</li>OC en tránsito</a>
                </nav>
            </div>
            <main class="mdl-layout__content">
                <section class="mdl-grid" id='seccionInventarios'>
                    <a name="inventarios"></a>
                    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-grid">
                        <h4 class="mdl-cell mdl-cell--12-col">INVENTARIOS</h4>
                        <div class="mdl-color--white mdl-cell mdl-cell--12-col mdl-grid">
                            <div class="mdl-color--white mdl-cell mdl-cell--4-col mdl-grid">
                                <h4 class="mdl-cell mdl-cell--12-col">INVENTARIO ACTUAL DE U-100</h4>
                                <table id='inventarioActual' class="mdl-data-table mdl-js-data-table mdl-cell--3-col">
                                    <thead>
                                        <tr>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>TOTAL</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mdl-layout-spacer"></div>
                            <a name="Consumos"></a>
                            <div class="mdl-color--white">
                                <h4 class="mdl-cell mdl-cell--12-col">CONSUMOS DE U-100 LW</h4>
                                <table class="mdl-data-table mdl-js-data-table mdl-cell--4-col" id='lw'>
                                    <thead>
                                        <tr>
                                            <th class="mdl-data-table__cell--non-numeric">LW</th>
                                            <th class="mdl-data-table__cell--non-numeric STG">STG</th>
                                            <th class="mdl-data-table__cell--non-numeric GDL">GDL</th>
                                            <th class="mdl-data-table__cell--non-numeric MTY">MTY</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td class="mdl-data-table__cell--non-numeric">KGS</td><td id='SLWkgs' class="pdnInputInv STG"></td><td id='GLWkgs' class="pdnInputInv GDL"></td><td id='MLWkgs' class="pdnInputInv MTY"></td></tr>
                                        <tr><td class="mdl-data-table__cell--non-numeric">KGS/LT</td><td id='SLWkgslt' class="pdnInputInv STG"></td><td id='GLWkgslt' class="pdnInputInv GDL"></td><td id='MLWkgslt' class="pdnInputInv MTY"></td></tr>
                                        <tr><td class="mdl-data-table__cell--non-numeric">SACOS</td><td id='SLWsacos' class="pdnInputInv STG"></td><td id='GLWsacos' class="pdnInputInv GDL"></td><td id='MLWsacos' class="pdnInputInv MTY"></td></tr>
                                        <tr><td class="mdl-data-table__cell--non-numeric">MIX</td><td id='SLWmix' class="mdl-data-table__cell--non-numeric pdnInputInv STG"></td><td id='GLWmix' class="mdl-data-table__cell--non-numeric pdnInputInv GDL"></td><td id='MLWmix' class="mdl-data-table__cell--non-numeric pdnInputInv MTY"></td></tr>

                                    </tbody>
                                </table>
                            </div>
                            <div class="mdl-color--white" style='margin-left:10px;'>
                                <h4 class="mdl-cell mdl-cell--12-col">CONSUMOS DE U-100 L4W</h4>
                                <table class="mdl-data-table mdl-js-data-table mdl-cell--4-col" id='l4w'>
                                    <thead>
                                        <tr>
                                            <th class="mdl-data-table__cell--non-numeric">L4W</th>
                                            <th class="mdl-data-table__cell--non-numeric STG">STG</th>
                                            <th class="mdl-data-table__cell--non-numeric GDL">GDL</th>
                                            <th class="mdl-data-table__cell--non-numeric MTY">MTY</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td class="mdl-data-table__cell--non-numeric">KGS</td><td id='SL4Wkgs' class="pdnInputInv STG"></td><td id='GL4Wkgs' class="pdnInputInv GDL"></td><td id='ML4Wkgs' class="pdnInputInv MTY"></td></tr>
                                        <tr><td class="mdl-data-table__cell--non-numeric">KGS/LT</td><td id='SL4Wkgslt' class="pdnInputInv STG"></td><td id='GL4Wkgslt' class="pdnInputInv GDL"></td><td id='ML4Wkgslt' class="pdnInputInv MTY"></td></tr>
                                        <tr><td class="mdl-data-table__cell--non-numeric">SACOS</td><td id='SL4Wsacos' class="pdnInputInv STG"></td><td id='GL4Wsacos' class="pdnInputInv GDL"></td><td id='ML4Wsacos' class="pdnInputInv MTY"></td></tr>
                                        <tr><td class="mdl-data-table__cell--non-numeric">MIX</td><td id='SL4Wmix' class="mdl-data-table__cell--non-numeric pdnInputInv STG"></td><td id='GL4Wmix' class="mdl-data-table__cell--non-numeric pdnInputInv GDL"></td><td id='ML4Wmix' class="mdl-data-table__cell--non-numeric pdnInputInv MTY"></td></tr>
                                        <tr><td class="mdl-data-table__cell--non-numeric">SACOS/SEM</td><td id='SL4WsacosSem' class="pdnInputInv STG"></td><td id='GL4WsacosSem' class="pdnInputInv GDL"></td><td id='ML4WsacosSem' class="pdnInputInv MTY"></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mdl-color--white mdl-cell mdl-cell--12-col">
                            <a name="Flujo"></a>
                            <h3 class="mdl-cell mdl-cell--12-col">Inventario en tránsito</h3>
                            <div id='tablasTransito' class='mdl-color--white mdl-cell mdl-cell--12-col'></div>
                        </div>
                        <div class="mdl-color--white mdl-cell mdl-cell--12-col">
                            <a name="oc"></a>
                            <h3 class="mdl-cell mdl-cell--12-col">Órdenes de Compra en Tránsito</h3>
                            <div id="detalleOC" class="mdl-cell mdl-cell--12-col mdl-grid">
                            </div>
                        </div>
                    </div>
                </section>

                <footer class="mdl-mini-footer">
                    <div class="mdl-mini-footer__left-section">
                        Todos los Derechos Reservados Raloy Lubricantes, S.A. de C.V. &trade;
                    </div>
                    <div class="mdl-mini-footer__right-section">
                        2015
                    </div>
                </footer>
            </main>
        </div>
        
        
    </body>
</html>