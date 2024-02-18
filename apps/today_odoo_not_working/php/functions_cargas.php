<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once($_SERVER['DOCUMENT_ROOT'] . "/intranet/php/session_check.php");
$check = session_check($_GET["t"]);
if ($check != 1) {
    echo json_encode(array("status" => "0", "error" => "Sesión expirada"));
    return;
}

$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function dump()
{
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    echo var_dump($mysqli);
}

function getData()
{
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $strPlantas = $_SESSION["sessionInfo"]["strPlantas"]["plants"];
    $query = <<<SQL
        SELECT 
            pk_entrega_odoo,
            folio,
            planta,
            fechaCarga,
            albaranRaloy,
            doctoSolCarZK,
            doctoRemisionZK,
            doctoTicketBascula
        FROM
            smartRoad_entregas_odooZK 
        WHERE albaranRaloy="" AND planta IN ($strPlantas)
        ORDER BY fechaCarga DESC
SQL;
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"][] = $row;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        exit(json_encode($respuesta));
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function createNewRecord()
{

    $values = [];
    $values[] = "'" . $_GET["folio"] . "'";
    $values[] = "'" . $_GET["planta"] . "'";
    $values[] = "'" . $_GET["fechaCarga"] . "'";
    $values[] = "'" . $_GET["doctosSolCargaZK"] . "'";

    $values = implode(",", $values);
    $query = <<<SQL
        INSERT INTO 
            smartRoad_entregas_odooZK (
                folio,
                planta,
                fechaCarga,
                doctoSolCarZK
            )
        VALUES(
            $values
        )
SQL;

    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    if($mysqli->connect_errno){
        return json_encode(array("status"=>0,"error"=>$mysqli->connect_error));
    }
    if ($mysqli->query($query)) {
        $respuesta["srtatus"] = 1;
        $respuesta["error"] = "";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function revisaPermisos($id_permiso)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (in_array($id_permiso, explode(",", $_SESSION["sessionInfo"]["strIdsPerms"]))) {
        return 1;
    } else {
        return 0;
    }
}

function datosRecepcionRaloy()
{
    $id_entrega = $_GET["folio"];
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    $sql = <<<SQL
    Select 
        numOCaZK,
        cliente,
        determinante,
        cveProducto,
        nombreProducto,
        ltsSurtir,
        concentracion
    FROM
        smartRoad_pre_ruteo 
    where id_entrega = $id_entrega 
        and fuente_pedido = "odooRaloy" 
SQL;
    $result = $mysqli->query($sql);
    $mysqli->close();
    $tbody = "";
    while ($row = $result->fetch_assoc()) {
        $tbody .= "<tr>";
        $rems = "";
        $ocs = "";
        $ocs_info = explode(",", $row["numOCaZK"]);
        foreach ($ocs_info as $oc_info) {
            $oc = explode("@", $oc_info);
            $rems .= $oc[0] . " ";
            $ocs .= $oc[0] . " ";
        }
        $tbody .= "<td>" . $rems . "</td>";
        $tbody .= "<td>" . $ocs . "</td>";
        $tbody .= "<td>" . $row["cliente"] . "</td>";
        $tbody .= "<td>" . $row["determinante"] . "</td>";
        $tbody .= "<td>" . $row["cveProducto"] . " " . $row["nombreProducto"] . "</td>";
        $tbody .= "<td>" . $row["ltsSurtir"] . "</td>";
        $tbody .= "<td>" . $row["concentracion"] . "%</td>";
        $tbody .= "</tr>";
    }
    return json_encode(array("status" => 1, "data" => $tbody));
}
function save_saveData($lept, $lpt, $sellosE, $sellosD, $rems, $iprs, $statusCert)
{
    $fechaHora = date("Y-m-d H:i:s");
    $id_usuario = $_SESSION["sessionInfo"]["userSession"];
    $ocs = dimeocyclienteremision($rems);
    if ($ocs["status"] == 1) {
        $octosave = $ocs["oc"];
        $ctestosave = $ocs["cvecliente"];
        $ltsrem = $ocs["lts"];
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "oc_s:" . $ocs["error"];
        return json_encode($respuesta);
    }
    if ($statusCert == 1) {
        $updateStatusCarga = 1;
        $updateLept = "loteEPT ='$lept',";
    } else {
        $updateStatusCarga = 0;
        $updateLept = "";
    }
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $sql = "select GROUP_CONCAT(CONCAT(NumRemi,'@',Pedido)) ocs FROM FRemision WHERE numRemi in ($rems) GROUP BY NumRemi,Pedido";
    $result = $mysqli->query($sql);
    $row = $result->fetch_assoc();
    $ocs = $row["ocs"];
    $mysqli->close();
    $query = <<<SQL
        UPDATE smartRoad_pre_ruteo SET $updateLept loteZK='$lpt', sellosEscotilla='$sellosE',sellosDescarga='$sellosD',remisionZK='$rems',numOCaZK='$ocs',statusCargaZK=$updateStatusCarga,id_usuariodatosZK='$id_usuario',occliente='$octosave',cveclientezk='$ctestosave' where id_pre_ruteo IN ($iprs);
SQL;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "saving error:" . $mysqli->error;
    }
    return $respuesta;
}

function save_saveDataResultsLab($iprs, $concentracion, $densidad, $indicer, $apariencia)
{
    $fechaHora = date("Y-m-d H:i:s");
    $id_usuario = $_SESSION["sessionInfo"]["userSession"];
    $query = <<<SQL
        UPDATE smartRoad_pre_ruteo SET concentracion=$concentracion,densidad=$densidad,indicer=$indicer,apariencia='$apariencia',fechaHoraCertificado='$fechaHora',statusCargaZK=1,id_usuariodatosZK='$id_usuario' where id_pre_ruteo IN ($iprs);
SQL;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "saving error:" . $mysqli->error;
    }
    return $respuesta;
}

