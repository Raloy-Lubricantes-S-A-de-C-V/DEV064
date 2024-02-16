<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
$fase = $_GET["fase"];

$response = call_user_func($fase);
echo $response;

function rootDirectory()
{
    $rootDirectory = $_SERVER['DOCUMENT_ROOT'] . "/intranet/uploads";
    $absolutePath= "https://".$_SERVER['SERVER_NAME']. "/intranet/uploads";
    return array($rootDirectory,$absolutePath);
}
function upload()
{
    $rootDirectory=rootDirectory()[0];
    $response = [];
    if (isset($_POST) == true) {
        $ruta = str_replace("/", "_", $_POST["ruta"]);
        $remision = str_replace("/", "_", $_POST["remision"]);
        $target = str_replace("/", "_", $_POST["target"]);
        //Crear la carpeta del folio de entrega
        $folderRuta = $rootDirectory."/ruta_" . $ruta;
        if (!is_dir($folderRuta)) {
            mkdir($folderRuta);
        }

        //Crear la carpeta de albarán correspondiente;
        if ($target != "tickets_bascula") {
            $folderRemision = $folderRuta . "/" . $remision;
            if (!is_dir($folderRemision)) {
                mkdir($folderRemision);
            }
        } else {
            $folderRemision = $folderRuta;
        }

        //Crear la carpeta para remisiones, báscula o aptin
        $targetDir = $folderRemision . "/" . $target;
        if (!is_dir($targetDir)) {
            mkdir($targetDir);
        }

        for ($i = 0; $i < count($_FILES["file"]["name"]); $i++) {

            //Generar el nombre de archivo
            $fileName = date("Ymd_His") . '_' . basename($_FILES["file"]["name"][$i]);
            $targetFilePath = $targetDir . "/" . $fileName;
            //Revisar nuevamente que el formato sea permitido
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            $allowTypes = array('pdf');

            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES["file"]["tmp_name"][$i], $targetFilePath)) {
                    $response['filenames'][] = $targetFilePath . "/" . $fileName;
                    $response['status'] = 'ok';
                } else {
                    $response['status'] = 'err ' . $targetFilePath;
                }
            } else {
                $response['status'] = 'type_err';
            }
        }
        echo json_encode($response);
    }
}

function getFilesRemision()
{
    $respuesta = [];
    $ruta = str_replace("/", "_", $_GET["ruta"]);
    $remision = str_replace("/", "_", $_GET["remision"]);
    $parentDir=rootDirectory();
    $subdir="ruta_" . $ruta . "/" . $remision;
    $directory = $parentDir[0] . "/".$subdir;
    $path=$parentDir[1] . "/".$subdir;
    $respuesta["dir"] = $directory;
    $remisiones = array_diff(scandir($directory . "/remisiones"), array('..', '.'));
    $aptIns = array_diff(scandir($directory . "/apt_ins"), array('..', '.'));
    $respuesta["status"] = 1;
    foreach ($remisiones as $filename) {
        $link = $path. "/remisiones"."/".$filename;
        $respuesta["remisiones"] .= "<div class='linkToFile'><span class='deleteFile' folder='$subdir/remisiones' filename='$filename'><i class='fa fa-minus-circle'></i></span> <a href='" . $link . "' target='_blank'><i class='fa fa-file-pdf pdfi'></i> " . $filename . "</a></div>";
    }
    foreach ($aptIns as $filename) {
        $link = $path. "/apt_ins"."/".$filename;
        $respuesta["apt_ins"] .= "<div class='linkToFile'><span class='deleteFile' folder='$subdir/apt_ins' filename='$filename'><i class='fa fa-minus-circle'></i></span> <a href='" . $link . "' target='_blank'><i class='fa fa-file-pdf pdfi'></i> " . $filename . "</a></div>";
    }
    return json_encode($respuesta);
}
function getTicketsBascula()
{
    $respuesta = [];
    $ruta = str_replace("/", "_", $_GET["ruta"]);
    $parentDir=rootDirectory();
    $subdir="ruta_" . $ruta."/tickets_bascula";
    $directory = $parentDir[0]."/".$subdir;
    $path=$parentDir[1] . "/".$subdir;
    $respuesta["dir"] = $directory;
    $tickets = array_diff(scandir($directory), array('..', '.'));
    $respuesta["status"] = 1;
    foreach ($tickets as $filename) {
        $respuesta["tickets"] .= "<div class='linkToFile'><span class='deleteFile' folder='$subdir' filename='$filename'><i class='fa fa-minus-circle'></i></span> <a href='" . $path . "/" . $filename . "' target='_blank'><i class='fa fa-file-pdf pdfi'></i> " . $filename . "</a></div>";
    }

    return json_encode($respuesta);
}

function removeFile()
{
    $parentDir=rootDirectory();
    $file = $_GET["file"];
    $subdir=$_GET["folder"];
    $directory = $parentDir[0]."/".$subdir;
    $myFile =  $directory . "/" . $file;
    unlink($myFile) or die(json_encode(array("status" => 1, "error" => "Error", "folder" => rootDirectory()[0])));
}
