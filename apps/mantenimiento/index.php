<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"])) {
    header('Location: ../../login.html');
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>INTRANET</title>
    <link rel="icon" type="image/png" href="/today_zk/img/iconzk.png" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--jQuery-->
    <script type="text/javascript" src="/today_zk/libs/jquery-3.2.1.min.js"></script>

    <!--Fonts Awesome-->
    <link rel="stylesheet" href="/today_zk/libs/fontawesome-free-5.4.2/css/all.min.css">

    <!--Bootstrap-->
    <link rel="stylesheet" href="/today_zk/libs/bootstrap-4.3.1/css/bootstrap.min.css">
    <script type="text/javascript" src="/today_zk/libs/bootstrap-4.3.1/js/bootstrap.bundle.min.js"></script>


    <!--Propias-->
    <script type="text/javascript" src="js/fIndex.js"></script>
    <!-- <link rel="stylesheet" href="css/style.css"> -->
    <!-- <link rel="stylesheet" href="../../css/bhc.css"> -->
    <!-- <link rel="stylesheet" href="../../css/sIndex.css"> -->
    <style>
        .tab {
            cursor: pointer;
            border-radius: 5px;
        }

        .tab:hover {
            background: #f8f9fa;
        }

        .tab img {
            height: 50px;
        }
    </style>

</head>

<body>

    <!-- Antes: http://mantenimiento.zar-kruse.com/public/ -->

    <nav class="navbar navbar-expand-lg navbar-dark" style="background:#024a74;">
        <a class="navbar-brand ml-3" href="/intranet/index.php">
            <img src="/today_zk/img/zarkruse-logo-light.svg" style="height:30px;padding-right:10px;" alt="SkyBlue"/>
        </a>
        <a class="navbar-brand ml-3" href="#">
            LogBook
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
                        <a class="dropdown-item" href="#"><i class="fa fa-cog" style="font-size:0.8em;"></i> Configuración</a>
                        <a class="dropdown-item" href="/intranet/password_change.php"><i class="fa fa-key" style="font-size:0.8em;"></i> Cambiar Password</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/intranet/login.html"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div id="cuerpo" class="container-fluid">
        <div class="row">
            <div class="col d-flex">
                <?php
                include("../../php/conexion.php");
                $idusuario = $_SESSION["sessionInfo"]["userSession"];
                $dataconn = dataconn("intranet");
                $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
                if ($mysqli->connect_errno) {
                    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
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
                            WHERE id_modulo IN (" . $_SESSION["sessionInfo"]["strIdsMods"] . ") AND moduloPadre=18
                            ORDER BY 
                                ordenMostrar";
                $result = $mysqli->query($query);
                $divs = "";
                while ($row = $result->fetch_assoc()) {
                    $img = ($row["iconmod"] !== "") ? "<img src='../../img/" . $row["iconmod"] . "' alt='icon'/>" : '';
                    $divs .= '<div class="tab m-2 p-3 text-center d-flex flex-column" app="' . $row["urlmod"] . '"><div>' . $img . '</div><div>' . utf8_encode($row["nommod"]) . '</div><div class="text-secondary">' . utf8_encode($row["descmod"]) . '</div></div>';
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
    </div>
</body>

</html>