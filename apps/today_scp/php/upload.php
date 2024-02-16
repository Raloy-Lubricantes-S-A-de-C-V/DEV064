<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function upload() {
    $response = [];
    if (isset($_POST) == true) {

        //Crear la carpeta del folio de entrega
        $targetParent = "../../../uploads/dir" . $_POST["folio"];
        if (!is_dir($targetParent)) {
            mkdir($targetParent);
        }

        //Crear la carpeta correspondiente (remisiones, ocs, ticketbascula)
        $target = $_POST["target"];
        $targetDir = "../../../uploads/dir" . $_POST["folio"] . "/" . $target;
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
                //Mover el archivo al servidor
                if (move_uploaded_file($_FILES["file"]["tmp_name"][$i], $targetFilePath)) {
                    //insertar el archivo en la BD?
                    //... código insertar---
                    $response['filenames'][] = $fileName;
                    $response['status'] = 'ok';
                } else {
                    $response['status'] = 'err';
                }
            } else {
                $response['status'] = 'type_err';
            }
        }
        echo json_encode($response);
    }
}

function getFiles() {
    $dir = $_GET["dir"];
    $folio = $_GET["folio"];
    $directory = "../../../uploads/dir" . $folio . "/" . $dir;
    $directorytoshow = "../../uploads/dir" . $folio . "/" . $dir;
//    echo $directory;
    if (is_dir($directory)) {
        $arrFiles = array_diff(scandir($directory), array('..', '.'));
        $conteo = count($arrFiles);
        $strFiles = "";
        foreach ($arrFiles as $value) {
            $strFiles.="<div class='linkToFile'><span class='deleteFile' filename='$value'><i class='fa fa-minus-circle'></i></span> <a href='" . $directorytoshow . "/" . $value . "' target='_blank'><i class='fa fa-file-pdf pdfi'></i> " . $value . "</a></div>";
        }
        $respuesta["links"] = $strFiles;
        $respuesta["conteo"] = $conteo;
    } else {
        $respuesta["links"] = "";
        $respuesta["conteo"] = 0;
    }
    return json_encode($respuesta);
}

function removeFile() {
    $folio = $_GET["folio"];
    $file = $_GET["file"];
    $folder = $_GET["folder"];
    $myFile = "../../../uploads/dir" . $folio . "/" . $folder . "/" . $file;
    unlink($myFile) or die("Error");
}
