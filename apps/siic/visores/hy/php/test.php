<?
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

$title = "SIIC";
$path = $title;
$modulo = 3;

require_once($_SERVER['DOCUMENT_ROOT']."/intranet/php/session_check.php");
$check=session_check($_GET["t"]);


$dataconn=dataconn("intranet");
$mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
if($mysqli->connect_errno){
    echo $mysqli->connect_error;
}else{
    echo $check;
}

?>