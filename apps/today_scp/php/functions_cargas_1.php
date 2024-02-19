<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function dimeCargas() {
     $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $query = <<<SQL
        SELECT 
            c.id_entrega,
            c.placas,
            f.capacidad,
            c.fecha_carga,
            c.fecha_regreso,
            c.planta_carga,
            c.planta_regreso,
            c.STATUS,
            numrems.conteo conteoRemisiones,
            c.validacionAMP valAMP,
            (SELECT 
                usuario 
            FROM
                framework_usuarios 
            WHERE id_usuario = c.usuarioValAMP) usuarioValAMP,
            c.fechahoraValAMP 
        FROM
            smartRoad_entregas c 
            INNER JOIN
            smartRoad_flota f 
            ON f.placas = c.placas 
            INNER JOIN
            (SELECT 
                id_entrega,
                COUNT(DISTINCT remisionZK) conteo 
            FROM
                smartRoad_pre_ruteo 
            GROUP BY id_entrega) numrems 
            ON c.id_entrega = numrems.id_entrega
        WHERE c.STATUS IN ("camino") 
        GROUP BY c.id_entrega 
        ORDER BY c.STATUS,
            c.id_entrega DESC 
SQL;
    if ($result = $mysqli->query($query)) {
        $box = "";
        $fechaHoy = date("Y-m-d");
        while ($row = $result->fetch_assoc()) {
            //Status carga y certificados
            $dataDetalles = detallesBox($row["id_entrega"], $mysqli, "carga");
            $detalles = $dataDetalles["detalles"];
            $arrStatus = $dataDetalles["status"];
            $ltsTot = $dataDetalles["suma"];
            $utilizacion = $ltsTot / $row["capacidad"];
            
            if (in_array(0, $arrStatus)) {
                if (in_array(1, $arrStatus)) {
                    $color = "#ffc04c"; //incompletos - amarillo
                } else {
                    $color = "#DF5252"; //Sin datos - rojo
                }
            } else {
                $color = "#4ca64c"; //completos-green
            }
            //Status de remisiones
            $infoRemisiones = dimeRemisiones($row["id_entrega"]);
            $remisionesUploaded = ($infoRemisiones["conteo"]>0)?$infoRemisiones["conteo"]:0;
            $conteoRemisiones = $row["conteoRemisiones"];
            if ($remisionesUploaded > 0) {
                if ($conteoRemisiones == $remisionesUploaded) {
                    $colorRem = "#4ca64c"; //completos y concuerdan -green
                } else {
                    $colorRem = "#ffc04c"; //incompletos o incorrecto- amarillo
                }
            } else {
                $colorRem = "#DF5252"; //Sin datos - rojo
            }
            //Status de cuándo debió cargarse
            $colorFolio = ($fechaHoy <= $row["fecha_carga"]) ? "green" : "red";
            //Status de validación AMP
            if ($row["valAMP"] == 1) {
                $colorValAMP = "#4ca64c";
                $textValAMP=$row["usuarioValAMP"]. "<br/>". $row["fechahoraValAMP"];
            } else {
                $colorValAMP = "#DF5252";
                $textValAMP="";
            }

            //Botones dependiendo las funciones y los módulos autorizados para el usuario
            //Guardar datos de carga
            $classEdit = (revisaPermisos(10) === 1 && $textValAMP=="") ? "editallowed" : "";
            //Validación AMP
            $permisoValAMP = (revisaPermisos(11) === 1 && $textValAMP=="" && $color == "#4ca64c" && $colorRem == "#4ca64c") ? "editallowed" : "";
            //Validación Liberación por desviación
//            $permisoLibDesv = revisaPermisos(12);

            //Crear todos los objetos
            $box.="<tr folio='" . $row["id_entrega"] . "' class='trcarga $classEdit'>";
            $box.="<td>" . utf8_encode($row["planta_carga"]) . "</td>";
            $box.="<td>" . $row["fecha_carga"] . "</td>";
            $box.="<td style='color:$colorFolio;font-weight:bold;'>" . $row["id_entrega"] . "</td>";
            $box.="<td>" . number_format($ltsTot, 0) . " L</td>";
            $box.="<td> " . utf8_encode($row["placas"]) . "</td>";
            $box.="<td class='detallesEnvio'>" . $detalles . "</td>";
            $box.="<td class='getPapeleta' style='color:#4ca64c;font-size: calc(1em + 1vw);' folio='" . $row["id_entrega"] . "'><i class='fas fa-file-alt'></i></td>";
            $box.="<td class='folderCerts' style='color:#fff;text-align:center;font-size:calc(0.8em + 0.8vh)'><i style='color:$color;font-size: calc(1.5em + 1vh);' class='fa fa-folder'></i></td>";
            $box.="<td class='folderRems' numremsmustbe='$conteoRemisiones' numremsare='$remisionesUploaded' style='color:#fff;text-align:center;font-size:calc(0.8em + 0.8vh)'><span class='fa-stack fa-1.5x'><i style='color:$colorRem;font-size: calc(1.5em + 1vh);' class='fa fa-folder fa-stack-1x'></i><span style='font-size:calc(0.5em + 0.5vh);margin:0 auto;' class='fa fa-stack-1x conteoRems-text'>$remisionesUploaded / $conteoRemisiones</span></span></td>";
            $box.="<td class='valAMP $permisoValAMP'><i style='color:$colorValAMP;font-size: calc(0.8em + 1vw);' class='fa fa-circle'></i><br/>$textValAMP</td>";
            $box.="</tr>";
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        exit(json_encode($respuesta));
    }
    $respuesta["boxes"] = $box;
    return json_encode($respuesta);
}
function detallesBox($id_entrega, $mysqli, $stat) {
    $query = <<<SQL
            SELECT 
                r.eta,
                LEFT(em.mpio, 17) mpio,
                em.edoCor edo,
                r.cveProducto cve,
                r.pedido ped,
                LEFT(r.cliente,5) cliente,
                SUM(r.ltsSurtir) lts,
                GROUP_CONCAT(IFNULL(statusCargaZK,0)) statCarga
            FROM
                smartRoad_pre_ruteo r 
                INNER JOIN
                smartRoad_stdEdosMpios em 
                ON r.id_edoMpio = em.id 
            WHERE r.id_entrega = $id_entrega AND status='$stat'
            GROUP BY r.id_entrega,
                r.eta,
                r.cveCliente,
                r.determinante,
                r.cliente,
                r.pedido,
                em.edoCor,
                em.mpio,
                r.cveProducto,
                r.nombreProducto 
            ORDER BY r.eta,
                em.mpio,
                em.edoCor,
                r.cveProducto,
                r.pedido,
                lts 
SQL;
    $result = $mysqli->query($query);
    $spans = [];
    $stats = [];
    $suma = 0;
    while ($row = $result->fetch_assoc()) {
        $eta = $row["eta"];
        $lts = $row["cve"]." ".number_format($row["lts"], 2)." L";
        $mpio = utf8_encode($row["mpio"]);
        $edo = utf8_encode($row["edo"]);
        $cliente=utf8_encode($row["cliente"]);
        $spans[] = "<span style='display:block;width:100%;'>".$lts." (".$cliente." ".$mpio.",".$edo." ".$eta.")</span>";
        $stats = array_merge(explode(",", $row["statCarga"]), $stats);
        $suma+=$row["lts"];
    }
    $detalles = implode("", $spans);
    $respuesta["status"] = array_unique($stats);
    $respuesta["detalles"] = $detalles;
    $respuesta["suma"] = $suma;
    return $respuesta;
}
function revisaPermisos($id_permiso) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (in_array($id_permiso, explode(",", $_SESSION["sessionInfo"]["strIdsPerms"]))) {
        return 1;
    } else {
        return 0;
    }
}

