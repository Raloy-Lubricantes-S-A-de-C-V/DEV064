<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]) || !in_array(9, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: ../../login.html?app=today/index.php');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Configuración</title>
        <link rel="icon" type="image/png" href="../../img/today.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--Propias-->
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="../../css/sIndex.css">
        <script type="text/javascript" src="../../js/findex.js"></script>
    </head>
    <body>
        <header>
            <div id='leftHeader'>
                <a id="logo" href="../../index.php"><img src="../../img/Logo Skyblue horizontal.png" alt="SkyBlue"/></a>
                <span id="appName" ><i class='fas fa-sun'></i> Today</span>
            </div>
            <div id='rightHeader'>
                <tab><i class="fas fa-user"></i> <span id="userSession"><?php echo $_SESSION["sessionInfo"]["userName"]; ?></span></tab>
                <tab><i class="fa fa-clock-o"></i> <span><?php echo $_SESSION["sessionInfo"]["sessionDate"]; ?></tab>
                <tab><a href='../../login.html'><i class="fa fa-sign-out-alt"></i></a></tab>
            </div>
        </header>
        <div id="cuerpo">
            <?php
            include("../../php/connParam.php");
            $idusuario = $_SESSION["sessionInfo"]["userSession"];
            $conn = connParams();

            $mysqli = new mysqli($conn["server"], $conn["user"], $conn["password"], $conn["db"]);
            if ($mysqli->connect_errno) {
                echo "Failed to connect to MySQL: " . $mysqli->connect_error;
            }
            $query = "SELECT 
                modulos.id_modulo idmod,
                nombreModulo nommod,
                descModulo descmod,
                relURL urlmod,
                ordenMostrar,
                relIconURL iconmod
            FROM
                framework_modulos modulos 
            WHERE id_modulo IN (".$_SESSION["sessionInfo"]["strIdsMods"].") AND moduloPadre=9
            ORDER BY ordenMostrar";
            $result = $mysqli->query($query);
            $divs = "";
            while ($row = $result->fetch_assoc()) {
                $img = ($row["iconmod"] !== "") ? "<img src='../../img/" . $row["iconmod"] . "' alt='icon'/>" : '';
                $divs.='<div class="tag" app="' . $row["urlmod"] . '">' . $img . '<span>' . utf8_encode($row["nommod"]) . '</span><descripcion>' . utf8_encode($row["descmod"]) . '</descripcion></div>';
                $_SESSION["sessionInfo"]["idsModulos"][] = $row["idmod"];
            }
            if ($divs != "") {
                echo $divs;
            } else {
                echo "No hay módulos asignados";
            }
            ?>
        </div>
    </body>
</html>