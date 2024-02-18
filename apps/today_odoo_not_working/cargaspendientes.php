<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "Cargas";
$path = "<a href='index.php'>Today</a> / " . $title;
$modulo = 10;

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
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
    <title><?php echo $title; ?></title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../../img/cargas.png" />
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <!--Propias-->
    <script type="text/javascript" src="js/functions_cargas.js?v=1.1.3"></script>
    <link rel="stylesheet" href="/intranet/css/sIndex.css">
    <link rel="stylesheet" href="css/style.css?v=1.2">

</head>

<body>

    <div id="loading" style="position:absolute;height:100vh;width:100vw;z-index:999;top:0;left:0;background:rgba(255,255,255,0.8);">
        <div class="d-flex justify-content-around align-items-center h-100 w-100">
            <span>Un momento <i class="fa fa-spin fa-spinner"></i></span>
        </div>
    </div>
    <div id='cuerpo'>
        <div id="divdoctos" class='modalContainer'>
            <div class="cuerpoModal">
                <div class="menuModal">
                    <div class='leftMenuModal'><button class='closeModal'><i class="fa fa-close"></i> Cerrar</button></div>
                    <div class='centerMenuModal'><span class='nombreventana'>DOCUMENTACIÓN DE EMBARQUE</span></div>
                    <div class='rightMenuModal'></div>
                </div>
                <div id='docsHeadCtr' class="d-flex align-items-center">
                    <div id='docshead'>
                        <table id='docsheadtbl'>
                            <tr>
                                <td class='etiquetah'>Folio:</td>
                                <td class='valh' id='foliodocs'></td>
                                <td class='etiquetah'>Planta:</td>
                                <td class='valh' id='plantadocs'></td>
                                <td class='etiquetah'>Fecha:</td>
                                <td class='valh' id='fechadocs'></td>
                                <td class='etiquetah'>Placas:</td>
                                <td class='valh' id='placasdocs'></td>
                                <td class='etiquetah'>Operario:</td>
                                <td><input type='text' size='10' id='responsableCarga' /></td>
                                <!--<td><button id='saveRespCarga' class='btn'><i class='fa fa-save'></i></button></td>-->
                            </tr>
                        </table>
                    </div>
                    <button id='saveAllBtn' class="btn btn-primary">Guardar Todo</button>
                    <button id='changeStatus' class="btn btn-success ml-2">Archivar</button>
                    <br style='clear:both;' />
                </div>
                <div class="contentModal" id='certsctr'>
                    <div class="centereddivs">
                        <table id='certs'>
                            <thead>
                                <tr>
                                    <th>Entrega</th>
                                    <th>Lote EPT</th>
                                    <th>Lote PT</th>
                                    <th>Sellos E</th>
                                    <th>Sellos D</th>
                                    <th>Remisiones ZK</th>
                                    <th>OCs</th>
                                    <!--<th>Guardar</th>-->
                                    <th>Certificado</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="contentModal">
                    <div class="centereddivs">
                        <!-- <div id='uplOcs' class='docSection' ondragleave='$(this).removeClass("dragover");' encabezado='Referencia Órdenes Cliente' folder='ocs'></div> -->
                        <div id='uplRems' class='docSection' ondragleave='$(this).removeClass("dragover");' encabezado='Remisiones' folder='remisiones'></div>
                        <div id='uplPesaje' class='docSection' ondragleave='$(this).removeClass("dragover");' encabezado='Pesaje' folder='pesaje'></div>
                        <div id='acusesContainer' class='docSection' encabezado='Acuses' folder='recRal'>
                            <div>Acuses de Recibo del Cliente</div>
                            <div class="linksContainer"></div>
                        </div>
                        <br style='clear:both;'>
                    </div>

                </div>
            </div>
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
        <div class="container-fluid">
            <!-- <button class="btn open-modal">Abrir M</button> -->
            <div id="filtros" class="row">
                <div class="col">
                    <input type='text' placeholder='Buscar' id="txtfilter" />
                    <button><i class='fas fa-times'></i></button>
                </div>
                <div class="col">
                    Cargas Pendientes: <span id="conteoCargas"></span>
                </div>

        </div>

        <div id="datos">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Planta</th>
                        <th>Folio SolCar</th>
                        <th>Sol. Carga ZK</th>
                        <th></th>
                    </tr>
                </thead>

                <tr>
                    <td><input type="date" id="inputFecha" class="form-control" /></td>
                    <td><input type="text" id="inputPlanta" class="form-control" /></td>
                    <td><input type="text" id="inputFolioSolCar" class="form-control" /></td>
                    <td>
                        <div id="divFileSolCarga" folder="solicitudes_carga"></div>
                        <!-- <input id="inputSolCarga" type="file" class="d-none" /> -->
                        <!-- <button id='btnInputSolCarga' class="btn" onclick="$('#inputSolCarga').click();"><i class="fa fa-folder"></i></button> -->
                    </td>

                    <td><button id="addReg" class='btn'>Agregar</button></td>
                </tr>
            </table>
        </div>

        <div id="resumen">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Planta</th>
                        <th>Folio SolCar</th>
                        <th>SO ZK</th>
                        <th>Remisión ZK</th>
                        <th>Albarán Raloy</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="tdFecha"></td>
                        <td id="tdPlanta"></td>
                        <td id="tdFolio"></td>
                        <td id="tdSOZK"></td>
                        <td>
                            <a class="btn" id='anchorRemisionZK' href="../../uploads"><i class="fa fa-folder"></i></button>

                        </td>

                        <td><input type="text" id="inputAlbaranRaloy" class="form-control" /></td>
                        <td><button class='btn'>Recibir</button></td>
                    </tr>
                </tbody>
            </table>
            <table class="table w-100">
                <thead>
                    <tr>
                        <th>Planta</th>
                        <th>Fecha de Carga</th>
                        <th>Folio</th>
                        <th>Litros</th>
                        <th>Placas</th>
                        <th>Detalle de Envíos</th>
                        <th>Papeleta</th>
                        <th>APT ZK</th>
                        <th>AMP RALOY</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!--                <div id="detalle">
                
                                </div>-->
    </div>
    <br style="clear:both;" />
    </div>

    </div>

</body>

</html>