function dimeCertificados() {
    //Puede ser que existan iprs agrupados en cada 'tiro' ?
    $folio = $_GET["folio"];
    $query = <<<SQL
     SELECT 
        GROUP_CONCAT(r.id_pre_ruteo) iprs,
        r.id_entrega,
        r.eta,
        SUM(r.ltsSurtir) lt,
        r.loteEPT loteept,
        r.loteZK lotept,
        r.sellosDescarga sellosD,
        r.sellosEscotilla sellosE,
        r.remisionZK rems,
        statusCargaZK,
        CONCAT(
            "<span iprs='",
            GROUP_CONCAT(r.id_pre_ruteo),
            "'>",
            cveProducto,
            ' ',
            FORMAT(SUM(r.ltsSurtir), 2),
            ' L ',
            '(',
            LEFT (cliente, 6),
            ' ',
            LEFT(em.mpio, 5),
            ', ',
            em.edoCor,
            ' ',
            r.eta,
            ')',
            '</span>'
        ) conc 
    FROM
        smartRoad_pre_ruteo r 
        INNER JOIN
        smartRoad_stdEdosMpios em 
        ON r.id_edoMpio = em.id 
    WHERE r.id_entrega = $folio 
    GROUP BY r.id_entrega,
        r.eta,
        r.cveCliente,
        r.determinante,
        r.cliente,
        r.pedido,
        em.edoCor,
        em.mpio,
        r.cveProducto,
        r.nombreProducto 
    ORDER BY 
        r.eta,
        em.mpio,
        em.edoCor,
        r.cveProducto,
        r.pedido,
        lt 
SQL;
     $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    //Datos del responsable de la carga
    $resResp = $mysqli->query("SELECT responsableCarga FROM smartRoad_entregas WHERE id_entrega='$folio'");
    $rowresponsable = $resResp->fetch_assoc();
    $respuesta["responsableCarga"] = $rowresponsable["responsableCarga"];

    //Todo lo demás
    $respuesta["status"] = 1;
    $trs = "";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {

            $iprs = $row["iprs"];
            $texttoshow = utf8_encode($row["conc"]);
            $inputlept = "<input type='text' class='lept' value='" . $row["loteept"] . "'/>";
            $inputlpt = "<input type='text' class='lpt' value='" . $row["lotept"] . "'/>";
            $inputsellosE = "<input type='text' class='sellosE' value='" . $row["sellosE"] . "'/>";
            $inputsellosD = "<input type='text' class='sellosD' value='" . $row["sellosD"] . "'/>";
            $inputremision = "<input type='text' class='rems' value='" . $row["rems"] . "'/>";
            $btnvalidar = "<button class='validarCert' iprs='" . $iprs . "'><i class='fa fa-save'></i></button>";
            if ($row["statusCargaZK"] == 1) {
                $getcert = "<i class='fa fa-file-alt cert' style='color:#4ca64c' folio='$folio' iprs='$iprs'></i>";
            } else {
                $getcert = "";
            }
            $trs.="<tr iprs='$iprs' folio='$folio'><td>" . $texttoshow . "</td><td>" . $inputlept . "</td><td>" . $inputlpt . "</td><td>" . $inputsellosE . "</td><td>" . $inputsellosD . "</td><td>" . $inputremision . "</td><td>" . $btnvalidar . "</td><td class='certCtr'>$getcert</td></tr>";
        }
        $respuesta["trs"] = $trs;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        exit(josn_encode($respuesta));
    }
    return json_encode($respuesta);
}

