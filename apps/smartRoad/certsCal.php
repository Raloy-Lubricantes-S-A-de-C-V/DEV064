<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["sessionInfo"]["userSession"]) || $_SESSION["sessionInfo"]["userSession"] == "") {
    header('Location: ../../login.html?app=smartRoad');
}
require_once("../../php/conexion.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <title>COA <?php echo$_GET["folio"]; ?></title>
        <link rel="icon" type="image/png" href="img/icono_camiones.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.debug.js"></script>-->

        <!--Propias-->
        <!--<link rel="stylesheet" href="css/styleCarga.css">-->
        <!--<script type="text/javascript" src="js/fCertsCal.js"></script>-->

    </head>
    <body>

        <div id='folioContainer'>
            FOLIO No. <span id='folio'><?php echo$_GET["folio"]; ?></span>
        </div>
        <div style='clear:both;'></div>
        <?php
        $folio = $_GET["folio"];
        $query = <<<SQL
  SELECT 
  e.placas,
  p.sellosDescarga sellosDescar,
  p.loteZK AS lote,
  p.cveProducto cveProd,         
  CONCAT(p.cveProducto, ' ' , p.nombreProducto) prod,
  p.densidad,
  p.concentracion,
  e.apariencia,
  f.sellosFijos,
  e.sellosEscotilla sellosEscot,
  SUM(p.ltsSurtir) lts 
FROM
  smartRoad_entregas e 
  INNER JOIN
  smartRoad_flota f 
  ON e.placas = f.placas 
  INNER JOIN
  smartRoad_pre_ruteo p 
  ON e.id_entrega = p.id_entrega 
WHERE e.id_entrega = $folio 
GROUP BY p.loteZK,
  p.sellosDescarga,p.cveProducto
ORDER BY p.sellosDescarga,p.cveProducto
SQL;
        $dataconn = dataconn("intranet");
        $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
        if ($mysqli->connect_errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->connect_error;
            return $respuesta;
        }
        $mysqli->query("SET NAMES 'utf8'");
        $ccs = "";
        if ($result = $mysqli->query($query)) {
            //CON DATOS
            if ($result->num_rows > 0) {
                $respuesta["status"] = 1;
                while ($row = $result->fetch_assoc()) {
                    $ccs .= "<li><a href='certificadoCalidad.php?folio=" . $folio . "&sd=" . $row["sellosDescar"] . "&prod=" . $row["cveProd"] . "'>" . $row["placas"] . " | " . $row["sellosDescar"] . " | " . $row["lote"] . " | " . $row["lts"] . " | " . $row["prod"] . "</a></li>";
                }
            } else {
                $respuesta["status"] = 2;
            }
        } else {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
        }
        ?>
        <ul>
            <?php echo $ccs; ?>
        </ul>
    </body>
</html>
