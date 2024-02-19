<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]) || !in_array(19, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: ../../login.html?app=smartRoad/index.php');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Admin Ruteo</title>
        <link rel="icon" type="image/png" href="../../img/route.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Bootstrap-->
        <link rel="stylesheet" href="../../libs/bootstrap-4.3.1/css/bootstrap.min.css">
        <script type="text/javascript" src="../../libs/bootstrap-4.3.1/js/bootstrap.bundle.min.js"></script>

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


        <!--Propias-->
        <!--<link rel="stylesheet" href="../../css/bhc.css">-->
        <!--<link rel="stylesheet" href="css/header.css">-->
        <link rel="stylesheet" href="css/sAdmin.css?v=0">
        <script type="text/javascript" src="js/admin.js"></script>
    </head>
    <body class="bg-light">
        <div id="divConcluirEnvio" class='modalContainer bg-light w-100 h-100'>
            <div class="cuerpoModal container">
                <div class="navbar bg-light navbar-light px-0 my-2">
                    <div class='leftMenuModal'><button id='closeConcluirEnvio' class="btn btn-secondary mx-0"><i class="fa fa-close"></i> Regresar</button></div>
                    <div class='centerMenuModal'><div class='avisos'></div></div>
                    <div class='rightMenuModal'>
                        <button id='enableEdit' class="btn btn-primary mx-0">Editar</button>
                        <div id="editBtns" class="btn-group">
                            <button id='cancelEdit' class="btn btn-secondary mx-0">Cancelar</button>
                            <button id='saveEdit' class="btn btn-primary mx-0">Guardar</button>
                        </div>

                    </div>
                </div>
                <div class="contentModal">
                    <input type='hidden' id='concEnviIdEntrega' value=''>
                </div>
                <div id='datosConcEnvio'>
                    <div class="d-flex flex-row m-0 p-0">
                        <div class="flex-sm-4 px-2">
                            <h4>HOJA DE COSTO</h4>
                        </div>
                        <div class="flex-sm-4 px-2">
                            <span>
                                Folio: 
                                <span class='folio'></span>

                            </span> 
                            <span>
                                Envío: 
                                <span id="numEnvioRaloyCE"></span> 
                                <span id='tipoenvio'></span>

                            </span>
                        </div>
                        <div class="flex-sm-4 px-2 ml-auto">

                        </div>
                    </div>
                    <hr class="w-100"/>
                    <div class="row p-0 m-0">
                        <div class='col-md-4 p-0 pr-2 m-0'>
                            <h5>Registro de Datos <small class="text-danger">Costos sin IVA</small></h5>
                            <table class="w-100">
                                <tbody>
                                    <tr>
                                        <td>Precio Diésel</td>
                                        <td class='inputCtr'>
                                            <div class='input-group'>
                                                <input class='form-control calccd' type='text' behave-as='number' id='preciodieselsiniva'/>
                                                <div class='input-group-append'><span class='input-group-text'>$/L Sin IVA</span></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Combustible consumido</td>
                                        <td class='inputCtr'>
                                            <div class='input-group'>
                                                <input class='form-control calccd calckms' type='text' behave-as='number' id='ltsDiesel'/>
                                                <div class='input-group-append'><span class='input-group-text'>L</span></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan='3' class='tblsubh'>Rendimiento</td>
                                    </tr>
                                    <tr>
                                        <td>Odóm. Inicial</td>
                                        <td class='inputCtr'>
                                            <div class='input-group'>
                                                <input class='form-control calckms' type='text' behave-as='number' id='odomInicio'/>
                                                <div class='input-group-append'><span class='input-group-text'>Kms</span></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Odóm. Final</td>
                                        <td class='inputCtr'>
                                            <div class='input-group'>
                                                <input class='form-control calckms' type='text' behave-as='number' id='odomFin'/>
                                                <div class='input-group-append'><span class='input-group-text'>Kms</span></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tara</td>
                                        <td class='inputCtr'>
                                            <div class='input-group'>
                                                <input class='form-control calckms' type='text' behave-as='number' id='pesosincarga'/>
                                                <div class='input-group-append'><span class='input-group-text'>Kgs.</span></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Peso de la carga</td>
                                        <td class='inputCtr espejeado'>
                                            <div class='input-group'>
                                                <input class='form-control calckms' type='text' behave-as='number' id='pesocarga'/>
                                                <div class='input-group-append'><span class='input-group-text'>Kgs.</span></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan='3' class='tblsubh'>Costo fijo</td>
                                    </tr>
                                    <tr>
                                        <td>Tiempo de ruta</td>
                                        <td class='inputCtr espejeado'>
                                            <div class='input-group'>
                                                <input class='form-control' type='text' behave-as='number' id='tiemporuta'/>
                                                <div class='input-group-append'><span class='input-group-text'>Días</span></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan='3' class='tblsubh'>Costo variable</td>
                                    </tr>
                                    <tr>
                                        <td>Peajes</td>
                                        <td class='inputCtr espejeado'>
                                            <div class='input-group'>
                                                <div class='input-group-prepend'><span class='input-group-text'>$</span></div>
                                                <input class='form-control' type='text' behave-as='number' id='peajes'/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Alimentos</td>
                                        <td class='inputCtr espejeado'>
                                            <div class='input-group'>
                                                <div class='input-group-prepend'><span class='input-group-text'>$</span></div>
                                                <input class='form-control' type='text' behave-as='number' id='alimentos'/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Hospedajes</td>
                                        <td class='inputCtr espejeado'>
                                            <div class='input-group'>
                                                <div class='input-group-prepend'><span class='input-group-text'>$</span></div>
                                                <input class='form-control' type='text' behave-as='number' id='hospedaje'/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Otros</td>
                                        <td class='inputCtr espejeado'>
                                            <div class='input-group'>
                                                <div class='input-group-prepend'><span class='input-group-text'>$</span></div>
                                                <input class='form-control' type='text' behave-as='number' id='otros'/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan='3' class='tblsubh'>Transporte Externo</td>
                                    </tr>
                                    <tr class='externa'>
                                        <td>Costo</td>
                                        <td class='inputCtr espejeado'>
                                            <div class='input-group'>
                                                <div class='input-group-prepend'><span class='input-group-text'>$</span></div>
                                                <input class='form-control' type='text' behave-as='number' id='costoext'/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class='externa'>
                                        <td>Repartos</td>
                                        <td class='inputCtr espejeado'>
                                            <div class='input-group'>
                                                <div class='input-group-prepend'><span class='input-group-text'>$</span></div>
                                                <input class='form-control' type='text' behave-as='number' id='repartosext'/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class='externa'>
                                        <td>Desvíos</td>
                                        <td class='inputCtr espejeado'>
                                            <div class='input-group'>
                                                <div class='input-group-prepend'><span class='input-group-text'>$</span></div>
                                                <input class='form-control' type='text' behave-as='number' id='desviosext'/>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div><div class='label' style='width:100%;padding:5px;box-sizing:border-box;'>Bitácora/Observaciones</div><textarea id='bitacoraConcEnv'></textarea></div>
                        </div>
                        <div class='col-md-4 py-4 m-0' style='background:rgba(237,238,240,0.7);'>
                            <input type='hidden' id='longitudpipam'/>
                            <table>
                                <thead>
                                    <tr><th colspan='3'>Resumen</th></tr>
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
                                    <tr><th  colspan='3'>Costo MXN</th></tr>
                                    <tr><th></th><th>Total</th><th>Unitario</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class='tblsubh'>Costo Fijo</td>
                                        <td id='totalF'  class='currency2'></td>
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
                                        <td class='tblsubh-2' colspan='3'>Transporte Propio</td>
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
                                        <td class='tblsubh-2' colspan='3'>Transporte Externo</td>

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
                                    <!--<tr><th coslpan='3'></th></tr>-->
                                    <!--<tr>-->
                                        <!--<th colspan='3'>-->
                                            <!--<button style='width:100%;box-sizing:border-box;' id='concluyeEnvio' identrega=''><i class='fa fa-save'></i> Guardar</button>-->
                                    <!--</th>-->
                                    <!--</tr>-->
                                </tfoot>
                            </table>
                        </div>
                        <div class='col-md-4 p-0 pl-2 m-0'>
                            <h5>Datos de la Carga</h5>
                            <div id='datos'>
                                
                            </div>
                            <h5>Historial de Costeo</h5>
                            <div id="logCosto">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--**************Terminan Modales********************-->

        <nav class="navbar navbar-expand-md navbar-dark" style="background:#024a74;">
            <a class="navbar-brand ml-3" href="../../index.php">Intranet</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">

                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="#">SmartRoad <span class="sr-only">(current)</span></a>
                    </li>
                </ul>
                <div class="form-inline my-2 mr-3 my-lg-0">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-user" style="font-size:0.8em;"></i> <?php echo $_SESSION["sessionInfo"]["userName"]; ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="#"><i class="fa fa-cog" style="font-size:0.8em;"></i> Configuración</a>
                            <a class="dropdown-item" href="password_change.php"><i class="fa fa-key" style="font-size:0.8em;"></i> Cambiar Password</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>


        <div class="container pt-4 h-100 md-12">
            <div class="row">
                <input type="text" id="searchString"/>
                <button id="buscarEntrega"><i class="fa fa-search"></i></button>
            </div>
            <div class="row">
                <div id="muestraEntregas" class="w-100"></div>
            </div>
        </div>
    </body>
</html>