function save_all() {
    $lept = $_GET["lept"];
    $lpt = $_GET["lpt"];
    $sellosE = $_GET["sellosE"];
    $sellosD = $_GET["sellosD"];
    $rems = $_GET["rems"];
    $iprs = $_GET["iprs"];

    $checkLab = save_checkLab($lept);
    if ($checkLab["status"] == 1) {
        $results = $checkLab["resultados"];
        $checkSave = save_saveData($lept, $lpt, $sellosE, $sellosD,$rems, $iprs, $results["1093"], $results["1032"], $results["1080"], "Id&eacute;ntico a Referencia");
        $respuesta["status"] = $checkSave["status"];
        $respuesta["error"] = $checkSave["error"];
    } else {
        $respuesta["status"] = $checkLab["status"];
        $respuesta["error"] = $checkLab["error"];
    }
    return json_encode($respuesta);
}

function save_checkLab($loteEPT) {

    $qCheckLab = <<<SQL
    SELECT 
        lote,
        todos.fechaProd,
        todos.f1 fingreso,
        todos.f2 fresult,
        IF(
            FIND_IN_SET("err", GROUP_CONCAT(okErr)) > 0,
            "Err",
            IF(
                FIND_IN_SET("ND", GROUP_CONCAT(okErr)) > 0,
                "ND",
                "OK"
            )
        ) errok,
	GROUP_CONCAT(CONCAT(Pid,'@',valor) ORDER BY Pid) results 
    FROM
        (SELECT 
            Referencia lote,
            mues.fechaProd,
            mues.planta,
            mues.tanque,
            mues.f1,
            mues.f2,
	    mues.Pid,
            mues.param,
            valMin,
            valMax,
            mues.Valor,
            IF(
                mues.Valor = "" 
                OR ISNULL(mues.Valor),
                "ND",
                IF(
                    ISNULL(valMax) 
                    AND mues.Valor = "si",
                    "OK",
                    IF(
                        ISNULL(valMin),
                        IF(valMax >= mues.valor, "OK", "ERR"),
                        IF(
                            valMax >= mues.valor 
                            AND valMin <= mues.Valor,
                            "OK",
                            "ERR"
                        )
                    )
                )
            ) okErr 
        FROM
            Skyblue_referenciaISO iso 
            INNER JOIN
            (SELECT 
                a.Pid,
                CONCAT(
                    "20",
                    SUBSTRING(m.Referencia, 5, 2),
                    "-",
                    SUBSTRING(m.Referencia, 3, 2),
                    "-",
                    LEFT(m.Referencia, 2)
                ) fechaProd,
                SUBSTRING(m.Referencia, 7, 1) planta,
                SUBSTRING(m.Referencia, 8, 1) Tanque,
                m.Referencia,
                m.idMuestra,
                a.PNombre param,
                IF(
                    a.PValor REGEXP ('^[0-9]'),
                    ROUND(a.PValor, 4),
                    a.PValor
                ) valor,
                m.Fecha f1,
                m.respuesta_fecha f2,
                a.Analista an,
                m.Descripcion 
            FROM
                muestras m 
                LEFT JOIN
                analisisMuestras a 
                ON m.folio = a.idMuestra 
                AND m.YearFolio = a.YearMuestra 
            WHERE Pid IN (
                    1032,
                    1078,
                    1079,
                    1080,
                    1081,
                    1082,
                    1083,
                    1084,
                    1085,
                    1086,
                    1087,
                    1088,
                    1089,
                    1090,
                    1091,
                    1092,
                    1093,
                    1094,
                    322
                ) 
                AND m.Tipo = "ZK" 
                AND m.Fecha >= "2017-05-01" 
                AND m.Referencia = "$loteEPT" 
            ORDER BY a.Pid) mues 
            ON mues.Pid = iso.Pid 
        ORDER BY mues.fechaProd DESC,
            lote,
            iso.ordenEnNorma) todos 
    GROUP BY todos.lote 
    ORDER BY DATE(todos.fechaProd) DESC
SQL;
    $dataconn=dataconn("laboratorio");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($qCheckLab)) {
        $row = $result->fetch_assoc();
        if ($row["errok"] == "OK") {
            $respuesta["status"] = 1;
            $arrRes = explode(",", $row["results"]);
            foreach ($arrRes as $resultado) {
                $vx = explode("@", $resultado);
                $results[$vx[0]] = $vx[1];
            }
//            echo json_encode($results);
            $respuesta["resultados"] = $results;
        } else {
//            checkLibDesv($mysqli, $loteEPT);
            $respuesta["status"] = 2;
            $respuesta["error"] = "NO ES POSIBLE EMITIR EL CERTIFICADO\nEl lote especificado no ha sido liberado o está fuera de conformidad.\nLos datos no han sido guardados.";
        }
    } else {
        $respuesta["error"] = $mysqli->error;
        $respuesta["status"] = 0;
    }
    $mysqli->close();
    return $respuesta;
}

