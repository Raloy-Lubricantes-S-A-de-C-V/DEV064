<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"])) {
    header('Location: login.html');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>INTRANET</title>
        <link rel="icon" type="image/png" href="img/iconzk.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--Bootstrap-->
        <link rel="stylesheet" href="libs/bootstrap-4.3.1/css/bootstrap.min.css">
        <script type="text/javascript" src="libs/bootstrap-4.3.1/js/bootstrap.bundle.min.js"></script>

        <!--form creator-->
        <link rel="stylesheet" href="libs/jsonform-master/deps/opt/spectrum.css">
        <script type="text/javascript" src="libs/jsonform-master/deps/underscore.js"></script>
        <script type="text/javascript" src="libs/jsonform-master/deps/opt/jsv.js"></script>
        <script type="text/javascript" src="libs/jsonform-master/lib/jsonform.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="css/sIndex.css">
        <script type="text/javascript" src="js/fPasswordChange.js"></script>
    </head>
    <body>
        <div id='loading' style='z-index:10000;padding:200px 46%;box-sizing:border-box;position:fixed;top:0;height:0;width:100%;height:100%;background:rgba(255,255,255,0.9);'>
            <img src='img/logo_skyblue.png' alt='Cargando...' height="85px"/><br/>Cargando ...
        </div>

        <nav class="navbar navbar-expand-md navbar-dark" style="background:#024a74;">
            <a class="navbar-brand ml-3" href="index.php">
                <!--<img src="img/logoZK.png" style="width:150px;padding-right:10px;" alt="SkyBlue"/>-->
                Intranet</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                </ul>
                <div class="form-inline my-2 mr-3 my-lg-0">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="index.php" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-user" style="font-size:0.8em;"></i> <?php echo $_SESSION["sessionInfo"]["userName"]; ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="#"><i class="fa fa-cog" style="font-size:0.8em;"></i> Configuración</a>
                            <a class="dropdown-item" href="password_change.php"><i class="fa fa-key" style="font-size:0.8em;"></i> Cambiar Password</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/intranet/login.html"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="container pt-4 h-100" id="cuerpo">
            <h3>Cambio de Contraseña</h3>
            
            <div class="card bg-light">
                <form class="card-body" id="result"></form>
                <div id="res" class="alert"></div>
            </div>
        </div>
    </body>
</html>

