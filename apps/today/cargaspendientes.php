<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "Cargas";
$path = "<a href='index.php'>Today</a> / " . $title;
$modulo = 10;

require_once($_SERVER['DOCUMENT_ROOT'] . "/today_zk/php/session_check.php");
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
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
    <div id="modal-recepcion-raloy" class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">RECEPCIÓN EN ALMACÉN DEL CLIENTE</h4>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            <div class="col text-danger">
                                <span>Folio: </span>
                                <span id="recepcion-raloy-folio"></span>
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col">

                                <span class="w-100">Número de Recibo:</span>
                                <input id="input-num-recepcion" class="form-control w-100" type="text" />
                            </div>
                            <div class="col">
                                <div id='uplRecRal' class='docSection w-100' ondragleave='$(this).removeClass("dragover");' encabezado='Documento de Recepción' folder='recRal'>
                                    <div class="linksContainer"></div>
                                </div>
                                <!-- <div class="d-flex justify-content-center align-items-center" style="height: 80px;background: #edeaee;cursor: pointer;border-style: dashed;border-color: rgb(97,158,230);">
                                    <div><i class="fa fa-file-o" style="margin-right: 8px;"></i><span>Subir APT/IN</span></div>
                                </div> -->
                            </div>
                        </div>
                        <div class="row m-0 p-0">
                            <div class="col-12">
                                <table class="table table-responsive" id="detalles-recepcion">
                                    <thead>
                                        <tr>
                                            <th>Remisión</th>
                                            <th>OC</th>
                                            <th>Cliente</th>
                                            <th>Enviar</th>
                                            <th>Producto</th>
                                            <th>Litros</th>
                                            <th>% Urea</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button><button id="btn-save-recepcion" class="btn btn-primary" type="button">Guardar</button></div>
            </div>
        </div>
    </div>

    <div id="loading" style="position:absolute;height:100vh;width:100vw;z-index:999;top:0;left:0;background:rgba(255,255,255,0.8);">
        <div class="d-flex justify-content-around align-items-center h-100 w-100">
            <span>Un momento <i class="fa fa-spin fa-spinner"></i></span>
        </div>
    </div>
    <div id="modal-doctos" class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex">
                        <h4 class="modal-title" style="color: rgb(8,12,113);">Documentación de Embarque</h4>
                    </div>
                    <div class="ms-auto">
                        <div class="ms-2"><span class="ms-2" style="font-weight: bold;color: rgb(4,21,79);">Ruta:</span><span id="modal-span-route"></span></div>
                        <div class="ms-2"><span class="ms-2" style="font-weight: bold;color: rgb(4,21,79);">Pedido:</span><span id="modal-span-pedido"></span></div>
                        <div class="ms-2"><span class="ms-2" style="font-weight: bold;color: rgb(4,21,79);">Albarán:</span><span id="modal-span-albaran"></span></div>
                        <div class="ms-2"><span class="ms-2" style="font-weight: bold;color: rgb(4,21,79);">Litros:</span><span id="modal-span-litros"></span></div>
                    </div>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between">
                        <div class="dropbox-container overflow-auto mx-1 p-2 border border-rounded" style="width:33%;background: #f6f6f6;" target-folder="remisiones">
                            <input type='file' class='input-file d-none' multiple='multiple' class='dropbox-input' target='target' id='fileinput-remisiones' />
                            <h5 style="color: rgb(8,12,113);">Remisiones ZK</h5>
                            <h5 input-file-target='fileinput-remisiones' class="text-center text-secondary dropbox d-flex justify-content-center align-items-center mt-1 p-2" style="background: #e3f4fd;border-style: dashed;border-color: #CACACA;">Seleccione o arrastre un archivo <i class="fa fa-upload"></i></h5>
                            <p class="w-100 dropbox-messages"></p>
                            <div class="files-container" style="height: 200px;"></div>
                        </div>
                        <div class="dropbox-container overflow-auto mx-1 p-2 border border-rounded" style="width:33%;background: #f6f6f6;" target-folder="tickets_bascula">
                            <input type='file' class='input-file d-none' multiple='multiple' class='dropbox-input' target='target' id='fileinput-bascula' />
                            <h5 style="color: rgb(8,12,113);">Báscula</h5>
                            <h5 input-file-target='fileinput-bascula' class="text-center text-secondary dropbox d-flex justify-content-center align-items-center mt-1 p-2" style="background: #e3f4fd;border-style: dashed;border-color: #CACACA;">Seleccione o arrastre un archivo <i class="fa fa-upload"></i></h5>
                            <p class="w-100 dropbox-messages"></p>
                            <div class="files-container" style="height: 200px;"></div>
                        </div>
                        <div class="dropbox-container overflow-auto mx-1 p-2 border border-rounded" style="width:33%;background: #f6f6f6;" target-folder="apt_ins">
                            <input type='file' class='input-file d-none' multiple='multiple' class='dropbox-input' target='target' id='fileinput-aptins' />
                            <h5 style="color: rgb(8,12,113);">Entrada Raloy</h5>
                            <h5 input-file-target='fileinput-aptins' class="text-center text-secondary dropbox d-flex justify-content-center align-items-center mt-1 p-2" style="background: #e3f4fd;border-style: dashed;border-color: #CACACA;">Seleccione o arrastre un archivo <i class="fa fa-upload"></i></h5>
                            <p class="w-100 dropbox-messages"></p>
                            <div class="files-container" style="height: 200px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark px-3 m-0 w-100" style="background:#024a74;">
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
    <div style="width:100vw;font-size:11px;" class="p-2">
        <table id="tabla-cargas" class="display compact" style="width:100%">
            <thead>
                <tr style="background:#0C2E6C;color:#fff;">
                    <th>Ruta</th>
                    <th>Planta</th>
                    <th>Fecha de Carga</th>
                    <th>Producto</th>
                    <th>Pedido ZK</th>
                    <th>Remisión ZK</th>
                    <th>Factura ZK</th>
                    <th>Determinante</th>
                    <th>Archivos</th>
                    <th>Litros</th>
                    <th>IN Raloy</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <!-- <div class="container-fluid">
        <button class="btn open-modal">Abrir M</button>
        <div id="filtros" class="row">
                <div class="col">
                    <input type='text' placeholder='Buscar' id="txtfilter" />
                    <button><i class='fas fa-times'></i></button>
                </div>
                <div class="col">
                    Cargas Pendientes: <span id="conteoCargas"></span>
                </div>

            </div>
        <div class="row m-0 p-0 h-100 w-100" style='overflow-y:auto;'>
            <div class="col col-12 m-0 p-2">
                <table id="tabla-cargas" class="display compact" style="width:100%">
                    <thead>
                        <tr>
                            <th>Ruta</th>
                            <th>Planta</th>
                            <th>Fecha de Carga</th>
                            <th>Producto</th>
                            <th>Pedido ZK</th>
                            <th>Remisión ZK</th>
                            <th>Factura ZK</th>
                            <th>Determinante</th>
                            <th>Archivos</th>
                            <th>Litros</th>
                            <th>IN Raloy</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div> -->


</body>

</html>