<?php

$fase = filter_input(INPUT_GET, "fx");
$response = call_user_func($fase);
//header('Content-Type: text/html; charset=utf-8');
echo $response;

function createChart() {
    $mysqli = new mysqli("localhost", "zarkruse_intrane", "Totich182308", "zarkruse_webcollab", "3306");
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }
    $projectid = filter_input(INPUT_GET, "projectid", FILTER_SANITIZE_NUMBER_INT);

    $q = "SELECT t.id,t.task_name label,t.parent,t.precedents,UNIX_TIMESTAMP(CURDATE()) curdateunix,mindate,maxdate,UNIX_TIMESTAMP(deadline) deadlineunix,sequence FROM tasks t LEFT JOIN (SELECT projectid, UNIX_TIMESTAMP(min(deadline)) mindate,UNIX_TIMESTAMP(max(deadline)) maxdate FROM tasks GROUP BY projectid) minmaxdates  ON t.projectid=minmaxdates.projectid WHERE t.projectid=$projectid ORDER BY parent, deadline DESC, id";

    $result = $mysqli->query($q);
    if ($mysqli->errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    if ($result->num_rows > 0) {
        $respuesta["status"] = 1;
        $nodes = [];
        $edges = [];
        $yaxis = 0;
        $scalesize = 300;
        while ($row = $result->fetch_assoc()) {
            $xaxis = round(($row["deadlineunix"] - $row["mindate"]) / ($row["maxdate"] - $row["mindate"]) * $scalesize, 0);
            if ($row["curdateunix"] > $row["deadlineunix"]) {
                $nodecolor = "#993232";
            } else {
                $nodecolor = "#96ceb4";
            }

            $yaxis = ($yaxis + $row["sequence"]) / 2;
            if ($row["parent"] > 0) {
                $edges[] = array("id" => $row["parent"] . "-" . $row["id"], "source" => $row["parent"], "target" => $row["id"], "type" => "arrow", "size" => $scalesize / 15, "color" => "#a7a7a7");
            }

            $nodes[] = array("id" => $row["id"], "label" => $row["label"], "x" => $xaxis, "y" => $yaxis * 20, "size" => $scalesize / 15, "color" => $nodecolor);
            if ($row["precedents"] != "" && $row["precedents"] != null) {
                $precedents = explode(",", $row["precedents"]);
                foreach ($precedents as $id_precedent) {
                    $edges[] = array("id" => $id_precedent . "-" . $row["id"], "source" => $id_precedent, "target" => $row["id"], "type" => "dotted", "size" => $scalesize / 15, "color" => "#a7a7a7");
                }
            }
        }
        $respuesta["status"] = 1;
        $respuesta["graph"] = array("nodes" => $nodes, "edges" => $edges);
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "No data available";
    }

    return json_encode($respuesta);
}