//function checkLibDesv($mysqli,$loteEPT){
//    $query="SELECT * FROM sollibdesv WHERE lote_EPT='$loteEPT' AND status_lib=1";
//    if ($result = $mysqli->query($qCheckLab)) {
//        
//    }else{
//        return "error";
//    }
//}
//function solicitarLiberacionDesviacion(){
//    $lept = $_GET["lept"];
//    $lpt = $_GET["lpt"];
//    $sellos = $_GET["sellos"];
//    $rems = $_GET["rems"];
//    $iprs = $_GET["iprs"];
//
//    if ($checkLab["status"] == 1) {
//        $results = $checkLab["resultados"];
//        $checkSave = save_saveData($lept, $lpt, $sellos, $rems, $iprs, $results["1093"], $results["1032"], $results["1080"], "Id&eacute;ntico a Referencia");
//        $respuesta["status"] = $checkSave["status"];
//        $respuesta["error"] = $checkSave["error"];
//    } else {
//        $respuesta["status"] = $checkLab["status"];
//        $respuesta["error"] = $checkLab["error"];
//    }
//    return json_encode($respuesta);
//    global $host, $user, $pass, $db, $port;
//    $mysqli = new mysqli($host, $user, $pass, $db, $port);
//    $querySave=
//}
function save_saveData($lept, $lpt, $sellosE,$sellosD,$rems, $iprs, $concentracion, $densidad, $indicer, $apariencia) {
    $fechaHora = date("Y-m-d H:i:s");
    $id_usuario = $_SESSION["sessionInfo"]["userSession"];
    $query = <<<SQL
        UPDATE smartRoad_pre_ruteo SET loteEPT ='$lept', loteZK='$lpt', sellosEscotilla='$sellosE',sellosDescarga='$sellosD',remisionZK='$rems',concentracion=$concentracion,densidad=$densidad,indicer=$indicer,apariencia='$apariencia',fechaHoraCertificado='$fechaHora',statusCargaZK=1,id_usuariodatosZK='$id_usuario' where id_pre_ruteo IN ($iprs);
SQL;
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return $respuesta;
}

