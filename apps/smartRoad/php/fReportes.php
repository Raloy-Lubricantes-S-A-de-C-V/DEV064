<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
require_once ("../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function llenaSelReportes() {
    $query = <<<SQL
         SELECT * FROM smartRoad_reportes
SQL;
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows > 0) {
            $options = "<option value=''></option>";
            while ($row = $result->fetch_assoc()) {
                $options.="<option value='" . $row["id_reporte"] . "'>" . utf8_encode($row["nombreCorto"]) . "</option>";
            }
            $respuesta["options"] = $options;
            $respuesta["status"] = 1;
        } else {
            $respuesta["status"] = 2;
            $respuesta["error"] = "Sin Datos";
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function ltsxtransporte() {
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $query = <<<SQL
    SELECT DISTINCT periodo FROM
    (SELECT 
      DATE_FORMAT(e.fecha_carga, "%Y-%m") periodo,
      f.propietario,
      COUNT(DISTINCT e.id_entrega) entregas,
      SUM(e.litros) lts
    FROM
      smartRoad_flota f 
      INNER JOIN
      smartRoad_entregas e 
      ON f.placas = e.placas 
    WHERE e.STATUS <> "carga cancelada"
    GROUP BY DATE_FORMAT(e.fecha_carga, "%Y-%m"),
      f.propietario) todo ORDER BY periodo
SQL;
    $labels = [];
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {

            $labels[] = $row["periodo"];
        }
        $result->close();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $mysqli->close();
        return $respuesta;
    }
    $query = <<<SQL
    SELECT propietario,sum(lts) litros FROM
    (SELECT 
      DATE_FORMAT(e.fecha_carga, "%Y-%m") periodo,
      f.propietario,
      COUNT(DISTINCT e.id_entrega) entregas,
      SUM(e.litros) lts
    FROM
      smartRoad_flota f 
      INNER JOIN
      smartRoad_entregas e 
      ON f.placas = e.placas
    WHERE e.STATUS <> "carga cancelada"
    GROUP BY DATE_FORMAT(e.fecha_carga, "%Y-%m"),
      f.propietario) todo
            group by propietario order by litros desc
SQL;
    $propietarios = [];
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $propietarios[] = utf8_decode(utf8_encode($row["propietario"]));
        }
        $result->close();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $mysqli->close();
        return $respuesta;
    }

    $query = <<<SQL
    SELECT 
        DATE_FORMAT(e.fecha_carga, "%Y-%m") periodo,
        f.propietario,
        GROUP_CONCAT(
        DISTINCT (CONCAT(e.placas, ":", CAST(FORMAT(f.capacidad,2) AS CHAR) )) 
        ORDER BY f.capacidad
        ) Unidades,
        COUNT(DISTINCT e.id_entrega) numEntregas,
        SUM(e.litros) lts,
        SUM(e.litros) / ltsxmes.lts shareMes 
    FROM
        smartRoad_flota f 
    INNER JOIN
        smartRoad_entregas e 
        ON f.placas = e.placas 
    LEFT JOIN
        (
            SELECT 
                DATE_FORMAT(fecha_carga, "%Y-%m") periodo,
                SUM(litros) lts 
            FROM
                smartRoad_entregas 
            GROUP BY DATE_FORMAT(fecha_carga, "%Y-%m")
        ) ltsxmes 
        ON DATE_FORMAT(e.fecha_carga, "%Y-%m") = ltsxmes.periodo
    WHERE e.STATUS <> "carga cancelada"
    GROUP BY f.propietario,
    DATE_FORMAT(e.fecha_carga, "%Y-%m")
    ORDER BY DATE_FORMAT(e.fecha_carga, "%Y-%m"),lts desc
