<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT']."/intranet/php/session_check.php");
$check=session_check($_GET["t"]);
if ($check != 1) {
    echo json_encode(array("status"=>"0","error"=>"Sesión expirada"));
    return;
}

$f = $_GET["f"];
$response = call_user_func($f);
echo $response;

function reportesUsuario() {
    $usuario = $_SESSION["sessionInfo"]["userSession"];
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $query = <<<SQL
			SELECT
				r.IDReporte AS IDReporte,
				NomReporte,
				if(Version >= 4,CONCAT("visores/", r.IDReporte, ".php"),IF(Version=2,CONCAT("visores/",ArchReporte),CONCAT("http://skyblue.mx/siic/visores/",ArchReporte))) AS url,
				Descrip,
				Titulo
			FROM
				siic_reportes r
				INNER JOIN siic_perfiles p ON r.IDReporte = p.IDReporte
			WHERE
				p.id_usuario = '$usuario'
				AND r.VigReporte = 'T'
			ORDER BY
				Categoria,NomReporte
SQL;

    $result = $mysqli->query($query) or die($mysqli->error);

    while ($rs = $result->fetch_assoc()) {
        $reportes[] = array(
            'IDReporte' => $rs["IDReporte"],
            'NomReporte' => utf8_encode($rs["NomReporte"]),
            'url' => $rs["url"],
            'Descrip' => utf8_encode($rs["Descrip"]),
            'Titulo' => utf8_encode($rs["Titulo"])
        );
    }
    $mysqli->close();
    return json_encode($reportes);
}
