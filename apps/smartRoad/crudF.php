<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "Flota";
$path = "<a href='index.php'>SmartRoad</a> / " . $title;
$modulo = 13;

require_once($_SERVER['DOCUMENT_ROOT']."/intranet/php/session_check.php");
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

        <!-- bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!--Propias-->

        <!--Propias-->
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/styleCrud.css">
        <link rel="stylesheet" href="/intranet/css/sIndex.css">
        <script type="text/javascript" src="js/fCrudF.js"></script>
    </head>
    <body>
        <div id="cover" class='modalContainer'>
            <div class="cuerpoModal"><i class='fa fa-spinner fa-spin fa-5x'></i></div>
        </div>
        <nav class="navbar navbar-expand-lg navbar-dark px-3 m-0 w-100" style="background:#024a74;">
            <a class="navbar-brand" href="/intranet/index.php">
                <img src="/intranet/img/zarkruse-logo-light.svg" style="height:30px;padding-right:10px;" alt="SkyBlue" />
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
            <div class="shLeft">
                <ul id="sessionInfo" class='selector'>
                    <li><button id="btnSaveFlota"><i class="fa fa-save"></i> Guardar</button></li>
                </ul>
            </div>
            <div class="shRight">
                <ul>
                    <li><button fase='flota'><i class='fa fa-sync'></i>  Actualizar</button></li>
                    <!--<li><a href="index.php"><i class='fa fa-road'></i>  Ruteo</a></li>-->
<!--                    <li><a class='current' href="crud.php"><i class='fa fa-cog'></i> </a></li>-->
                    <!--<li><a href="reportes.php"><i class='fa fa-line-chart'></i> Reportes</a></li>-->
                </ul>
            </div>
            <br style="clear:both;"/>
        </div>
        <div id='cuerpo'>

            <div class='grupoEditable' id='divFlota'>
                <div class='titleGrupo'><i class='fa fa-truck-moving'></i> Flota</div>
                <!--                <div class="menuGrupo">
                                    <div class='leftMenuGrupo'><div id='avisosFlota'></div></div>
                                    <div class='rightMenuGrupo'><button id="btnSaveFlota"><i class="fa fa-save"></i> Guardar</button></div>
                                </div>-->
                <br style='clear:both;'/>
                <div class='contentGrupo'>
                    <div id='configCamion'>
                        <div id='datosgrales'>
                            <table>
                                <thead>
                                    <tr><th colspan='3'>Datos Básicos</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Placas</td>
                                        <td><input  id='placas' type='text'/></td>
                                    </tr>
                                    <tr>
                                        <td>Producto</td>
                                        <td>
                                            <select id='producto'>
                                                <option value=''></option>
                                                <option value='SkyBlue'></option>
                                                <option value='Aceite'></option>
                                                <option value='Anticongelante'></option>
                                                <option value='Limpia Parab.'></option>
                                                <option value='Básicos'></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Capacidad máxima</td>
                                        <td><input  id='capacidad' type='text'/></td>
                                    </tr>
                                    <tr>
                                        <td>Sellos fijos</td>
                                        <td><input  id='sellos' type='text'/></td>
                                    </tr>
                                    <tr>
                                        <td>Sellos fijos</td>
                                        <td><input  id='sellos' type='text'/></td>
                                    </tr>
                                </tbody>
                            </table>
                            <table>
                                <thead><tr><th colspan='3'>Propietario</th></tr></thead>
                                <tbody>
                                    <tr>
                                        <td>Propex</td>
                                        <td><input id='propex' type='text'/></td>
                                    </tr>
                                    <tr>
                                        <td>Línea de Transporte</td>
                                        <td><input id='lineatrans' type='text'/></td>
                                    </tr>

                                </tbody>
                            </table>
                            <table>
                                <thead><tr><th colspan='3'>Verificación Fisico Mecánica</th></tr></thead>
                                <tbody>
                                    <tr>
                                        <td>Última Vez</td>
                                        <td><input id='vfmlt' type='text'/></td>
                                    </tr>
                                    <tr>
                                        <td>Próxima</td>
                                        <td><input id='vfmnt' type='text'/></td>
                                    </tr>

                                </tbody>
                            </table>
                            <span>Placas</span><input type='text'/>
                            <span>Producto</span><input type='text'/>


                            <div id='tiposnom012'>

                                <table>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Clase</th>
                                            <th>Nomenclatura</th>
                                            <th>Ejes</th>
                                            <th>Llantas</th>
                                            <th>Imagen</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id='datoscamion'>
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan='3'>Dimensiones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Alto</td>
                                        <td><input id='altov' value='0'/></td>
                                        <td>mts</td>
                                    </tr>
                                    <tr>
                                        <td>Largo</td>
                                        <td><input id='largov' value='0'/></td>
                                        <td>mts</td>
                                    </tr>
                                    <tr>
                                        <td>Ancho</td>
                                        <td><input id='anchov' value='0'/></td>
                                        <td>mts</td>
                                    </tr>
                                    <tr>
                                        <td>Peso</td>
                                        <td><input id='pesov' value='0'/></td>
                                        <td>mts</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan='3'>Combustible</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Rendimiento Cargado</td>
                                        <td><input id='rendcarv' value='0'/></td>
                                        <td>km/L</td>
                                    </tr>
                                    <tr>
                                        <td>Rendimiento Vacío</td>
                                        <td><input id='rendvacv' value='0'/></td>
                                        <td>km/L</td>
                                    </tr>
                                    <tr>
                                        <td>Costo Diésel</td>
                                        <td><input id='dieselv' value='0'/></td>
                                        <td>MXN/L</td>
                                    </tr>
                                    <tr>
                                        <td>Tanque</td>
                                        <td><input id='tanquedieselv' value='0'/></td>
                                        <td>L</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan='3'>Carga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Unidad</td>
                                        <td colspan='2'>
                                            <select id='unidadcarga'>
                                                <option value='L'>Lts</option>
                                                <option value='Kg'>Kgs</option>
                                                <option value='MT'>MTs</option>
                                                <option value='Pallets'>Pallets</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Peso Unidad Carga</td>
                                        <td><input id='pesounitcarv' value='0'/></td>
                                        <td>Kg</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan='3'>Depreciación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Valor</td>
                                        <td><input type='text' id='valorv'/></td>
                                        <td>Mxn</td>
                                    </tr>
                                    <tr>
                                        <td>Vida útil</td>
                                        <td><input id='vidautilv' value='0'/></td>
                                        <td>meses</td>
                                    </tr>
                                    <tr>
                                        <td>Valor Rescate</td>
                                        <td><input id='vrescatev' value='0'/></td>
                                        <td>MXN</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr/>
                    <table id='tblAdminFlota' class='display nowrap compact'>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Placas</th>
                                <th>Capacidad</th>
                                <th>Sellos Fijos</th>
                                <th>No. Ejes</th>
                                <th>Propia/Externa</th>
                                <th>Propietario</th>
                                <th>Modelo Cta. Lts</th>
                                <th>Fecha Últ. Verificación</th>
                                <th>Fecha Próx. Verificación</th>
                                <th>Fecha Últ. calibración</th>
                                <th>Fecha Próx. calibración</th>
                                <th>Status</th>
                                <th>URL Carpeta Drive</th>
                                <th>Depreciación/Mes</th>
                                <th>Otros fijos/Mes</th>
                                <th>Descr. Otros Fijos</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot></tfoot>
                    </table>
                </div>
            </div> 
        </div>
    </body>
</html>