SQL;

    $arrValores = [];
    $tablaDatos = "<thead><tr><th>Mes</th><th>Línea de T.</th><th>Num. Viajes</th><th>Litros</th></tr></thead><tbody>";

    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $propietario = utf8_decode(utf8_encode($row["propietario"]));
            $periodo = $row["periodo"];
            $lts = $row["lts"];
            $arrValores[$propietario][$periodo] = $lts;
            $tablaDatos.="<tr><td>$periodo</td><td>$propietario</td><td>" . number_format($row["numEntregas"], 0) . "</td><td>" . number_format($lts) . "</td></tr>";
        }
        $result->close();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $mysqli->close();
        return $respuesta;
    }
    $mysqli->close();

    $alpha = 0.8;
    $colorRaloy = "rgba(0,124,182,$alpha)";
    $colors[] = "rgba(251,140,52,$alpha)";
    $colors[] = "rgba(253,192,77,$alpha)";
    $colors[] = "rgba(255,234,103,$alpha)";
    $colors[] = "rgba(242,255,120,$alpha)";
    $colors[] = "rgba(216,255,137,$alpha)";
    $colors[] = "rgba(1,31,75,$alpha)";
    $colors[] = "rgba(173,255,0,$alpha)";
    $colors[] = "rgba(0,210,127,$alpha)";
    $i = 0;
    foreach ($propietarios as $propietario) {
        $data = [];

        foreach ($labels as $month) {
            $data[] = ( array_key_exists($propietario, $arrValores) && array_key_exists($month, $arrValores[$propietario]) && $arrValores[$propietario][$month] > 0) ? $arrValores[$propietario][$month] : 0;
        }
        $color = ($propietario == "RALOY") ? $colorRaloy : $colors[$i];
        $datasets[] = array("label" => $propietario, "data" => $data, "backgroundColor" => $color);
        $i++;
    }

    $respuesta["labels"] = $labels;
    $respuesta["datasets"] = $datasets;
    $respuesta["tablaDatos"] = $tablaDatos;
    return json_encode($respuesta);
//    
//    $data = [];
//    $i = 0;
//    $data[] = 112000;
//    $data[] = 480909;
//    $respuesta["datasets"][] = array("label" => "Raloy", "data" => $data, "backgroundColor" => $colors[$i]);
//
//    $data = [];
//    $i = 1;
//    $data[] = 299000;
//    $data[] = 990000;
//    $respuesta["datasets"][] = array("label" => "Otros", "data" => $data, "backgroundColor" => $colors[$i]);
}

function cargas() {

    $query = <<<SQL
    SELECT 
  e.fecha_carga start,
  CONCAT(
    e.planta_carga, 
    " ",
    "F:",
    e.id_entrega,
    " ",
    CONVERT (FORMAT(e.litros,2) USING utf8)
  ) title,
  statusEnvio.colorInCalendar as color
FROM
  smartRoad_entregas e 
  INNER JOIN
  smartRoad_pre_ruteo pr 
  ON e.id_entrega = pr.id_entrega 
  INNER JOIN
  smartRoad_flota f 
  ON e.placas = f.placas 
  INNER JOIN
  smartRoad_stdEdosMpios em 
  ON pr.id_edoMpio = em.id
  LEFT JOIN statusEnvio ON statusEnvio=e.status 
GROUP BY e.fecha_carga,
            e.planta_carga,
  e.id_entrega,
  e.litros
            ORDER BY e.fecha_carga DESC,e.planta_carga DESC
            LIMIT 10000
SQL;
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

//    $ev=[];
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"]["events"][] = array("start" => $row["start"], "title" => utf8_encode($row["title"]), "color" => $row["color"]);
//            $ev[]= $row("fecha_carga");
//            array("start"=>  ,"title"=>utf8_encode($row["title"]));
        }
        $result->close();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $mysqli->close();
        return $respuesta;
    }
//    $respuesta["events"]["events"]=$ev;
    return json_encode($respuesta);
}

function entregas() {

    $query = <<<SQL
    SELECT 
  e.fecha_carga start,
  CONCAT(
    e.planta_carga, 
    " ",
    "F:",
    e.id_entrega,
    " ",
    CONVERT (FORMAT(SUM(pr.ltsSurtir),2) USING utf8)," \n",em.edoCor," \n",
 GROUP_CONCAT(pr.fechaPedido)
  ) title,
  IF(e.fecha_carga> DATE_ADD(pr.fechaPedido, INTERVAL (SELECT intervaloEntrega FROM intervalosEntrega WHERE diaSemanaNum=WEEKDAY(pr.fechaPedido)) DAY),"#d62d20","#008744") AS color
FROM
  smartRoad_entregas e 
  INNER JOIN
  smartRoad_pre_ruteo pr 
  ON e.id_entrega = pr.id_entrega 
  INNER JOIN
  smartRoad_flota f 
  ON e.placas = f.placas 
  INNER JOIN
  smartRoad_stdEdosMpios em 
  ON pr.id_edoMpio = em.id
  LEFT JOIN statusEnvio ON statusEnvio=e.STATUS 
 WHERE statusEnvio.id_statusEnvio<>4
GROUP BY e.fecha_carga,em.edoCor,
            e.planta_carga,
  e.id_entrega,pr.fechaPedido
            ORDER BY e.fecha_carga DESC,e.planta_carga DESC
            LIMIT 10000
SQL;
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

//    $ev=[];
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"]["events"][] = array("start" => $row["start"], "title" => utf8_encode($row["title"]), "color" => $row["color"]);
//            $ev[]= $row("fecha_carga");
//            array("start"=>  ,"title"=>utf8_encode($row["title"]));
        }
        $result->close();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $mysqli->close();
        return $respuesta;
    }