function dimeocyclienteremision($remision)
{
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
GROUP BY numRemi,oc,cte        
SQL;
    $dataconn = dataconn("scpzar");
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

function dimeremsandpos($remsphp = "")
{
    $rems = $_GET["rems"];
    $query = <<<SQL
    SELECT 
        r.NumRemi remision,
        c.CveCliente,
        LEFT(c.NomCliente,7) cliente,
        GROUP_CONCAT(p.PedCli) oc,
        SUM(cantiDada * CDV) lts,
        SUM(p.restante) restanteoc
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
        GROUP BY Cliente,PedCli) p 
        ON r.Pedido = p.PedCli 
     INNER JOIN FClientes c ON p.Cliente=c.CveCliente
    WHERE r.numRemi IN (
            $rems
        ) 
    GROUP BY remision,c.CveCliente,cliente       
SQL;
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
        $remisiones = "";
        $ocs = [];
        $uniqueocs = [];
        while ($row = $result->fetch_assoc()) {
            $remisiones .= "<span class='remcomp' oc='" . $row["oc"] . "'>" . $row["remision"] . "</span><span class='clientecomp'>(" . utf8_encode($row["cliente"]) . ")</span>:<span class='ltsrem'>" . number_format($row["lts"], 2) . "</span><br/>";
            $ocs[$row["oc"]] = array("oc" => $row["oc"], "restante" => $row["restanteoc"]);
            $uniqueocs[] = $row["oc"];
        }
        $uniqueocs = array_unique($uniqueocs);

        $respuesta["ocsphp"] = implode(",", $uniqueocs);
        $strocs = "";
        foreach ($uniqueocs as $oc) {
            $strocs .= "<span class='occomp'>" . $ocs[$oc]["oc"] . "</span>"
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

function saveRespCarga()
{
    $responsableCarga = $_GET["responsableCarga"];
    $folio = $_GET["folio"];
    $query = <<<SQL
        UPDATE smartRoad_entregas SET responsableCarga ='$responsableCarga' where id_entrega=$folio;
SQL;
    $dataconn = dataconn("intranet");
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

function uploadFile()
{
    if (0 < $_FILES['file']['error']) {
        return 'Error: ' . $_FILES['file']['error'] . '<br>';
    } else {
        move_uploaded_file($_FILES['file']['tmp_name'], 'http:/www.skyblue.mx/management/uploads/remisiones/' . $_FILES['file']['name']);
        return "success";
    }
}

function dimeRemisiones($foliophp = "")
{
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
            $strFiles .= "<div class='linkToFile'><span class='deleteFile' filename='$value'><i class='fa fa-minus-circle'></i></span> <a href='" . $directorytoshow . "/" . $value . "'><i class='fa fa-file-pdf pdfi'></i> " . $value . "</a></div>";
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

function dimeocs($foliophp = "")
{
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

function dimepesajes($foliophp = "")
{
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

function voboAMP()
{
    $folio = $_GET["folio"];
    $numRec = $_GET["numRec"];
    $fechahora = date("Y-m-d H:i:s");
    $fhrsello = date("YmdHis");
    $id_usuario = $id_usuario = $_SESSION["sessionInfo"]["userSession"];
    $username = utf8_decode(strtolower(str_replace(" ", "", $_SESSION["sessionInfo"]["userName"])));
    $sessiondatesello = str_replace(array("/", "-", ":", " "), "", $_SESSION["sessionInfo"]["sessionDate"]);
    $random = generateRandomString(12);
    $sello = array("folio" => $folio, "fhr" => $fhrsello, "user_id" => $id_usuario, "user_name" => $username, "token" => $random, "sessiondate" => $sessiondatesello);
    $selloJson = json_encode($sello);
    // $sello = "zk_f" . $folio . "_" . $random . "_rec" . $numRec . "_fhr" . $fhrsello . "@" . $id_usuario . "_" . $username . "_" . $sessiondatesello;
    $query = "UPDATE smartRoad_entregas SET selloAlmacenCliente='$selloJson', validacionAMP=1,numReciboCliente='$numRec',fechahoraValAMP='$fechahora',usuarioValAMP='$id_usuario' WHERE id_entrega='$folio'";
    $dataconn = dataconn("intranet");
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

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function reciboAMP()
{
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $respuesta["tbody"] = "<tr><td>Sin Datos</td></tr>";
    return json_encode($respuesta);
}
