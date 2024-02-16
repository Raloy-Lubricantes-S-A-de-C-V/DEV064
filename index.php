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
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="<?php echo $title; ?>">
    <link rel="manifest" href="/intranet/manifest.webmanifest">

    <!--jQuery-->
    <script type="text/javascript" src="libs/jquery-3.2.1.min.js"></script>

    <!--Fonts Awesome-->
    <link rel="stylesheet" href="libs/fontawesome-free-5.4.2/css/all.min.css">

    <!--Bootstrap-->
    <link rel="stylesheet" href="libs/bootstrap-4.3.1/css/bootstrap.min.css">
    <script type="text/javascript" src="libs/bootstrap-4.3.1/js/bootstrap.bundle.min.js"></script>

    <!--Propias-->
    <link rel="stylesheet" href="css/sIndex.css">
    <script type="text/javascript" src="js/findex.js?v=1.0"></script>
</head>

<body>
    <div id='loading' style='z-index:10000;padding:200px 46%;box-sizing:border-box;position:fixed;top:0;height:0;width:100%;height:100%;background:rgba(255,255,255,0.9);'>
        <img src='img/logo_skyblue.png' alt='Cargando...' height="85px" /><br />Cargando ...
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark px-3 m-0 w-100">
        <a class="navbar-brand" href="/intranet/index.php">
            <img src="/today_zk/img/zarkruse-logo-light.svg" style="height:30px;padding-right:10px;" alt="SkyBlue" />
        </a>
        <a class="navbar-brand" href="#">
            Intranet
        </a>
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
                        <a class="dropdown-item" href="password_change.php"><i class="fa fa-key" style="font-size:0.8em;"></i> Cambiar Password</a>
                        <a class="dropdown-item" href="login.html"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="container-fluid pt-4 h-100" id="cuerpo">
        <div class="row w-100 d-flex justify-content-around">


            <?php
            include("php/conexion.php");
            $idusuario = $_SESSION["sessionInfo"]["userSession"];
            $modulos = implode(",", $_SESSION["sessionInfo"]["idsModulos"]);
            $query = "SELECT 
                        modulos.id_modulo idmod,
                        nombreModulo nommod,
                        descModulo descmod,
                        relURL urlmod,
                        ordenMostrar,
                        relIconURL iconmod 
                    FROM
                        framework_modulos modulos
                    WHERE id_modulo IN ($modulos) 
                        AND moduloPadre = 0 
                    ORDER BY ordenMostrar,
                        idmod";
            $dataconn = dataconn("intranet");
            $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
            if ($result = $mysqli->query($query)) {
                $divs = "";
                while ($row = $result->fetch_assoc()) {
                    $img = ($row["iconmod"] !== "") ? "<img src='img/" . $row["iconmod"] . "' alt='icon'/>" : '';
                    $divs .= '<div class="p-4 m-2 col-sm-3 col-lg-2 tag position-relative d-flex flex-column align-items-center justify-content-around" data-toggle="tooltip" title="' . utf8_encode($row["descmod"]) . '" data-placement="bottom" app="' . utf8_encode($row["urlmod"]) . '">';
                    $divs .= $img;
                    $divs .= '<span class="mt-2 tag-maintext">' . $row["nommod"] . '</span>';
                    $divs .= '</div>';
                    $_SESSION["sessionInfo"]["idsModulos"][] = $row["idmod"];
                }
                if ($divs != "") {
                    echo $divs;
                } else {
                    echo "No hay módulos asignados";
                }
            } else {
                echo "error" . $mysqli->error;
            }
            ?>



        </div>

    </div>
</body>

</html>