//    $respuesta["events"]["events"]=$ev;
    return json_encode($respuesta);
}

function costoxciudad() {
    $query = <<<SQL
  SELECT 
  f.propia_externa,
  em.edoCor,
  em.mpio,
  DATE_FORMAT(e.fecha_carga, "%Y-%m") mes,
  e.planta_carga,
  SUM(
    pr.ltsSurtir / e.litros * e.costoTot
  ) costo,
  SUM(pr.ltsSurtir) lts,
  SUM(
    pr.ltsSurtir / e.litros * e.costoTot
  ) / SUM(pr.ltsSurtir) costo_Lt,
  GROUP_CONCAT(CONCAT(e.placas, " ", f.capacidad) SEPARATOR ',<br/>') pipas,
  GROUP_CONCAT(e.id_entrega SEPARATOR ',<br/>') folios,
            GROUP_CONCAT(e.numEnvioRaloy SEPARATOR ',<br/>') envioRaloy,
  COUNT(e.id_entrega) numEntregas FROM smartRoad_entregas e 
  INNER JOIN
  smartRoad_pre_ruteo pr 
  ON e.id_entrega = pr.id_entrega 
  INNER JOIN
  smartRoad_flota f 
  ON e.placas = f.placas 
  INNER JOIN
  smartRoad_stdEdosMpios em 
  ON pr.id_edoMpio = em.id 
  LEFT JOIN
  statusEnvio 
  ON statusEnvio = e.STATUS WHERE statusEnvio.id_statusEnvio <> 4 
  AND e.costoTot > 0 GROUP BY f.propia_externa,
  em.edoCor,
  em.mpio,
  DATE_FORMAT(e.fecha_carga, "%Y-%m"),
  e.planta_carga 
SQL;
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

$respuesta["table"]="<table id='costosxcd' class='display compact' style='width:100%'><thead><tr><th>PROPIETARIO</th><th>ESTADO</th><th>CIUDAD</th><th>PERIODO</th><th>PLANTA</th><th>MXN</th><th>LTS</th><th>MXN X L</th><th>NÚM. ENVÍOS</th><th>ATQ's</th><th>FOLIOS</th><th>Núms. Embarque</th></tr></thead><tbody>";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $respuesta["table"].="<tr>";
            $respuesta["table"].="<td>".$row["propia_externa"]."</td>";
            $respuesta["table"].="<td>".utf8_encode($row["edoCor"])."</td>";
            $respuesta["table"].="<td>".utf8_encode($row["mpio"])."</td>";
            $respuesta["table"].="<td>".$row["mes"]."</td>";
            $respuesta["table"].="<td>".utf8_encode($row["planta_carga"])."</td>";
            $respuesta["table"].="<td>".number_format($row["costo"])."</td>";
            $respuesta["table"].="<td>".number_format($row["lts"])."</td>";
            $respuesta["table"].="<td>".number_format($row["costo_Lt"],2)."</td>";
            $respuesta["table"].="<td>".number_format($row["numEntregas"])."</td>";
            $respuesta["table"].="<td>".utf8_encode($row["pipas"])."</td>";
            $respuesta["table"].="<td>".utf8_encode($row["folios"])."</td>";
            $respuesta["table"].="<td>".utf8_encode($row["envioRaloy"])."</td>";
            $respuesta["table"].="</tr>";
            
        }
        $respuesta["table"].="</tbody></table>";
        $result->close();
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $mysqli->close();
        return $respuesta;
    }
    return json_encode($respuesta);
}
