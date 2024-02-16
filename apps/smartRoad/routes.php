<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "Rutas";
$path = "<a href='index.php'>SmartRoad</a> / " . $title;
$modulo = 14;

require_once($_SERVER['DOCUMENT_ROOT']."/today.zar-kruse.com//php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    header('Location: /intranet/login.html?app=today/index.php');
}

if (!in_array($modulo, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: /intranet/index.php?t=' . $_GET["t"]);
}

?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo $title;?></title>
    <link rel="icon" type="image/png" href="../../img/route.png" />
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

    <!--Number Formats-->
    <script type="text/javascript" src="../../libs/autonumeric/numberformatter/libs/jshashtable-3.0.js"></script>
    <script type="text/javascript" src="../../libs/jquery.numberformatter-1.2.4.jsmin.js"></script>

    <!-- bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>


    <!--Propias-->
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="/intranet/css/sIndex.css">
    <link rel="stylesheet" href="css/style.css?v=1.2.2">
    <script type="text/javascript" src="js/fRoutes.js?v=tkn"></script>
</head>

<body>
    <input type='hidden' value='' id='preciodieseltoday' />
    <div id="divPedidos" class='modalContainer'>
        <div class="cuerpoModal">
            <div class="menuModal">
                <div class='leftMenuModal'><button id="closePedidos"><i class="fa fa-close"></i> Cerrar</button></div>
                <div class='centerMenuModal'>
                    <div class='avisos'></div>
                </div>
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
                            <th>Fuente</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    <tfoot>
                        <th colspan='21'></th>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div id="divPapeleta" class='modalContainer'>
        <div class="cuerpoModal">
            <div class="menuModal">
                <div class='leftMenuModal'><button id='closePapeleta'><i class="fa fa-close"></i> Cancelar</button></div>
                <div class='centerMenuModal'>
                    <div class='avisos'></div>
                </div>
                <div class='rightMenuModal'></div>
            </div>
            <div class="contentModal">
                <h3>SOLICITUD DE CARGA DE AUTOTANQUE</h3>
                <input type='hidden' id='inpIprs' value=''>
            </div>
            <div id='datosCarga'>
                <input type='hidden' id='ltsSurtirCarga' />
                <div class='leftCarga'>
                    <table>

                        <tr>
                            <td class='label'>Fecha de carga:</td>
                            <td class='valor'><input id='fechaCarga' type='text' /></td>
                            <td class='spacer'></td>
                            <td class='label'>De planta:</td>
                            <td class='valor'><select id='plantaOrigen' class='selPlantas'></select></td>
                        </tr>
                        <tr>
                            <td class='label'>Fecha de regreso:</td>
                            <td class='valor'><input id='fechaRegreso' type='text' /></td>
                            <td class='spacer'></td>
                            <td class='label'>A planta:</td>
                            <td class='valor'><select id='plantaRegreso' class='selPlantas'></select></td>
                        </tr>
                        <tr>
                            <td class='label'>Placas de la unidad:</td>
                            <td class='valor'><select id='placasUnidad'></select></td>
                            <td class='spacer'></td>
                            <td class='label'></td>
                            <td class='valor' id='capacidadUnidad'></td>
                        </tr>
                        <tr>
                            <td class='label'>Carta Porte Transportista:</td>
                            <td class='valor' id='numCartaPorte'></td>
                        </tr>
                    </table>
                </div>
                <div class='rightCarga'>
                    <label>Observaciones</label><br />
                    <textarea id='observacionesCarga'></textarea>
                </div>
                <br style='clear:both;' />
                <div style='text-align:right'>
                    <button id="btnSaveSolCarga"><i class="fa fa-check"></i> Confirmar Solicitud</button>
                </div>
            </div>
            <div id='datosPapeleta'>
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Pedido</th>
                            <th>Destino</th>
                            <th>Cantidad (L)</th>
                            <th>Producto</th>
                        </tr>
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
                <div class='centerMenuModal'>
                    <div class='avisos'></div>
                </div>
                <div class='rightMenuModal'></div>
            </div>
            <div class="contentModal">
                <div id='pec'>
                    <h1 class='title'>PONER EN CAMINO</h1>
                    <div id='folioPECCont'>FOLIO DE LA SOLICITUD: <span id='idEntregaPEC'></span></div>
                    <div class='datosPEC'>
                        <div class='floated'>
                            <span class='labelx'>Solicitante:</span> <span class='valorx' id='solicitantePEC'></span><br />
                            <span class='labelx'>Fecha de solicitud:</span> <span class='valorx' id='fechaSolicitudPEC'></span><br />
                            <span class='labelx'>Fecha de carga:</span> <span class='valorx' id='fechaCargaPEC'></span><br />
                            <span class='labelx'>Fecha de regreso:</span> <span class='valorx' id='fechaRegresoPEC'></span><br />
                            <span class='labelx'>Planta Origen:</span> <span class='valorx' id='plantaCargaPEC'></span><br />
                            <span class='labelx'>Planta Regreso:</span> <span class='valorx' id='plantaRegresoPEC'></span><br />
                        </div>
                        <div class='floated'>
                            <span class='labelx'>Placas Unidad:</span> <span class='valorx' id='placasUnidadPEC'></span><br />
                            <span class='labelx'>Capacidad Unidad:</span> <span class='valorx' id='capacidadUnidadPEC'></span><br />
                            <span class='labelx'>Sellos Escotilla:</span> <span class='valorx' id='PECsellosEscotilla'></span><br />
                            <span class='labelx'>Utilización:</span> <span class='valorx' id='utilizUnidPEC'></span><br />
                            <span class='labelx'>Litros Totales:</span> <span class='valorx' id='totalLtsPEC'></span><br />
                            <span class='labelx'>Responsable de Carga:</span> <span class='valorx' id='PECresponsable'></span><br />
                        </div>
                        <div class='floated'>
                            <span class='labelx'>Peso Neto(Kgs.):</span><br />
                            <span class='valorx'><input type='text' id='PECpesoNeto' /></span><br />
                            <span class='labelx'>Num. Embarque Raloy:</span><br />
                            <span class='valorx'><input type='text' id='PECnumEmbarque' /></span><br />
                            <br />
                            <span class='btnCtr'><button id='ponerEnCamino_save'><i class='fa fa-road'></i> Poner en Camino</button></span>
                        </div>
                    </div>
                    <br />
                    <table id="datosPapeletaPEC">
                        <thead>
                            <thead>
                                <th>Entrega</th>
                                <th>Lote EPT</th>
                                <th>Lote PT</th>
                                <th>Sellos</th>
                                <th>Remisiones ZK</th>
                                <th>Certificado</th>
                            </thead>
                        <tbody></tbody>
                        <tfoot></tfoot>
                    </table>
                    <div>
                        <span style='font-weight:bold;text-align:left;width:100%;'>Observaciones</span>
                        <div style='border:solid 1px grey;width:100%;height:15mm;' id='obsPEC'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="divConcluirEnvio" class='modalContainer'>
        <div class="cuerpoModal">
            <div class="menuModal">
                <div class='leftMenuModal'><button id='closeConcluirEnvio'><i class="fa fa-close"></i> Cancelar</button></div>
                <div class='centerMenuModal'>
                    <div class='avisos'></div>
                </div>
                <div class='rightMenuModal'></div>
            </div>
            <div class="contentModal">
                <input type='hidden' id='concEnviIdEntrega' value=''>
            </div>
            <div id='datosConcEnvio'>
                <h3>HOJA DE COSTO</h3><span>Folio: <span class='folio'></span></span> <span>Envío: <span id="numEnvioRaloyCE"></span> <span id='tipoenvio'></span></span>
                <hr />
                <div class='resumenConcluir datosconcluir'>
                    <table>
                        <thead>
                            <tr>
                                <th colspan="3">Registro de datos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan='3' class='tblsubh'>Rendimiento</td>
                            </tr>
                            <tr>
                                <td>Precio Diésel</td>
                                <td class='inputCtr'><input type='text' id='preciodieselsiniva' class='calccd' /></td>
                                <td>$/L sin IVA</td>
                            </tr>
                            <tr>
                                <td>Combustible consumido</td>
                                <td class='inputCtr'><input type='text' id='ltsDiesel' class='calccd calckms' /></td>
                                <td>L</td>
                            </tr>
                            <tr>
                                <td>Odóm. Inicial</td>
                                <td class='inputCtr'><input type='text' id='odomInicioP' class='calckms' /></td>
                                <td>Kms</td>
                            </tr>
                            <tr>
                                <td>Odóm. Final</td>
                                <td class='inputCtr'><input type='text' id='odomFinP' class='calckms' /></td>
                                <td>Kms</td>
                            </tr>
                            <tr>
                                <td>Peso sin Carga</td>
                                <td class='inputCtr'><input type='text' id='pesosincarga' class='calckms' /></td>
                                <td>kgs</td>
                            </tr>
                            <tr>
                                <td>Peso de la carga</td>
                                <td class='inputCtr'><input type='text' id='pesocarga' class='calckms' /></td>
                                <td>kgs</td>
                            </tr>
                            <tr>
                                <td colspan='3' class='tblsubh'>Costo fijo</td>
                            </tr>
                            <tr>
                                <td>Tiempo de ruta</td>
                                <td class='inputCtr espejeado'><input type='text' id='tiemporuta' /></td>
                                <td>Días</td>
                            </tr>
                            <tr>
                                <td colspan='3' class='tblsubh'>Costo variable</td>
                            </tr>
                            <tr>
                                <td>Peajes</td>
                                <td class='inputCtr espejeado'><input type='text' id='peajes' /></td>
                                <td>$</td>
                            </tr>
                            <tr>
                                <td>Alimentos</td>
                                <td class='inputCtr espejeado'><input type='text' id='alimentos' /></td>
                                <td>$</td>
                            </tr>
                            <tr>
                                <td>Hospedajes</td>
                                <td class='inputCtr espejeado'><input type='text' id='hospedaje' /></td>
                                <td>$</td>
                            </tr>
                            <tr>
                                <td>Otros</td>
                                <td class='inputCtr espejeado'><input type='text' id='otros' /></td>
                                <td>$</td>
                            </tr>
                            <tr>
                                <td colspan='3' class='tblsubh'>Transporte Externo</td>
                            </tr>
                            <tr class='externa'>
                                <td>Costo</td>
                                <td class='inputCtr espejeado'><input type='text' id='costoext' /></td>
                                <td>$</td>
                            </tr>
                            <tr class='externa'>
                                <td>Repartos</td>
                                <td class='inputCtr espejeado'><input type='text' id='repartosext' /></td>
                                <td>$</td>
                            </tr>
                            <tr class='externa'>
                                <td>Desvíos</td>
                                <td class='inputCtr espejeado'><input type='text' id='desviosext' /></td>
                                <td>$</td>
                            </tr>
                        </tbody>
                    </table>
                    <div>
                        <div class='label' style='width:100%;padding:5px;box-sizing:border-box;'>Bitácora/Observaciones</div><textarea id='bitacoraConcEnv'></textarea>
                    </div>
                </div>
                <div class='resumenConcluir' style='background:rgba(237,238,240,0.7);'>
                    <input type='hidden' id='longitudpipam' />
                    <table>
                        <thead>
                            <tr>
                                <th colspan='3'>Resumen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Volumen entregado</td>
                                <td id='ltsembarqueconc' class='numeric2'></td>
                                <td>Lts.</td>
                            </tr>
                            <tr>
                                <td>Tiempo de la ruta</td>
                                <td id='restiemporuta' class='numeric0'></td>
                                <td>Días</td>
                            </tr>
                            <tr>
                                <td>Kilometraje recorrido</td>
                                <td id='kmsRecorrP' class='numeric2'></td>
                                <td>Kms.</td>
                            </tr>
                            <tr>
                                <td>Rendimiento del combustible</td>
                                <td id='rendkmlP' class='numeric2'></td>
                                <td>Km/L</td>
                            </tr>
                            <tr>
                                <td>Peso Bruto</td>
                                <td id='pesobruto' class='numeric2'></td>
                                <td>Kgs</td>
                            </tr>
                        </tbody>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th colspan='3'>Costo MXN</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Total</th>
                                <th>Unitario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class='tblsubh'>Costo Fijo</td>
                                <td id='totalF' class='currency2'></td>
                                <td id='totalFUnitario' class='currency3'></td>
                            </tr>
                            <tr>
                                <td>Desgaste Llantas:</td>
                                <td id='llantas' montomensual='0' monto100kms='' class='cft prkms'></td>
                                <td id='llantasu' class='cfu'></td>
                            </tr>
                            <tr>
                                <td>Chofer:</td>
                                <td id='chofer' montomensual='' monto100kms='0' class='cft prmes'></td>
                                <td id='choferu' class='cfu'></td>
                            </tr>
                            <tr>
                                <td>Depreciación:</td>
                                <td id='depreciacion' montomensual='' monto100kms='0' class='cft prmes'></td>
                                <td id='depreciacionu' class='cfu'></td>
                            </tr>
                            <tr>
                                <td>Mantenimiento:</td>
                                <td id='mantenimiento' montomensual='' monto100kms='0' class='cft prkms'></td>
                                <td id='mantenimientou' class='cfu'></td>
                            </tr>
                            <tr>
                                <td>Administración:</td>
                                <td id='administracion' montomensual='' monto100kms='0' class='cft prmes'></td>
                                <td id='administracionu' class='cfu'></td>
                            </tr>
                            <tr>
                                <td>Seguro:</td>
                                <td id='seguro' montomensual='' monto100kms='0' class='cft prmes'></td>
                                <td id='segurou' class='cfu'></td>
                            </tr>
                            <tr>
                                <td>Otros Fijos:</td>
                                <td id='otrosfijos' montomensual='' monto100kms='0' class='cft prmes'></td>
                                <td id='otrosfijosu' class='cfu'></td>
                            </tr>

                            <tr>
                                <td class='tblsubh'>Costo Variable</td>
                                <td id='totalV' class='currency2'></td>
                                <td id='totalVUnitario' class='currency3'></td>
                            </tr>
                            <tr>
                                <td>Diésel</td>
                                <td id='resdiesel' class='cvt'></td>
                                <td id='resdieselu' class='cvu'></td>
                            </tr>
                            <tr>
                                <td>Peajes</td>
                                <td id='respeajes' class='cvt'></td>
                                <td id='respeajesu' class='cvu'></td>
                            </tr>
                            <tr>
                                <td>Alimentos</td>
                                <td id='resalimentos' class='cvt'></td>
                                <td id='resalimentosu' class='cvu'></td>
                            </tr>
                            <tr>
                                <td>Hospedajes</td>
                                <td id='reshospedaje' class='cvt'></td>
                                <td id='reshospedajeu' class='cvu'></td>
                            </tr>
                            <tr>
                                <td>Otros</td>
                                <td id='resotros' class='cvt'></td>
                                <td id='resotrosu' class='cvu'></td>
                            </tr>
                            <tr>
                                <td class='tblsubh'>Transporte Externo</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr class='ext'>
                                <td>Costo</td>
                                <td id='rescostoext' class='cvt'></td>
                                <td id='rescostoextu' class='cvu'></td>
                            </tr>
                            <tr class='ext'>
                                <td>Repartos</td>
                                <td id='resrepartosext' class='cvt'></td>
                                <td id='resrepartosextu' class='cvu'></td>
                            </tr>
                            <tr class='ext'>
                                <td>Desvíos</td>
                                <td id='resdesviosext' class='cvt'></td>
                                <td id='resdesviosextu' class='cvu'></td>
                            </tr>
                            <tr>
                                <td coslpan='3' class='tblsubh'>Costo Total</td>
                                <td id='costototP' class='currency2'></td>
                                <td id='costototuP' class='currency3'></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th coslpan='3'></th>
                            </tr>
                            <tr>
                                <th colspan='3'>
                                    <button style='width:100%;box-sizing:border-box;' id='concluyeEnvio' identrega=''><i class='fa fa-save'></i> Guardar</button>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <br style='clear:both;' />
            </div>
        </div>
    </div>

    <!--        <div id="divConcluirEnvio_externas" class='modalContainer'>
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
                            <thead>
                                <tr>
                                    <th colspan="2">Rendimiento</th>
                                </tr>
                            </thead>
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
                                <td class='labelDisabled'>Kms. Recorr.</td>
                                <td><input type='text' readonly='readonly' id='kmsRecorrE'/></td>
                            </tr>
                            <tr>
                                <td class='labelDisabled'>Rendimiento Km/L</td>
                                <td><input type='text' readonly='readonly' id='kmsRecorrE'/></td>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <td class='label'>Número de Envío Raloy</td>
                                <td id='numEnvioRaloyCEE'>" + numEnvioRaloy + "</td>
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
        </div>-->


    <!--****************Terminan divs modales****************-->
    <nav class="navbar navbar-expand-lg navbar-dark px-3 m-0 w-100">
        <a class="navbar-brand" href="/intranet/index.php">
            <img src="/today_zk/img/zarkruse-logo-light.svg" style="height:30px;padding-right:10px;" alt="SkyBlue" />
        </a>
        <div class="navbar-brand">
            <?php echo $path; ?>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
            </ul>
            <div class="form-inline my-2 mr-3 my-lg-0">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-user" style="font-size:0.8em;"></i> <?php echo $_SESSION["sessionInfo"]["userName"]; ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <div class="dropdown-item" href="#"><?php echo $_SESSION["sessionInfo"]["sessionDate"]; ?></div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/intranet/password_change.php"><i class="fa fa-key" style="font-size:0.8em;"></i> Cambiar Password</a>
                        <a class="dropdown-item" href="/intranet/login.html"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div id="subHeader">
        <div class="d-flex flex-wrap align-items-center m-0 p-0 w-100">
            <button type="button" class="btn mx-2" id="btnLlamaPedidos" abre="divPedidos"><i class="fa fa-search"></i> Pedidos</button>
            <div>
                <?php
                if ($_SESSION["sessionInfo"]["userSession"] == 1) {
                    $botonpedidocerrado = "<input type='text' id='pedidocerradoRaloy' placeholder='Pedido o Remisión'/><button onclick='buscapedidocerrado();'>Buscar Pedido cerrado</button>";
                    echo $botonpedidocerrado;
                }
                ?>
            </div>
            <a href="routes.php" class="btn ml-auto mr-2" role="button" aria-disabled="true" >Actualizar</a>
        </div>
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