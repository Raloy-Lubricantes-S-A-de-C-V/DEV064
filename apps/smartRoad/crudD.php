<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "Destinos";
$path = "<a href='index.php'>SmartRoad</a> / " . $title;
$modulo = 15;

require_once($_SERVER['DOCUMENT_ROOT']."/intranet/php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    header('Location: /today_zk/login.html?app=today/index.php');
}

if (!in_array($modulo, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: /today_zk/index.php?t=' . $_GET["t"]);
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
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/styleCrud.css">
        <link rel="stylesheet" href="/intranet/css/sIndex.css">
        <script type="text/javascript" src="js/fCrudD.js"></script>
    </head>
    <body>
        <div id="cover" class='modalContainer'>
            <div class="cuerpoModal"><i class='fa fa-spinner fa-spin fa-5x'></i></div>
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
                            <a class="dropdown-item" href="/today_zk/login.html"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <div id="subHeader">
            <div class="shLeft">
                <ul id="sessionInfo" class='selector'>
                    <li><button id="btnSaveDet"><i class="fa fa-save"></i> Guardar</button></li>
                </ul>
            </div>
            <div class="shRight">
                <ul>
                    <li><button fase='determinantes'><i class='fa fa-sync'></i>  Actualizar</button></li>
                </ul>
                
                
                
            </div>
            <br style="clear:both;"/>
        </div>
        <div id='cuerpo'>
            <div class='grupoEditable' id='divDet'>
                <div class='titleGrupo'><i class='fa fa-map-marker-alt'></i> Destinos</div>
                <!--<div class='filterCtr'><i class='fa fa-filter'></i> <input id="filterDet" type="text"/></div>-->
                <br style='clear:both;'/>
                <div class='contentGrupo'>
                    <hr/>
                    
                    <table id='tblAdminDet'class='display compact'>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Cliente</th>
                                <th>Determinante</th>
                                <th>Formato COA</th>
                                <th>Capacidad</th>
                                <th>Tanques</th>
                                <th>id em</th>
                                <th>Estado</th>
                                <th>Ciudad</th>
                                <th>Estado Orig.</th>
                                <th>Ciudad Orig.</th>
                                <th>Calle</th>
                                <th>Colonia</th>
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
