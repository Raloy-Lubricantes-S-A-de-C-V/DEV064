<?php

header('Content-Type: text/html; charset=utf-8');

date_default_timezone_set('America/Mexico_City');



$title = "Ruta";/today.zar-kruse.com//php/

$path = "SmartRoad / " . $title;

$modulo = 2;



require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");

if (session_check($_GET["t"]) != 1) {

    header('Location: /intranet/login.html?app=today/index.php');

}



if (!in_array($modulo, $_SESSION["sessionInfo"]["idsModulos"])) {

    header('Location: /intranet/index.php?t=' . $_GET["t"]);

}



$dataconn = dataconn("intranet");

$mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

$mysqli->set_charset("utf8");

if ($mysqli->connect_errno) {

    echo "Failed to connect to MySQL: " . $mysqli->connect_error;

}



?>

<!DOCTYPE html>

<html>



<head>

    <title><?php echo $title; ?></title>

    <link rel="icon" type="image/png" href="../../../img/today.png" />

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <!--jQuery-->

    <script type="text/javascript" src="/today_zk/libs/jquery-3.2.1.min.js"></script>



    <!--Fonts Awesome-->

    <link rel="stylesheet" href="/today_zk/libs/fontawesome-free-5.4.2/css/all.min.css">



    <!--Bootstrap-->

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>



    <!--Propias-->

    <link rel="stylesheet" href="/intranet/css/sIndex.css">



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



        .intranet-modal {

            width: 100%;

            height: 100%;

            z-index: 999;

            background: rgba(0, 0, 0, .5);

            position: absolute;

            top: 0;

            left: 0;

            display: none;

        }

    </style>

    <script type="text/javascript">

        $(document).ready(function() {

            $(".intranet-modal-trigger").on("click", function() {

                console.log("click")

                var target = $(this).attr("intranet-modal-target");

                $(".intranet-modal").hide();

                $(".intranet-modal[intranet-modal-name='modal-hoja-ruta']").show();

            })



            $(".intranet-modal-close").click(function() {

                $(".intranet-modal").hide()

            });

        });

    </script>

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

                        <a class="dropdown-item" href="/intranet/login.html"><i class="fa fa-sign-out-alt" style="font-size:0.8em;"></i> Cerrar Sesión</a>

                    </div>

                </div>

            </div>

        </div>

    </nav>



    <?php

    $hojaRuta = "

    <div class='intranet-modal p-2' intranet-modal-name='modal-hoja-ruta'>

        <div class='container-fluid bg-white p-2 h-100'>

            <div class='row'>

                <div class='col'>

                    <div class='row'>

                        <div class='col-8'><h2>Hoja de Ruta</h2></div>

                        <div class='col-4 text-right'><button type='button' class='btn btn-secondary intranet-modal-close'>Cerrar</button></div>

                    </div>

                    <div class='row w-100 p-0 m-0'>

                        <div class='col intranet-modal-content p-2'>

                        <form>

                            <h3>Kilometraje</h3>

                            Kms Inicial: <input type='text'/>

                            Kms Final: <input type='text'/>

                            <h3>Diésel</h3>

                            Kms Inicial: <input type='text'/>

                            Kms Final: <input type='text'/>

                            <h3>Peajes</h3>

                            Kms Inicial: <input type='text'/>

                            Kms Final: <input type='text'/>

                            <h3>Viáticos</h3>

                            Kms Inicial: <input type='text'/>

                            Kms Final: <input type='text'/>

                            <h3>Otros</h3>

                            Kms Inicial: <input type='text'/>

                            Kms Final: <input type='text'/>

                        </form>

                            

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>";

    echo $hojaRuta;

    $id_user = $_SESSION["sessionInfo"]["userSession"];

    $sql = "SELECT e.id_entrega,pr.id_pre_ruteo,pr.pedido,pr.cliente,pr.determinante,pr.ltsSurtir,pr.eta,pr.nombreProducto,pr.estado,pr.municipio,pr.id_edoMpio FROM smartRoad_entregas e INNER JOIN smartRoad_pre_ruteo pr ON e.id_entrega=pr.id_entrega WHERE id_usuario_entrega=$id_user AND status_usuario_entrega=1 ORDER BY pr.eta, pr.estado";

    $result = $mysqli->query($sql);

    $form = "";

    $formTitle = "";

    $folio = "";

    if ($result->num_rows > 0) {



        while ($row = $result->fetch_assoc()) {

            $botonIniciar = "";

            $folio = "<h1>FOLIO " . $row["id_entrega"] . "</h1>";

            $formTitle = "<h3>Ruta:</h3>";

            $form .= "<div class='row w-100 d-flex p-2'>";

            $form .= "<div class='col d-flex flex-column p-2'>";

            $form .= "<div class='mr-2 text-primary'><b>" . $row["municipio"] . "," . $row["estado"] . "</b></div>";

            $form .= "<div><b>" . number_format($row["ltsSurtir"], 2) . "</b></div>";

            $form .= "<div class='text-secondary'><b>" . $row["eta"] . "</b></div>";

            $form .= "</div>";

            $form .= "<div class='col  p-2'>";

            $form .= $row["pedido"] . " ";

            $form .= $row["nombreProducto"] . "<br/>";

            $form .= $row["cliente"] . "/";

            $form .= $row["determinante"];

            $form .= "</div>";

            $form .= "<div class='col d-flex flex-column p-2'>";

            $form .= "<button id_entrega='" . $row["id_entrega"] . " id_pre_ruteo='" . $row["id_pre_ruteo"] . "' id_edoMpio='" . $row["id_edoMpio"] . "' type='button' class='w-100 btn btn-primary btn-checkpoint'>CheckPoint</button>";

            $form .= "</div>";



            $form .= "</div>";

        }

    } else {

        $form = '

                        <div class="row w-100 p-2 m-0">

                            <div class="input-group p-2 col-sm-6 mx-auto border border-success rounded">

                                <div class="input-group-prepend">

                                    <span class="input-group-text bg-success text-white" id="tagFolio">Folio</span>

                                </div>

                                <input id="inputFolio" type="text" class="form-control" aria-label="tagFolio" aria-describedby="tagFolio">

                            </div>

                        </div>';

    }

    ?>



    <div id="main-container" class="container-fluid p-2">

        <?php echo $folio; ?>

        <div class="d-flex align-items-center">

            <?php echo $botonIniciar; ?>

            <button type="button" class="btn btn-primary mr-2 intranet-modal-trigger" intranet-modal-target="modal-hoja-ruta">Hoja de Ruta</button>

            <button type="button" class="btn btn-warning mr-2">No es mi Ruta</button>

            <button type="button" class="btn btn-danger mr-2">Enviar Alerta</button>

        </div>

        <div class="row w-100 p-2 m-0">



            <div class="col-sm-12 col-lg-7 p-2">

                <?echo $formTitle,$form;?>

            </div>

            <div class="col-sm-12 col-lg-7 p-2">

                <h3>Bitácora</h3>

                <div class="text-secondary w-100">Los permisos de Siic deben estar activados</div>

                <?php

                $sql = "SELECT IDReporte,NomReporte FROM siic_reportes WHERE Active=1 ORDER BY NomReporte";

                $result = $mysqli->query($sql);

                while ($row = $result->fetch_assoc()) {

                    $reportes .= '<div class="form-check">';

                    $reportes .= '<input class="form-check-input checkReportes" type="checkbox" value="" id="checkReportes' . $row["IDReporte"] . '" id_reporte="' . $row["IDReporte"] . '">';

                    $reportes .= '<label class="form-check-label" for="checkReportes' . $row["IDReporte"] . '">';

                    $reportes .= $row["NomReporte"];

                    $reportes .= '</label>';

                    $reportes .= '</div>';

                }

                $result->free();

                $mysqli->close();

                echo $reportes;

                ?>

            </div>

        </div>

        <div class="modal">

            <div id="formData" class="w-100 m-0 p-0">



                <div class="w-100 d-flex py-2">

                    <button id="backBtn" class="btn btn-secondary ml-auto">Limpiar</button>

                    <button id="guardarUsuario" class="btn btn-primary mx-2">Guardar</button>

                </div>



                <input type="hidden" id="idUsuario" value="0" />



                <div class="row w-100 p-2 m-0">



                    <div class="input-group p-2 col-sm-12 col-md-6">

                        <div class="input-group-prepend">

                            <span class="input-group-text" id="label-placas">Placas</span>

                        </div>

                        <input id="inputPlacas" type="text" class="form-control" placeholder="Usuario" aria-label="Username" aria-describedby="label-placas">

                    </div>



                    <div class="input-group p-2 col-sm-12 col-md-6">



                        <div class="input-group-prepend">

                            <span class="input-group-text" id="tagNombre">Chofer</span>

                        </div>

                        <input id="inputNombre" type="text" class="form-control" placeholder="Nombre Completo" aria-label="Username" aria-describedby="tagNombre">

                    </div>

                </div>



                <div class="row w-100 p-2 m-0">



                    <div class="input-group p-2 col-sm-12 col-md-6">



                        <div class="input-group-prepend">

                            <span class="input-group-text" id="tagNombre">Folio</span>

                        </div>

                        <input id="inputNombre" type="text" class="form-control" placeholder="Nombre Completo" aria-label="Username" aria-describedby="tagNombre">

                    </div>



                    <div class="input-group p-2 col-sm-12 col-md-6">



                        <div class="input-group-prepend">

                            <span class="input-group-text" id="tagNombre">Carta Porte</span>

                        </div>

                        <input id="inputNombre" type="text" class="form-control" placeholder="Nombre Completo" aria-label="Username" aria-describedby="tagNombre">

                    </div>



                </div>



                <div class="row w-100 p-2 m-0">



                    <div class="input-group p-2 col-sm-12 col-md-6">

                        <div class="input-group-prepend">

                            <span class="input-group-text" id="tagNombre">Kms Inicial</span>

                        </div>

                        <input id="inputNombre" type="text" class="form-control" placeholder="Nombre Completo" aria-label="Username" aria-describedby="tagNombre">

                    </div>

                    <div class="input-group p-2 col-sm-12 col-md-6">

                        <div class="input-group-prepend">

                            <span class="input-group-text" id="basic-addon1">Kms Final</span>

                        </div>

                        <input id="inputUsuario" type="text" class="form-control" placeholder="Usuario" aria-label="Username" aria-describedby="basic-addon1">

                    </div>



                </div>



                <div class="row w-100 p-2 m-0">

                    <div class="input-group p-2 col-sm-12 col-md-6">

                        <div class="input-group-prepend">

                            <span class="input-group-text" id="tagArea">Área</span>

                        </div>

                        <input id="inputArea" type="text" class="form-control" placeholder="Área / Posición" aria-label="Userarea" aria-describedby="tagArea">

                    </div>

                    <div class="input-group p-2 col-sm-12 col-md-6">

                        <div class="input-group-prepend">

                            <span class="input-group-text" id="basic-addon1">e-mail</span>

                        </div>

                        <input id="inputEmail" type="email" class="form-control" placeholder="e-mail" aria-label="Email" aria-describedby="basic-addon1">

                    </div>





                </div>



                <div class="row w-100 p-2 m-0">

                    <div class="input-group p-2 col-sm-12 col-md-6">

                        <div class="input-group-prepend">

                            <span class="input-group-text" id="basic-addon1">Password</span>

                        </div>

                        <input id="inputPassword" type="password" class="form-control" placeholder="Password" aria-label="Username" aria-describedby="basic-addon1">

                    </div>



                    <div class="input-group p-2 col-sm-12 col-md-6">

                        <div class="input-group-prepend">

                            <span class="input-group-text" id="basic-addon1">Password</span>

                        </div>

                        <input id="inputPassword2" type="password" class="form-control" placeholder="Password" aria-label="Username" aria-describedby="basic-addon1">

                    </div>

                </div>







            </div>

        </div>













    </div>

</body>



</html>