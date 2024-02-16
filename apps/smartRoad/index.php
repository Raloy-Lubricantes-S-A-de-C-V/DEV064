<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "SmartRoad";
$path = $title;
$modulo = 2;

require_once($_SERVER['DOCUMENT_ROOT']."/today_zk/php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    header('Location: /today_zk/login.html?app=today/index.php');
}

if (!in_array($modulo, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: /today_zk/index.php?t=' . $_GET["t"]);
}

$query = "  SELECT 
                modulos.id_modulo idmod,
                nombreModulo nommod,
                descModulo descmod,
                relURL urlmod,
                ordenMostrar,
                relIconURL iconmod
            FROM
                framework_modulos modulos 
            WHERE id_modulo IN (" . $_SESSION["sessionInfo"]["strIdsMods"] . ") AND moduloPadre=$modulo
            ORDER BY ordenMostrar";


?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo $title; ?></title>
    <link rel="icon" type="image/png" href="/today_zk/img/route.png" />
    <meta charset="UTF-8">

    <link rel="icon" href="/today_zk/img/route.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="/img/route.png" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="manifest" href="/intranet/manifest.webmanifest">

    <!--jQuery-->
    <script type="text/javascript" src="/today_zk/libs/jquery-3.2.1.min.js"></script>

    <!--Fonts Awesome-->
    <link rel="stylesheet" href="/today_zk/libs/fontawesome-free-5.4.2/css/all.min.css">

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!--Propias-->
    <link rel="stylesheet" href="/intranet/css/sIndex.css">
    <script type="text/javascript" src="/intranet/js/findex.js?v=tkn_cambioplanta"></script>

    <style>
        html,
        body {
            overscroll-behavior: none;
            position: fixed;
            height: 100vh;
            width: 100vw;
        }

        #main-container {
            width: 100vw;
            height: 100vh;
            overflow: auto;
            -webkit-overflow-scrolling: touch !important;
        }
    </style>

</head>

<body>
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

    <div id="main-container" class="container-fluid">
        <div class="row p-2 m-0 w-100">
            <?php
            $idusuario = $_SESSION["sessionInfo"]["userSession"];
            $dataconn = dataconn("intranet");
            $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
            if ($mysqli->connect_errno) {
                echo "Failed to connect to MySQL: " . $mysqli->connect_error;
            }
            $result = $mysqli->query($query);
            $divs = "";
            while ($row = $result->fetch_assoc()) {
                $img = ($row["iconmod"] !== "") ? "<img src='/today_zk/img/" . $row["iconmod"] . "' alt='icon'/>" : '';
                $divs .= '<div class="col-sm-4 col-md-3 col-lg-2 my-2"><div class="tag p-2 text-center d-flex flex-column align-items-center justify-content-center" app="' . $row["urlmod"] . '"><div>' . $img . '</div><div class="tag-maintext">' . utf8_encode($row["nommod"]) . '</div><div class="tag-subtext">' . utf8_encode($row["descmod"]) . '</div></div></div>';
                $_SESSION["sessionInfo"]["idsModulos"][] = $row["idmod"];
            }
            if ($divs != "") {
                echo $divs;
            } else {
                echo "No hay módulos asignados";
            }
            ?>
        </div>
    </div>
</body>

</html>