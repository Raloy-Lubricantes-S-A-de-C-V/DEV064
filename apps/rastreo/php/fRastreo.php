<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function getOrders()
{
    $whereFolio=($_GET["folio"] != "") ? " AND id_entrega='" . $_GET["folio"] . "'" : "";
    $wherePedido=($_GET["numPedido"] != "") ? " AND pedido='" . $_GET["numPedido"] . "'" : "";
    $today = date("Y-m-d");
    $sql = "SELECT * FROM smartRoad_pre_ruteo WHERE eta>='$today' AND status<>'Terminado' $whereFolio $wherePedido ORDER BY eta";
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $tbody = "";
    if ($result = $mysqli->query($sql)) {
        if ($result->num_rows == 0) {
            $respuesta["status"] = 2;
            $respuesta["error"] = "Sin Datos";
        } else {
            $colores = array("pre" => "red", "carga" => "orange", "cargado" => "blue", "camino" => "green");
            while ($row = $result->fetch_assoc()) {
                $color = $colores[$row["status"]];
                $tbody .= "<tr>";
                $tbody .= "<td>" . $row["pedido"] . " <span style='color:$color;'>" . $row["status"] . "</span></td>";
                $tbody .= "<td>" . $row["fechaPedido"] . "</td>";
                $tbody .= "<td>" . $row["id_entrega"] . "-" . $row["id_pre_ruteo"] . "</td>";
                $tbody .= "<td>" . $row["eta"] . "</td>";
                $tbody .= "<td>" . $row["cveCliente"] . " / " . $row["determinante"] . "</td>";
                $tbody .= "<td>" . $row["cliente"] . " / " . substr($row["nombreDeterminante"], 0, 12) . "</td>";
                $tbody .= "<td>" . $row["cveProducto"] . "</td>";
                $tbody .= "<td>" . $row["nombreProducto"] . "</td>";
                $tbody .= "<td>" . $row["municipio"] . "," . $row["estado"] . "</td>";
                $tbody .= "<td>" . number_format($row["ltsSurtir"], 2) . "</td>";
                $tbody .= "</tr>";
            }
            $respuesta["status"] = 1;
            $respuesta["tbody"] = $tbody;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}
