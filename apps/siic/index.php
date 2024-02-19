<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "SIIC";
$path = $title;
$modulo = 3;

require_once($_SERVER['DOCUMENT_ROOT']."/intranet/php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    header('Location: /intranet/login.html?app=today/index.php');
}

if (!in_array($modulo, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: /intranet/index.php?t=' . $_GET["t"]);
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="<?php echo $title; ?>">
    <title><?php echo $title; ?></title>

    <!--jQuery-->
    <script type="text/javascript" src="/intranet/libs/jquery-3.2.1.min.js"></script>

    <!--Fonts Awesome-->
    <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

    <link href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=es" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="mdl/material.indigo-blue.min.css">
    <link rel="stylesheet" type="text/css" href="css/estilos.css">

    <link rel="shortcut icon" href="images/skico.ico">

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!--<script type="text/javascript" src="js/session.js"></script>-->
    <link rel="stylesheet" href="../../css/sIndex.css">
</head>

<body>
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
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
        <main class="mdl-layout__content">
            <section class="mdl-grid">
                <a name="misreportes"></a>
                <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-grid siic-reportes" style="margin-top:20px;">
                    <h4 class="mdl-cell mdl-cell--12-col">Mis Reportes</h4>
                    <div class="mdl-cell mdl-cell--12-col mdl-grid">
                        <div class="siic-list-reportes mdl-layout__content mdl-cell mdl-cell--top mdl-cell--4-col">
                            <ul class="siic-ul-reportes">
                            </ul>
                        </div>
                        <div class="mdl-cell mdl-cell--top mdl-cell--4-col">
                            <div class="mdl-card siic-card">
                                <div class="mdl-card__title mdl-card--border">
                                    <h2 class="mdl-card__title-text siic-selected__reporte-titulo"></h2>
                                </div>
                                <div class="mdl-card__supporting-text siic-selected__reporte-descrip"></div>
                                <div class="mdl-card__actions"></div>
                            </div>
                        </div>
                        <div class="mdl-cell mdl-cell--top mdl-cell--4-col">
                            <span class="siic-selected-report"></span>
                            <div class="siic-formulario">
                                <form action="" id="frmreportes">
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input type="date" id="fec1" class="mdl-textfield__input">
                                        <label class="mdl-textfield__label siic-label" for="fec1">Fecha Inicial</label>
                                    </div>
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input type="date" id="fec2" class="mdl-textfield__input">
                                        <label class="mdl-textfield__label siic-label" for="fec2">Fecha Final</label>
                                    </div>
                                    <!-- <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                                <input type="text" id="cliente" class="mdl-textfield__input">
                                                <label class="mdl-textfield__label" for="cliente">Cliente</label>
                                        </div>
                                        <div class="siic-center"> -->
                                    <button type="button" class="btn btn-primary" id="mostrar">Mostrar</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>
    <script type="text/javascript" src="mdl/material.min.js"></script>
    <script type="text/javascript" src="js/app.js?v=tknsst"></script>
</body>

</html>