function saveRespCarga() {
    $responsableCarga = $_GET["responsableCarga"];
    $folio = $_GET["folio"];
    $query = <<<SQL
        UPDATE smartRoad_entregas SET responsableCarga ='$responsableCarga' where id_entrega=$folio;
SQL;
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return json_encode($respuesta);
}

function uploadFile() {
    if (0 < $_FILES['file']['error']) {
        return 'Error: ' . $_FILES['file']['error'] . '<br>';
    } else {
        move_uploaded_file($_FILES['file']['tmp_name'], 'http:/www.skyblue.mx/management/uploads/remisiones/' . $_FILES['file']['name']);
        return "success";
    }
}

function dimeRemisiones($foliophp = "") {
    if ($foliophp == "") {
        $folio = $_GET["folio"];
    } else {
        $folio = $foliophp;
    }
    $directory = "../../../uploads/dir" . $folio."/remisiones";
    $directorytoshow = "../../uploads/dir" . $folio."/remisiones";
//    echo $directory;
    if (is_dir($directory)) {
        $arrFiles = array_diff(scandir($directory), array('..', '.'));
        $conteo = count($arrFiles);
        $strFiles = "";
        foreach ($arrFiles as $value) {
            $strFiles.="<div class='linkToFile'><span class='deleteFile' filename='$value'><i class='fa fa-minus-circle'></i></span> <a href='" . $directorytoshow . "/" . $value . "'><i class='fa fa-file-pdf pdfi'></i> " . $value . "</a></div>";
        }
        $respuesta["links"] = $strFiles;
        $respuesta["conteo"] = $conteo;
    } else {
        $respuesta["links"] = "";
        $respuesta["conteo"] = 0;
    }
    if ($foliophp == "") {
        return json_encode($respuesta);
    } else {
        return $respuesta;
    }
}

function voboAMP() {
    $folio = $_GET["folio"];
    $fechahora = date("Y-m-d H:i:s");
    $id_usuario =$id_usuario = $_SESSION["sessionInfo"]["userSession"];
    $query = "UPDATE smartRoad_entregas SET validacionAMP=1,fechahoraValAMP='$fechahora',usuarioValAMP='$id_usuario' WHERE id_entrega='$folio'";
    $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    return json_encode($respuesta);
}

function reciboAMP() {
    $respuesta["status"]=1;
    $respuesta["error"]="";
    $respuesta["tbody"]="<tr><td>Sin Datos</td></tr>";
    return json_encode($respuesta);
}
