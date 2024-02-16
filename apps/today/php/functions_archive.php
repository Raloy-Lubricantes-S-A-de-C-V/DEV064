<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
$ruteados = [];

require_once("../../../php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    echo array("status"=>0,"error"=>"Sesión Expirada");
    return;
}

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function dimeCargas() {
     $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $f1=$_GET["f1"];
    $f2=$_GET["f2"];
//    $numenvio=explode(",",$_GET["albaran"]);
//    if(count($numenvio)>0){
//        $strenvio="OR numEnvioRaloy IN(".$_GET["albaran"].")";
//    }else{
//        $strenvio="";
//    }
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $query = <<<SQL
        SELECT 
            c.id_entrega,
            c.numEnvioRaloy cartaPorte,
            c.placas,
            f.capacidad,
            c.fecha_carga,
            c.fecha_regreso,
            c.planta_carga,
            c.planta_regreso,
            c.status,
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
        WHERE 
            DATE(fecha_carga) >= "$f1" 
            AND DATE(fecha_carga) <= "$f2"
        GROUP BY c.id_entrega 
        ORDER BY c.STATUS,
            c.id_entrega DESC  
SQL;
    if ($result = $mysqli->query($query)) {
        $box = "";
        $fechaHoy = date("Y-m-d");
        while ($row = $result->fetch_assoc()) {
            //Status carga y certificados
            $dataDetalles = detallesBox($row["id_entrega"], $mysqli, "terminado");
            $detalles = $dataDetalles["detalles"];
            $arrStatus = $dataDetalles["status"];
            $ltsTot = $dataDetalles["suma"];
            $utilizacion = $ltsTot / $row["capacidad"];

            if (in_array(0, $arrStatus)) {
                if (in_array(1, $arrStatus)) {
                    $classCerts = "waitaminute"; //incompletos - amarillo
                } else {
                    $classCerts = "notyet"; //Sin datos - rojo
                }
            } else {
                $classCerts = "ok"; //completos-green
            }
            //Status de remisiones
            $infoRemisiones = dimeRemisiones($row["id_entrega"]);
            $infoocs = dimeocs($row["id_entrega"]);
            $infopesajes = dimepesajes($row["id_entrega"]);
            $remisionesUploaded = ($infoRemisiones["conteo"] > 0) ? $infoRemisiones["conteo"] : 0;
            $conteoRemisiones = $row["conteoRemisiones"];
            if ($remisionesUploaded > 0) {
                if ($conteoRemisiones == $remisionesUploaded) {
                    $classRem = "ok"; //completos y concuerdan -green
                } else {
                    $classRem = "waitaminute"; //incompletos o incorrecto- amarillo
                }
            } else {
                $classRem = "notyet"; //Sin datos - rojo
            }
            //Status de cuándo debió cargarse
            $colorFolio = ($fechaHoy >= $row["fecha_carga"] && $row["status"]!="terminado") ? "red" : "green";
            //Status de validación AMP
            if ($row["valAMP"] == 1) {
                $colorValAMP = "#4ca64c";
                $textValAMP = $row["usuarioValAMP"] . "<br/>" . $row["fechahoraValAMP"];
            } else {
                $colorValAMP = "#DF5252";
                $textValAMP = "";
            }
            $classPesaje = ($infopesajes["conteo"]>0)?"ok":"notyet";
            $classOcs = ($infoocs["conteo"]>0)?"ok":"notyet";
            $classDocs = ($classCerts == "ok" && $classRem == "ok") ? "okbig" : "notyetbig";
            //Botones dependiendo las funciones y los módulos autorizados para el usuario
            //Guardar datos de carga
            $classEdit = (revisaPermisos(10) === 1 && $textValAMP == "") ? "editallowed" : "";
            //Validación AMP
            $permisoValAMP = (revisaPermisos(11) === 1 && $textValAMP == "" && $classCerts == "ok" && $classRem == "ok") ? "editallowed" : "";
            //Validación Liberación por desviación
//            $permisoLibDesv = revisaPermisos(12);
            //Crear todos los objetos
            $box.="<tr folio='" . $row["id_entrega"] . "' class='trcarga $classEdit'>";
            $box.="<td>" . utf8_encode($row["planta_carga"]) . "</td>";
            $box.="<td>" . $row["fecha_carga"] . "</td>";
            $cartaPorte=($row["cartaPorte"]<=0)?"<input type='text' class='inputCartaPorte' placeholder='Carta Porte'/>":"C. Porte:".$row["cartaPorte"];
            $status=($row["status"]=="terminado")?$row["status"]:$row["status"]." <button class='finishOrder' style='cursor:pointer;border:none;background:none;font-size:8pt;color:blue !important;'>Terminar</button>";
            $box.="<td style='color:$colorFolio;font-weight:bold;'>Folio:" . $row["id_entrega"] . "<br/>".$cartaPorte."<br/>".$status."</td>";
            $box.="<td>" . number_format($ltsTot, 0) . " L</td>";
            $box.="<td> " . utf8_encode($row["placas"]) . "</td>";
            $box.="<td class='detallesEnvio'>" . $detalles . "</td>";
            $box.="<td class='getPapeleta' style='color:#4ca64c;font-size: calc(1em + 1vw);' folio='" . $row["id_entrega"] . "'><i class='fas fa-file-alt'></i></td>";
            $box.="<td class='folderDoctos' style='color:#fff;text-align:center;font-size:calc(0.8em + 0.8vh)'><i class='fa fa-folder $classDocs'></i><br/><span class='onmeter'><i class='fas fa-square $classCerts'></i><i class='fas fa-square $classOcs'></i><i class='fas fa-square $classRem'></i><i class='fas fa-square $classPesaje'></i></span></td>";
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
            WHERE r.id_entrega = $id_entrega
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
        $lts = $row["cve"] . " " . number_format($row["lts"], 2) . " L";
        $mpio = utf8_encode($row["mpio"]);
        $edo = utf8_encode($row["edo"]);
        $cliente = utf8_encode($row["cliente"]);
        $spans[] = "<span style='display:block;width:100%;'>" . $lts . " (" . $cliente . " " . $mpio . "," . $edo . " " . $eta . ")</span>";
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
    $resResp = $mysqli->query("SELECT responsableCarga,placas,fecha_carga feccar,planta_carga planta FROM smartRoad_entregas WHERE id_entrega='$folio'");
    $rowgraldata = $resResp->fetch_assoc();
    $respuesta["planta"] = $rowgraldata["planta"];
    $respuesta["feccar"] = $rowgraldata["feccar"];
    $respuesta["placas"] = $rowgraldata["placas"];
    $respuesta["responsableCarga"] = $rowgraldata["responsableCarga"];

    //Todo lo demás
    $respuesta["status"] = 1;
    $trs = "";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {

            $iprs = $row["iprs"];
            $texttoshow = utf8_encode($row["conc"]);
            $inputlept = "<input type='text' class='lept' value='" . $row["loteept"] . "'/>";
            $inputlpt = "<input  type='text' class='lpt' value='" . $row["lotept"] . "'/>";
            $inputsellosE = "<input  type='text' class='sellosE' value='" . $row["sellosE"] . "'/>";
            $inputsellosD = "<input  type='text' class='sellosD' value='" . $row["sellosD"] . "'/>";
            $inputremision = "<input  type='text' class='rems' value='" . $row["rems"] . "'/>";
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
        exit(json_encode($respuesta));
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
//    $respuesta["checklab"]=$checkLab;
//    return json_encode($respuesta);
    if ($checkLab["status"] == 1) {
        $results = $checkLab["resultados"];
        $checkSave = save_saveData($lept, $lpt, $sellosE, $sellosD, $rems, $iprs, $results["1093"], $results["1032"], $results["1080"], "Id&eacute;ntico a Referencia");
        $respuesta["status"] = $checkSave["status"];
        $respuesta["error"] = $checkSave["error"];
    } else {
        $respuesta["status"] = $checkLab["status"];
        $respuesta["error"] = $checkLab["error"];
    }
    return json_encode($respuesta);
}

function save_checkLab($loteEPT) {
    if (!isset($_SESSION["parametros"]["liberacionlotes"]) || $_SESSION["parametros"]["liberacionlotes"] <= 0) {
        $respuesta["error"] = "Error al validar sus permisos. Por favor cierre sesión y vuelva a ingresar";
        $respuesta["status"] = 2;
        return $respuesta;
    }
    $querypruebasachecar = "SELECT p.Pid FROM merit_formatosCert f INNER JOIN merit_referenciaISO p ON f.id_prueba=p.Pid WHERE f.id_formato_cert=" . $_SESSION["parametros"]["liberacionlotes"];
     $dataconn=dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $pruebas = [];
    if ($resultx = $mysqli->query($querypruebasachecar)) {
        while ($rowx = $resultx->fetch_assoc()) {
            $pruebas[] = $rowx["Pid"];
        }
    } else {
        $respuesta["error"] = $mysqli->error . $mysqli->connect_error;
        $respuesta["status"] = 0;
        $mysqli->close();
        return $respuesta;
    }
    $strpruebas = implode(",", $pruebas);
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
                    $strpruebas
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
function save_saveData($lept, $lpt, $sellosE, $sellosD, $rems, $iprs, $concentracion, $densidad, $indicer, $apariencia) {
    $fechaHora = date("Y-m-d H:i:s");
    $id_usuario = $_SESSION["sessionInfo"]["userSession"];
    $ocs = dimeocyclienteremision($rems);
    if ($ocs["status"] == 1) {
        $octosave = $ocs["oc"];
        $ctestosave = $ocs["cvecliente"];
        $ltsrem = $ocs["lts"];
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $ocs["error"];
        return json_encode($respuesta);
    }
    $query = <<<SQL
        UPDATE smartRoad_pre_ruteo SET loteEPT ='$lept', loteZK='$lpt', sellosEscotilla='$sellosE',sellosDescarga='$sellosD',remisionZK='$rems',concentracion=$concentracion,densidad=$densidad,indicer=$indicer,apariencia='$apariencia',fechaHoraCertificado='$fechaHora',statusCargaZK=1,id_usuariodatosZK='$id_usuario',occliente='$octosave',cveclientezk='$ctestosave' where id_pre_ruteo IN ($iprs);
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

function dimeocyclienteremision($remision) {
    $query = <<<SQL
    SELECT 
    r.Pedido oc,
    SUM(cantiDada * CDV) lts,
    r.Cliente cte
FROM
    FRemision r 
    INNER JOIN
    InvProdTerm pt 
    ON r.Producto = pt.PTNumArticulo 
WHERE r.numRemi = $remision 
GROUP BY numRemi         
SQL;
     $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
        $row = $result->fetch_assoc();
        $respuesta["oc"] = $row["oc"];
        $respuesta["cvecliente"] = $row["cte"];
        $respuesta["lts"] = $row["lts"];
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return $respuesta;
}

function dimeremsandpos($remsphp = "") {
    $rems = $_GET["rems"];
    $query = <<<SQL
    SELECT 
        GROUP_CONCAT(p.PedCli) oc,
        r.NumRemi remision,
        SUM(cantiDada * CDV) lts,
        SUM(p.restante) restanteoc,
        c.CveCliente,
        LEFT(c.NomCliente,7) cliente 
    FROM
        FRemision r 
        INNER JOIN
        InvProdTerm pt 
        ON r.Producto = pt.PTNumArticulo 
        AND r.Acabado = pt.PTTipo 
        INNER JOIN
        (SELECT 
            Cliente,
            PedCli,
            SUM(CantiOrden) - SUM(cantiDada) restante 
        FROM
            FPedidos p 
        GROUP BY PedCli) p 
        ON r.Pedido = p.PedCli 
     INNER JOIN FClientes c ON p.Cliente=c.CveCliente
    WHERE r.numRemi IN (
            $rems
        ) 
    GROUP BY numRemi         
SQL;
     $dataconn=dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
        $remisiones = "";
        $ocs = [];
        $uniqueocs = [];
        while ($row = $result->fetch_assoc()) {
            $remisiones.="<span class='remcomp' oc='" . $row["oc"] . "'>" . $row["remision"] . "</span><span class='clientecomp'>(" . utf8_encode($row["cliente"]) . ")</span>:<span class='ltsrem'>" . number_format($row["lts"], 2) . "</span><br/>";
            $ocs[$row["oc"]] = array("oc" => $row["oc"], "restante" => $row["restanteoc"]);
            $uniqueocs[] = $row["oc"];
        }
        $uniqueocs = array_unique($uniqueocs);

        $respuesta["ocsphp"] = implode(",", $uniqueocs);
        $strocs = "";
        foreach ($uniqueocs as $oc) {
            $strocs.="<span class='occomp'>" . $ocs[$oc]["oc"] . "</span>"
//                    . ":<span class='restanteoc'>".$ocs[$oc]["restante"]."</span>"
                    . "<br/>";
        }
        $respuesta["compremisiones"] = $remisiones;
        $respuesta["ocs"] = $strocs;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
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
    $directory = "../../../uploads/dir" . $folio . "/remisiones";
    $directorytoshow = "../../uploads/dir" . $folio . "/remisiones";
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

function dimeocs($foliophp = "") {
    $directory = "../../../uploads/dir" . $foliophp . "/ocs";
    if (is_dir($directory)) {
        $arrFiles = array_diff(scandir($directory), array('..', '.'));
        $conteo = count($arrFiles);
        $respuesta["conteo"] = $conteo;
    } else {
        $respuesta["conteo"] = 0;
    }
    return $respuesta;
}

function dimepesajes($foliophp = "") {
    $directory = "../../../uploads/dir" . $foliophp . "/pesaje";
    if (is_dir($directory)) {
        $arrFiles = array_diff(scandir($directory), array('..', '.'));
        $conteo = count($arrFiles);
        $respuesta["conteo"] = $conteo;
    } else {
        $respuesta["conteo"] = 0;
    }
    return $respuesta;
}

function voboAMP() {
    $folio = $_GET["folio"];
    $fechahora = date("Y-m-d H:i:s");
    $fhrsello = date("YmdHis");
    $id_usuario = $id_usuario = $_SESSION["sessionInfo"]["userSession"];
    $username = utf8_decode(strtolower(str_replace(" ", "", $_SESSION["sessionInfo"]["userName"])));
    $sessiondatesello = str_replace(array("/", "-", ":", " "), "", $_SESSION["sessionInfo"]["sessionDate"]);
    $random = generateRandomString(12);
    $sello = "zk_f" . $folio . "_" . $random . "_fhr" . $fhrsello . "@" . $id_usuario . "_" . $username . "_" . $sessiondatesello;
    $query = "UPDATE smartRoad_entregas SET selloAlmacenCliente='$sello', validacionAMP=1,fechahoraValAMP='$fechahora',usuarioValAMP='$id_usuario' WHERE id_entrega='$folio'";
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

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function reciboAMP() {
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $respuesta["tbody"] = "<tr><td>Sin Datos</td></tr>";
    return json_encode($respuesta);
}
