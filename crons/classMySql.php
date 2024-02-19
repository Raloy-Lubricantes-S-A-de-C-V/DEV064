<?php

date_default_timezone_set('America/Mexico_City');
require_once("../php/conexion.php");
function executemultiquery($queries) {
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $affected = 0;
    $errors = [];
    foreach ($queries as $query) {
        if ($query != "") {
            if (!$mysqli->query($query)) {
                $errors[] = $mysqli->error;
            }
            $affected += $mysqli->affected_rows;
        }
    }
    $mysqli->close();
    return array("errors" => $errors, "affectedRows" => $affected);
}

function executeSimpleQuery($mysqli, $query) {
    $respuesta = array("error" => "", "query" => $query);
    if (!$mysqli->query($query)) {
        $respuesta["error"] = $mysqli->error;
    }
    return $respuesta;
}

function escape_values($v) {
    if (is_numeric($v)) {
        $str = "'" . $v . "'";
    } else {
        $v = str_replace("'", " ", $v);
        $pos = strpos($v, "/");
        if ($pos == true) {
            $datePart = substr($v, 0, 10);
            if (strlen($v) > 10) {
                $timePart = substr($v, -8, 8);
            } else {
                $timePart = "";
            }

            $y = substr($datePart, -4, 4);
            $m = substr($datePart, -7, 2);
            $d = substr($datePart, -10, 2);
            $v = $y . "-" . $m . "-" . $d . " " . $timePart;
        }
        $str = "'" . $v . "'";
    }
    return $str;
}