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

        <link href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=es" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="mdl/material.indigo-blue.min.css">
        <link rel="stylesheet" type="text/css" href="css/estilos.css">
        <link rel="shortcut icon" href="images/raloy.ico">
        <title>Dashboard Skyblue</title>
        <script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
        <script type="text/javascript" src="js/gChart/jquery.plugin.min.js"></script> 
        <script type="text/javascript" src="js/gChart/jquery.gchart.min.js"></script>
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
        <input type="hidden" id="fec1" value="<?php echo $_GET["fec1"];?>"/>
        <input type="hidden" id="fec2" value="<?php echo $_GET["fec2"];?>"/>
        <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
            <header class="mdl-layout__header">
                <div class="mdl-layout__header-row">
                    <span class="mdl-layout-title">DASHBOARD ZAR/KRUSE</span>
                    <div class="mdl-layout-spacer"></div>
                    <!--<span id="nombreCliente" class="mdl-layout-title mdl-layout--large-screen-only">NOMBRE DEL CLIENTE</span>-->
                    <div class="mdl-layout-spacer"></div>
                    <!--<span id="descDeterminante" class="mdl-layout-title mdl-layout--large-screen-only">NOMBRE DEL DETERMINANTE</span>-->
                    <!--<span class="siic-nomUsuario-large mdl-layout-title mdl-layout--large-screen-only">HOLA</span>-->
                </div>
            </header>
            <div class="mdl-layout__drawer mdl-color--grey-900 mdl-color-text--blue-grey-50">
                <span class="mdl-layout-title">
                    <div class="siic-logo-h"></div>
                    <span class="siic-nomUsuario"></span>
                </span>
                <nav class="siic-navigation mdl-navigation">
                    <!--<a class="mdl-navigation__link" href="#top"><li class="material-icons" role="presentation">home</li>Gestión de instalaciones</a>-->
                    <a class="mdl-navigation__link" href="#inventarios"><li class="material-icons">insert_chart</li>Inventarios</a>
                    <a class="mdl-navigation__link" href="#produccion"><li class="material-icons">list</li>Producción</a>
                    <a class="mdl-navigation__link" href="#rentabilidad"><li class="material-icons">list</li>Rentabilidad</a>
                    <a class="mdl-navigation__link" href="#instalaciones"><li class="material-icons">list</li>Instalaciones</a>
                    <a class="mdl-navigation__link" href="login/logout.html"><li class="material-icons">exit_to_app</li>Cerrar Sesión</a>
                </nav>
            </div>
            <main class="mdl-layout__content">
                <!--                <section>
                                    <a name="top"></a>
                                    <div class="siic-home-slide mdl-typography--text-center"  id="top"></div>
                                </section>-->
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
                                            <th class="mdl-data-table__cell--non-numeric STG">STG</th>
                                            <th class="mdl-data-table__cell--non-numeric GDL">GDL</th>
                                            <th class="mdl-data-table__cell--non-numeric MTY">MTY</th>
                                            <th class="mdl-data-table__cell--non-numeric TOTS">TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>P</td>
                                            <td id='SPU100' class='STG invInput'></td>
                                            <td id='GPU100' class='GDL invInput'></td>
                                            <td id='MPU100' class='MTY invInput'></td>
                                            <td id='TOTPU100' class='TOTS invInput'></td>
                                        </tr>
                                        <tr>
                                            <td>C</td>
                                            <td id='SCU100' class='STG invInput'></td>
                                            <td id='GCU100' class='GDL invInput'></td>
                                            <td id='MCU100' class='MTY invInput'></td>
                                            <td id='TOTCU100' class='TOTS invInput'></td>
                                        </tr>
                                        <tr>
                                            <td>I</td>
                                            <td id='SIU100' class='STG invInput'></td>
                                            <td id='GIU100' class='GDL invInput'></td>
                                            <td id='MIU100' class='MTY invInput'></td>
                                            <td id='TOTIU100' class='TOTS invInput'></td>
                                        </tr>
                                        <tr>
                                            <td>TOTAL</td>
                                            <td id='STOTU100' class='STG invInput'></td>
                                            <td id='GTOTU100' class='GDL invInput'></td>
                                            <td id='MTOTU100' class='MTY invInput'></td>
                                            <td id='TOTTOTU100' class='TOTS invInput'></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mdl-layout-spacer"></div>

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
                            <h3 class="mdl-cell mdl-cell--12-col">Órdenes de compra</h3>
                            <div id="detalleOC" class="siic-list-reportes mdl-layout__content mdl-cell mdl-cell--top mdl-cell--12-col">
                            </div>
                        </div>
                        <div class="mdl-color--white mdl-cell mdl-cell--12-col">
                            <h3 class="mdl-cell mdl-cell--12-col">Flujo de U-100</h3>
                            <div id='tablasTransito' class='mdl-color--white mdl-cell mdl-cell--12-col'></div>
                        </div>
                    </div>
                </section>
                <section class="mdl-grid" id='seccionProduccion'>
                    <a name="produccion"></a>
                    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-grid">
                        <h4 class="mdl-cell mdl-cell--12-col">PRODUCCIÓN</h4>
                        <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-grid">
                            <form action="" id="frmconsultaPdn">
                                <div class="blueButtonDiv">
                                    <button type="submit" class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" id="mostrar">Mostrar</button>
                                </div>
                            </form>
                        </div>
                        <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-grid">
                            <h4 class="mdl-cell mdl-cell--12-col">RESUMEN</h4>
                            <table class="mdl-data-table mdl-js-data-table mdl-cell--8-col">
                                <thead>
                                    <tr>
                                        <th class="mdl-data-table__cell--non-numeric"></th>
                                        <th class="mdl-data-table__cell--non-numeric STG">STG</th>
                                        <th class="mdl-data-table__cell--non-numeric GDL">GDL</th>
                                        <th class="mdl-data-table__cell--non-numeric MTY">MTY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td class="mdl-data-table__cell--non-numeric">PRODUCCIÓN PROMEDIO (LTS)</td><td id='StyLAVG' class="pdnInput STG"></td><td id='GtyLAVG' class="pdnInput GDL"></td><td id='MtyLAVG' class="pdnInput MTY"></td></tr>
                                    <tr><td class="mdl-data-table__cell--non-numeric">PRODUCCIÓN TOTAL EN EL PERIODO (LTS)</td><td id='StyL' class="pdnInput STG"></td><td id='GtyL' class="pdnInput GDL"></td><td id='MtyL' class="pdnInput MTY"></td></tr>
                                    <tr><td class="mdl-data-table__cell--non-numeric">AGUA PERMEADA CONSUMIDA (LTS)</td><td id='StyAgua' class="pdnInput STG"></td><td id='GtyAgua' class="pdnInput GDL"></td><td id='MtyAgua' class="pdnInput MTY"></td></tr>
                                    <tr><td class="mdl-data-table__cell--non-numeric">CONSUMO DE U-100 EN EL PERIODO (KGS)</td><td id='StyConsumption' class="pdnInput STG"></td><td id='GtyConsumption' class="pdnInput GDL"></td><td id='MtyConsumption' class="pdnInput MTY"></td></tr>
                                    <tr><td class="mdl-data-table__cell--non-numeric">CONSUMO DE U-100 POR LITRO (KGS/LT)</td><td id='StyConsumptionL' class="pdnInput STG"></td><td id='GtyConsumptionL' class="pdnInput GDL"></td><td id='MtyConsumptionL' class="pdnInput MTY"></td></tr>
                                    <tr><td class="mdl-data-table__cell--non-numeric">MIX DE U-100 EN EL PERIODO</td><td id='StyShare' class="pdnInput STG"></td><td id='GtyShare' class="pdnInput GDL"></td><td id='MtyShare' class="pdnInput MTY"></td></tr>
                                </tbody>
                            </table>
                            <div class="mdl-layout-spacer"></div>
                        </div>
                        <a name="consumosPdn"></a>
                        <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-grid">
                            <h4 class="mdl-cell mdl-cell--12-col">CONSUMOS DE U-100 POR ORDEN DE PRODUCCIÓN</h4>
                            <div class="mdl-cell mdl-cell--12-col mdl-grid">
                                <div id="detalleConsumoPdn" class="siic-list-reportes mdl-layout__content mdl-cell mdl-cell--top mdl-cell--4-col">

                                </div>                           
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
        <script type="text/javascript" src="mdl/material.min.js"></script>
        <script type="text/javascript" src="js/functionsSCM.js"></script>
    </body>
</html>