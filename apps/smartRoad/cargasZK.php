<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]) || !in_array(2,$_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: ../../login.html?app=smartRoad');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Cargas Granel</title>
        <link rel="icon" type="image/png" href="img/icono_camiones.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--DataTables-->
        <link rel="stylesheet" href="../../libs/DataTables-1.10.16/media/css/jquery.dataTables.min.css">
        <script type="text/javascript" src="../../libs/DataTables-1.10.16/media/js/jquery.dataTables.min.js"></script>

        <!--JqueryUI-->
        <link rel="stylesheet" href="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.css">
        <script type="text/javascript" src="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/style.css">
        <script type="text/javascript" src="js/fCargasGranel.js"></script>
    </head>
    <body>
        <div id="divPedidos" class='modalContainer'>
            <div class="cuerpoModal">
                <div class="menuModal">
                    <div class='leftMenuModal'><button id="closePedidos"><i class="fa fa-close"></i> Cerrar</button></div>
                    <div class='centerMenuModal'><div class='avisos'></div></div>
                    <div class='rightMenuModal'><button id='btnUpdPedidos'>Actualizar <i class='fa fa-refresh'></i></button><button id="btnSavePedidos"><i class="fa fa-save"></i> Guardar</button></div>
                </div>
                <div class="contentModal">
                    <table id="tblPedidos" class='display nowrap compact'>
                        <thead>
                            <tr>
                                <th><i class='fa fa-tags'></i></th>
                                <th>ATQ</th>
                                <th>Lts a Surtir</th>
                                <th>ETA</th>
                                <th>Causa Cambio</th>
                                <th>Estado Std.</th>
                                <th>Ciudad Std.</th>
                                <th>Pedido</th>
                                <th><i class='fa fa-calendar'></i> Pedido</th>
                                <th><i class='fa fa-calendar'></i> Compromiso</th>
                                <th><i class='fa fa-key'></i> Cliente</th>
                                <th>Cliente</th>
                                <th>Destino</th>
                                <th><i class='fa fa-key'></i> Producto</th>
                                <th>Producto</th>
                                <th>Estado Odoo</th>
                                <th>Ciudad Odoo</th>
                                <th>Litros</th>
                                <th>Lts Pendientes</th>
                                <th>Albaran</th>
                                <th>Agente</th>
                                <th>Estado Remisión</th>
                                <th>soid</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                        <tfoot><th colspan='21'></th></tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div id="divPapeleta" class='modalContainer'>
            <div class="cuerpoModal">
                <div class="menuModal">
                    <div class='leftMenuModal'><button id='closePapeleta'><i class="fa fa-close"></i> Cancelar</button></div>
                    <div class='centerMenuModal'><div class='avisos'></div></div>
                    <div class='rightMenuModal'></div>
                </div>
                <div class="contentModal">
                    <h3>SOLICITUD DE CARGA DE AUTOTANQUE</h3>
                    <input type='hidden' id='inpIprs' value=''></div>
                <div id='datosCarga'>
                    <input type='hidden' id='ltsSurtirCarga'/>
                    <div class='leftCarga'>
                        <table>

                            <tr>
                                <td class='label'>Fecha de carga:</td><td class='valor'><input id='fechaCarga' type='text'/></td><td class='spacer'></td><td class='label'>De planta:</td><td  class='valor'><select id='plantaOrigen' class='selPlantas'></select></td>
                            </tr>
                            <tr>
                                <td class='label'>Fecha de regreso:</td><td class='valor'><input id='fechaRegreso' type='text'/></td><td class='spacer'></td><td class='label'>A planta:</td><td  class='valor'><select id='plantaRegreso' class='selPlantas'></select></td>
                            </tr>
                            <tr>
                                <td class='label'>Placas de la unidad:</td><td class='valor'><select id='placasUnidad'></select></td><td class='spacer'></td><td class='label'></td><td  class='valor' id='capacidadUnidad'></td>
                            </tr>

                        </table>
                    </div>
                    <div class='rightCarga'>
                        <label>Observaciones</label><br/>
                        <textarea id='observacionesCarga'></textarea>
                    </div>
                    <br style='clear:both;'/>
                    <div style='text-align:right'>
                        <button id="btnSaveSolCarga"><i class="fa fa-check"></i> Confirmar Solicitud</button>
                    </div>
                </div>
                <div id='datosPapeleta'>
                    <table>
                        <thead>
                            <tr><th>Cliente</th><th>Pedido</th><th>Destino</th><th>Cantidad (L)</th><th>Producto</th></tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot></tfoot>
                    </table>

                </div>
            </div>
        </div>

        <div id="divPonerEnCamino" class='modalContainer'>
            <div class="cuerpoModal">
                <div class="menuModal">
                    <div class='leftMenuModal'><button id='closePonerEnCamino'><i class="fa fa-close"></i> Cancelar</button></div>
                    <div class='centerMenuModal'><div class='avisos'></div></div>
                    <div class='rightMenuModal'></div>
                </div>
                <div class="contentModal">
                    <div id='pec'>
                        <h1 class='title'>PONER EN CAMINO</h1>
                        <div id='folioPECCont'>FOLIO DE LA SOLICITUD: <span id='idEntregaPEC'></span></div>
                        <div class='datosPEC'>
                            <table>
                                <tr>
                                    <td class='label'>Fecha de solicitud:</td><td class='valor' id='fechaSolicitudPEC'></td><td class='spacer'></td><td class='label'>Solicitante:</td><td  class='valor' id='solicitantePEC'></td>
                                </tr>
                                <tr>
                                    <td class='label'>Placas de la unidad:</td><td class='valor' id='placasUnidadPEC'></td><td class='spacer'></td><td class='label'>Capacidad:</td><td  class='valor' id='capacidadUnidadPEC'>33,000 L </td>
                                </tr>

                                <tr>
                                    <td class='label'>Fecha de carga:</td><td class='valor' id='fechaCargaPEC'></td><td class='spacer'></td><td class='label'>De planta:</td><td  class='valor' id='plantaCargaPEC'></td>
                                </tr>
                                <tr>
                                    <td class='label'>Fecha de regreso:</td><td class='valor' id='fechaRegresoPEC'></td><td class='spacer'></td><td class='label'>A planta:</td><td  class='valor' id='plantaRegresoPEC'></td>
                                </tr>
                            </table>
                            <table>
                                <tr>
                                    <td class='label'>Sellos Escotilla:</td><td><input type='text' id='PECsellosEscotilla'/></td>
                                </tr>
                                <tr>    
                                    <td class='label'>Num. Embarque Raloy:</td><td><input type='text' id='PECnumEmbarque'/></td>
                                </tr>
                                <tr>
                                    <td class='label'>Peso Neto:</td><td><input type='text' id='PECpesoNeto'/></td>
                                </tr>
                                <tr>
                                    <td class='label'>Responsable de la carga:</td><td><input type='text' id='PECresponsable'/></td>
                                </tr>
                            </table>
                            <br style='clear:both;'/>
                        </div>
                        <table id="datosPapeletaPEC">
                            <thead>
                                <tr><th>Fecha Entrega</th><th>Cliente</th><th>Pedido</th><th>Destino</th><th>Producto</th><th>Cant. (L)</th><th>Remisión ZK</th><th>Lote ZK</th><th>Densidad 20°C</th><th>Contenido de urea</th><th>Sellos descarga</th></tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot></tfoot>
                        </table>
                        <div class='container100'>TOTAL LTS. <div id='totalLtsPEC'></div></div>
                        <div class='container100'>UTILIZACIÓN DE UNIDAD. <div id='utilizUnidPEC'></div></div>
                        <div>
                            <span style='font-weight:bold;text-align:left;width:100%;'>Observaciones</span>
                            <div style='border:solid 1px grey;width:100%;height:15mm;' id='obsPEC'></div>
                        </div>
                        <hr/>


                        <div id='btnGPonerEnCamino'>
                            <button id='ponerEnCamino_save'><i class='fa fa-road'></i> Enviar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="divConcluirEnvio_propias" class='modalContainer'>
            <div class="cuerpoModal">
                <div class="menuModal">
                    <div class='leftMenuModal'><button id='closeConcluirEnvio'><i class="fa fa-close"></i> Cancelar</button></div>
                    <div class='centerMenuModal'><div class='avisos'></div></div>
                    <div class='rightMenuModal'></div>
                </div>
                <div class="contentModal">
                    <input type='hidden' id='concEnviIdEntrega' value=''>
                </div>
                <div id='datosConcEnvio'>
                    <h3>CONCLUIR ENVÍO CON TRANSPORTE PROPIO</h3><span class='folio'></span><hr/>
                    <div class='div33'><table>
                            <tr>
                                <td class='label'>Número de Envío Raloy</td>
                                <td id='numEnvioRaloyCE'>" + numEnvioRaloy + "</td>
                            </tr>
                            <tr>
                                <td class='label'>Diésel ($MXN)</td>
                                <td><input type='text' id='diesel' class='calcula'/></td>
                            </tr>
                            <tr>
                                <td class='label'>Peajes ($MXN)</td>
                                <td><input type='text' id='peajes' class='calcula'/></td>
                            </tr>
                            <tr>
                                <td class='label'>Alimentos ($MXN)</td>
                                <td><input type='text' id='alimentos' class='calcula'/></td>
                            </tr>
                            <tr>
                                <td class='label'>Hospedajes ($MXN)</td>
                                <td><input type='text' id='hospedaje' class='calcula'/></td>
                            </tr>
                            <tr>
                                <td class='label'>Otros ($MXN)</td>
                                <td><input type='text' id='otros' class='calcula'/></td>
                            </tr>
                            <tr>
                                <td class='label'>Descr. Otros</td>
                                <td><textarea id='expotros'></textarea></td>
                            </tr>
                        </table></div>

                    <div class='div33'><table>
                            <tr>
                                <td class='label'>Lts. Diésel</td>
                                <td><input type='text' id='ltsDiesel'/></td>
                            </tr>
                            <tr>
                                <td class='label'>Odóm. Inicial</td>
                                <td><input type='text' id='odomInicio'/></td>
                            </tr>
                            <tr>
                                <td class='label'>Odóm. Final</td>
                                <td><input type='text' id='odomFin'/></td>
                            </tr>

                            <tr>
                                <td class='labelDisabled'>Kms. Recorr</td>
                                <td><input type='text' readonly='readonly' id='kmsRecorr'/></td>
                            </tr>
                            <tr>
                                <td class='labelDisabled'>Rend. Kms/Lt</td>
                                <td><input type='text' readonly='readonly' id='rendimiento'/></td>
                            </tr>
                            <tr>
                                <td class='labelDisabled'>Lts Entregados</td>
                                <td><input type='text' id='ltsEntregados' value=''/></td>
                            </tr>
                            <tr>
                                <td class='labelDisabled'>Costo Total</td>
                                <td><input type='text' id='costoTotP'/></td>
                            </tr>
                            <tr>
                            <tr>
                                <td class='labelDisabled'>Costo/Lt entregado</td>
                                <td><input type='text' readonly='readonly' id='costoxltP'/></td>
                            </tr>
                        </table></div>
                    <div class='div33'><div class='label' style='width:100%;padding:5px;box-sizing:border-box;'>Bitácora/Observaciones</div><textarea id='bitacoraConcEnv'></textarea></div>
                    <div class='div100'>
                        <button id='concluyePropias' identrega=''><i class='fa fa-save'></i> Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="divConcluirEnvio_externas" class='modalContainer'>
            <div class="cuerpoModal">
                <div class="menuModal">
                    <div class='leftMenuModal'><button id='closeConcluirEnvioExt'><i class="fa fa-close"></i> Cancelar</button></div>
                    <div class='centerMenuModal'><div class='avisos'></div></div>
                    <div class='rightMenuModal'></div>
                </div>
                <div class="contentModal">
                    <input type='hidden' id='concEnviIdEntregaExt' value=''>
                </div>
                <div id='datosConcEnvioExt'>
                    <h3>CONCLUIR ENVÍO CON TRANSPORTE EXTERNO</h3><span class='folio'></span><hr/>
                    <div class='div33'>
                        <table>
                            <tr>
                                <td class='label'>Número de Envío Raloy</td>
                                <td id='numEnvioRaloyCEE'>" + numEnvioRaloy + "</td>
                            </tr>
                            <tr>
                                <td class='label'>Lts. Diésel</td>
                                <td><input type='text' id='ltsDieselE'/></td>
                            </tr>
                            <tr>
                                <td class='label'>Odóm. Inicial</td>
                                <td><input type='text' id='odomInicioE'/></td>
                            </tr>
                            <tr>
                                <td class='label'>Odóm. Final</td>
                                <td><input type='text' id='odomFinE'/></td>
                            </tr>

                            <tr>
                                <td class='labelDisabled'>Kms. Recorr</td>
                                <td><input type='text' readonly='readonly' id='kmsRecorrE'/></td>
                            </tr>
                            <tr>
                                <td class='labelDisabled'>Rend. Kms/Lt</td>
                                <td><input type='text' readonly='readonly' id='rendimientoE'/></td>
                            </tr>
                            <tr>
                                <td class='labelDisabled'>Lts Entregados</td>
                                <td><input type='text' id='ltsEntregadosE' value=''/></td>
                            </tr>
                            <tr>
                                <td class='label'>Costo Total</td>
                                <td><input type='text' id='costoTotPE'/></td>
                            </tr>
                            <tr>
                            <tr>
                                <td class='labelDisabled'>Costo/Lt entregado</td>
                                <td><input type='text' readonly='readonly' id='costoxltPE'/></td>
                            </tr>
                        </table>
                    </div>
                    <div class='div33'>
                        <div class='label' style='width:100%;padding:5px;box-sizing:border-box;'>Bitácora/Observaciones</div><textarea id='bitacoraConcEnvE'></textarea>
                    </div>
                    <div class='div100'>
                        <button id='concluyeExternas' identrega=''><i class='fa fa-save'></i> Guardar</button>
                    </div>
                </div>
            </div>
        </div>


        <!--****************Terminan divs modales****************-->
        <header>
            <div id='leftHeader'>
                <a id="logo" href="../../index.php"><img src="../../img/Logo Skyblue horizontal.png" alt="SkyBlue"/></a>
                <span id="appName" ><i class='fas fa-lightbulb'></i> SmartRoad</span>
            </div>
            <div id='rightHeader'>
                <tab><i class="fas fa-user"></i> <span id="userSession"><?php echo $_SESSION["sessionInfo"]["userName"]; ?></span></tab>
                <tab><i class="fa fa-clock-o"></i> <span><?php echo $_SESSION["sessionInfo"]["sessionDate"]; ?></tab>
                <tab><a href='../../login.html'><i class="fa fa-sign-out-alt"></i></a></tab>
            </div>
        </header>

        <div id="subHeader">
            <div class="shLeft">
                <ul id="sessionInfo">
                    <li><button id="btnLlamaPedidos" abre="divPedidos"><i class="fa fa-search"></i> Pedidos</button></li>
                    <li><button id="btnUpdate" abre="divPedidos"><i class="fa fa-sync"></i> Actualizar</button></li>
                    <li>
                        <?php
                        if ($_SESSION["sessionInfo"]["userSession"] == 1) {
                            $botonpedidocerrado = "<input type='text' id='pedidocerradoRaloy' placeholder='Pedido o Remisión'/><button onclick='buscapedidocerrado();'>Buscar Pedido cerrado</button>";
                            echo $botonpedidocerrado;
                        }
                        ?>
                    </li>
                </ul>
            </div>
            <div class="shRight">
                <ul>
                    <li><a class='current' href="index.php"><i class='fa fa-road'></i>  Ruteo</a></li>
                    <li><a href="crud.php"><i class='fa fa-cog'></i> Administración</a></li>
                    <!--<li><a href="reportes.php"><i class='fa fa-line-chart'></i> Reportes</a></li>-->
                </ul>
            </div>
            <br style="clear:both;"/>
        </div>


        <div id="cuerpo">
            <div>

                <!--Llamar pedidos cerrados para ruteo-->

            </div>
            <section>
                <div class='titleSection'>En ruteo</div>
                <div id='contenedorBoxes'></div>
            </section>
            <section>
                <div class='titleSection'>En carga</div>
                <div id='contenedorCargas'></div>
            </section>
            <section>
                <div class='titleSection'>En camino</div>
                <div id='contenedorEnvios'></div>
            </section>
        </div>



        <div class='boxCarga'>

    </body>
</html>