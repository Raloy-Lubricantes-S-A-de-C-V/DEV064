<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "Archivo de Cargas";
$path = "<a href='index.php'>Today</a> / " . $title;
$modulo = 17;

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
    <title><?php echo $title; ?></title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../../img/archive.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/icon.png">

    <!--jQuery-->
    <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

    <!--Fonts Awesome-->
    <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

    <!--DataTables-->
    <link rel="stylesheet" href="../../libs/DataTables-1.10.16/media/css/jquery.dataTables.min.css">
    <script type="text/javascript" src="../../libs/DataTables-1.10.16/media/js/jquery.dataTables.min.js"></script>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>


    <!--JqueryUI-->
    <link rel="stylesheet" href="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.css">
    <script type="text/javascript" src="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>

    <!--Propias-->
    <script type="text/javascript" src="js/functions_archive.js?v=1.0"></script>
    <link rel="stylesheet" href="/intranet/css/sIndex.css">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <div id='cuerpo'>
        <div id="divdoctos" class='modalContainer'>
            <div class="cuerpoModal">
                <div class="menuModal">
                    <div class='leftMenuModal'><button class='closeModal'><i class="fa fa-close"></i> Cerrar</button></div>
                    <div class='centerMenuModal'><span class='nombreventana'>Archivo</span></div>
                    <div class='rightMenuModal'></div>
                </div>
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
                            <td><button id='saveRespCarga' class='btn'><i class='fa fa-save'></i></button></td>
                        </tr>
                    </table>
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
                                    <th>Guardar</th>
                                    <th>Certificado</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="contentModal">
                    <div class="centereddivs">
                        <div id='uplOcs' class='docSection' ondragleave='$(this).removeClass("dragover");' encabezado='Referencia Órdenes Cliente' folder='ocs'></div>
                        <div id='uplRems' class='docSection' ondragleave='$(this).removeClass("dragover");' encabezado='Remisiones' folder='remisiones'></div>
                        <div id='uplPesaje' class='docSection' ondragleave='$(this).removeClass("dragover");' encabezado='Pesaje' folder='pesaje'></div>
                        <br style='clear:both;'>
                    </div>

                </div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg navbar-dark px-3 m-0 w-100" style="background:#024a74;">
            <a class="navbar-brand" href="/intranet/index.php">
                <img src="/today_zk/img/zarkruse-logo-light.svg" style="height:30px;padding-right:10px;" alt="SkyBlue" />
            </a>
            <div class="navbar-brand" href="#">
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

        <div id="filtros"><input type='text' placeholder='Buscar' id="txtfilter" /><button><i class='fas fa-times'></i></button> <input type="text" id="fec1" placeholder='Fecha Carga Inicial' /> <input type="text" id="fec2" placeholder='Fecha Carga Final' /> <span class='botonmostrar' id="getfiles">Mostrar</span></div>

        <div id="datos">
            <div id="resumen">
                <table>
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

</body>

</html>