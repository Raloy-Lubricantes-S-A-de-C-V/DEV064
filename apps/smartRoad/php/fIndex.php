<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
$ruteados = [];

require_once("../../../php/session_check.php");
if (session_check($_GET["t"]) != 1) {
    echo array("status"=>0,"error"=>"Sesión Expirada");
    return;
}

require_once("../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function dimeEdosMpiosStd()
{
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";

    $query = <<<SQL
       SELECT 
      CONCAT(TRIM(d.cliente),'@@',TRIM(d.destino)) cve,
      d.id_det_origen id_det,
      m.id,
      m.edoCor,
      m.mpio 
    FROM
      smartRoad_stdDet d LEFT JOIN 
      smartRoad_stdEdosMpios m ON d.id_relEdoMpio=m.id        
SQL;
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $respuesta["datos"][$row["id_det"]] = array("idDet" => $row["id_det"], "idEM" => $row["id"], "edoCor" => utf8_encode($row["edoCor"]), "mpio" => utf8_encode($row["mpio"]));
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return $respuesta;
}

function dimeRuteados()
{
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";

    $query = <<<SQL
        SELECT 
            CONCAT(
                TRIM(IFNULL(pedido, '')),
                '@@',
                TRIM(IFNULL(id_det_origen, '')),
                '@@',
                TRIM(IFNULL(cveProducto, '')),
                '@@',
                TRIM(IFNULL(soid, ''))
            ) cveRuteado,
            SUM(ltsSurtir) lts 
        FROM
            smartRoad_pre_ruteo 
        WHERE NOT ISNULL(id_det_origen)  
        GROUP BY cveRuteado
SQL;
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            if ($row["cveRuteado"] != null) {
                $respuesta["datos"][utf8_encode($row["cveRuteado"])] = $row["lts"];
            }
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    $ruteados = $respuesta;
    return;
}

function dimePedidosPendientesOdoo()
{
    global $ruteados;
    dimeRuteados();
    $arrRuteados = $ruteados;
    //CON DATOS
    $array = file("http://siic.raloy.com.mx/odoo-bi/services/pedidos_adblue.php");
    $data = json_decode($array[0], True);
    $arrEdoMpioStd = dimeEdosMpiosStd(); //Genera un array con los ids de odoo o SCP del determinante
    $table = "";
    if (count($data) > 0) {
        foreach ($data as $row) {
            $cveRuteado = $row["pedido"] . "@@" . $row["id_destino"] . "@@" . $row["clave"] . "@@" . $row["soid"];
            $ltsRuteados = (array_key_exists("datos", $arrRuteados) && count($arrRuteados["datos"]) > 0 && array_key_exists($cveRuteado, $arrRuteados["datos"])) ? $arrRuteados["datos"][$cveRuteado] : 0;
            $ltsPedido = str_replace(",", "", $row["litros"]);
            $ltsxEnviar = $ltsPedido - $ltsRuteados;
            $cveDet = $row["id_destino"];
            $idDet = (array_key_exists($cveDet, $arrEdoMpioStd["datos"])) ? $arrEdoMpioStd["datos"][$cveDet]["idDet"] : 0;
            $idEM = (array_key_exists($cveDet, $arrEdoMpioStd["datos"])) ? $arrEdoMpioStd["datos"][$cveDet]["idEM"] : 0;
            //            echo $cveDet."--".$idDet."--".$idEM."/ ";
            $edoCor = (array_key_exists($cveDet, $arrEdoMpioStd["datos"])) ? $arrEdoMpioStd["datos"][$cveDet]["edoCor"] : "";
            $mpio = (array_key_exists($cveDet, $arrEdoMpioStd["datos"])) ? $arrEdoMpioStd["datos"][$cveDet]["mpio"] : "";
            $fechaComp = formatDate(trim($row["commitment_date2"]));
            //                    $colorNoLiberado = ($row["statPed"] == "OP" && $row["Autoriza"] == 2) ? "style='color:red;'" : "";
            $colorNoLiberado = "";

            if ($ltsxEnviar > 0) {
                $inputAtq = "<input class='inputsPed inputAtq' value='' type='text'/>";
                $inputLts = "<input atq='' class='inputsPed inputLts' type='text' placeholder='" . $ltsxEnviar . "' value='" . $ltsxEnviar . "'/>";
                $inputEta = "<input class='inputsPed inputEta' value='" . $fechaComp . "' readonly='true' type='text'/>";
                $inputCausa = "<input class='inputsPed inputCausa' value='' placeholder='Cuando Aplique' type='text'/>";
                //Creando tabla
                $table .= "<tr $colorNoLiberado idpr='' idDet='" . $idDet . "'>";
                $table .= "<td class='atqNmbr'></td>"; //0
                $table .= "<td>$inputAtq</td>";
                $table .= "<td class='numeric'>$inputLts</td>";
                $table .= "<td>$inputEta</td>";
                $table .= "<td>$inputCausa</td>";
                $table .= "<td>" . $edoCor . "</td>"; //Estado Estándar
                $table .= "<td idEM='$idEM'>" . $mpio . "</td>"; //Ciudad Estándar
                $table .= "<td>" . $row["pedido"] . "</td>";
                $table .= "<td>" . $row["create_date"] . "</td>"; //Fecha de Pedido
                $table .= "<td>" . $fechaComp . "</td>"; //Fecha Compromiso
                $table .= "<td>" . $row["cliente"] . "</td>"; //Clave del Cliente
                $table .= "<td>" . $row["cliente_nombre"] . "</td>"; //Cliente
                $table .= "<td>" . $row["destino"] . "</td>"; //en SCP es el Determinante
                $table .= "<td>" . $row["clave"] . "</td>"; //Clave Producto
                $table .= "<td>" . $row["descripcion"] . "</td>"; //Producto
                $table .= "<td>" . $row["estado"] . "</td>"; //Estado Odoo
                $table .= "<td>" . $row["ciudad"] . "</td>"; //Ciudad Odoo
                $table .= "<td>" . $ltsPedido . "</td>"; //Litros en el pedido
                $table .= "<td>" . $ltsxEnviar . "</td>"; //Litros Pendientes (Sin Ruteo)
                $table .= "<td>" . $row["albaran"] . "</td>";
                $table .= "<td>" . $row["ventas"] . "</td>";
                $table .= "<td>" . $row["estado_remision"] . "</td>";
                $table .= "<td>" . $row["soid"] . "</td>";
                $table .= "</tr>";
            }
        }
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 2;
        $respuesta["error"] = "Sin Datos";
    }
    $respuesta["table"] = $table;
    return json_encode($respuesta);
}

function formatDate($dateddmmyyyy)
{
    $v = str_replace("'", " ", $dateddmmyyyy);
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
    return $v;
}

// function allBoxes()
// {
//     $dataconn = dataconn("intranet");
//     $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
//     $respuesta["status"] = 1;
//     $respuesta["error"] = "";
//     if ($mysqli->connect_errno) {
//         $respuesta["status"] = 0;
//         $respuesta["error"] = $mysqli->connect_error;
//         exit(json_encode($respuesta));
//     }
//     // $respuesta["enRuteo"] = boxesRuteados();
//     // $respuesta["enCarga"] = boxesCarga($mysqli);
//     $respuesta["enCamino"] = boxesCamino($mysqli);
//     $mysqli->close();
//     return json_encode($respuesta);
// }

function boxesRuteados()
{
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $query = <<<SQL
        SELECT 
            numCamion k,
            GROUP_CONCAT(id_pre_ruteo) ipr,
            SUM(ltsSurtir) lts,
            pr.eta,
            pr.pedido,
            pr.fuente_pedido,
            pr.cveProducto,
            cds.edoCor,
            cds.mpio,
            CveCliente,
            LEFT(pr.cliente,5) cliente,
            left(TRIM(pr.determinante),5) det
        FROM
            smartRoad_pre_ruteo pr 
            LEFT JOIN
            smartRoad_stdEdosMpios cds 
            ON pr.id_edoMpio = cds.id 
        WHERE STATUS = "pre" 
        GROUP BY numCamion,
            cveCliente,
            pr.determinante,
            pr.eta,pr.pedido 
        ORDER BY numCamion,
            edoCor,
            mpio,
            lts
SQL;
    $result = $mysqli->query($query) or die($mysqli->error);
    $keys = [];
    $iprs = [];
    $lts = [];
    $boxes = [];
    $key = "";
    $convertFuente = array("Odoo Raloy" => array("label" => "RAL", "color" => "#1a509c"), "SCP ZK" => array("label" => "ZAR", "color" => "#13AC61"));
    while ($row = $result->fetch_assoc()) {
        $fuente = (array_key_exists($row["fuente_pedido"], $convertFuente)) ? "<span style='color:" . $convertFuente[$row["fuente_pedido"]]["color"] . "'>" . $convertFuente[$row["fuente_pedido"]]["label"] . "</span>" : substr($row["fuente_pedido"], 0, 2);
        $key = $row["k"];
        $keys[] = $key;
        $iprs[$key][] = $row["ipr"];
        $lts[$key][] = $row["lts"];
        $boxes[$key][] = array("eta" => $row["eta"], "lts" => number_format($row["lts"], 0), "mpio" => utf8_encode($row["mpio"]), "edoCor" => utf8_encode($row["edoCor"]), "cveCte" => utf8_encode($row["CveCliente"]), "det" => utf8_encode($row["det"]), "pedido" => $row["pedido"], "fuente" => $fuente, "cveProd" => utf8_encode($row["cveProducto"]), "cliente" => utf8_encode($row["cliente"]));
    }
    $keys = array_unique($keys);
    foreach ($keys as $key) {
        $iprs[$key] = implode(",", $iprs[$key]);
        $lts[$key] = array_sum($lts[$key]);
    }
    $respuesta["data"]["keys"] = $keys;
    $respuesta["data"]["iprs"] = $iprs;
    $respuesta["data"]["lts"] = $lts;
    $respuesta["data"]["boxes"] = $boxes;
    $respuesta["status"] = 1;
    $respuesta["error"] = $mysqli->error;
    $mysqli->close();
    return json_encode($respuesta);
}

function boxesCarga()
{
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $query = <<<SQL
        SELECT 
            c.id_entrega,
            c.placas,
            f.capacidad,
            c.fecha_carga,
            c.fecha_regreso,
            c.planta_carga,
            c.planta_regreso,
            c.status,
            c.numEnvioRaloy 
        FROM
            smartRoad_entregas c 
            INNER JOIN
            smartRoad_flota f 
            ON f.placas = c.placas 
        WHERE c.status IN ("carga", "cargado") 
        GROUP BY c.id_entrega 
        ORDER BY c.STATUS,
            c.id_entrega DESC 
            LIMIT 200
SQL;
    if ($result = $mysqli->query($query)) {

        //CON DATOS
        $boxes = [];
        $fechaHoy = date("Y-m-d");
        while ($row = $result->fetch_assoc()) {
            $dataDetalles = detallesBox($row["id_entrega"], $mysqli, "carga");
            $detalles = $dataDetalles["detalles"];
            $arrStatus = $dataDetalles["status"];
            $ltsTot = $dataDetalles["suma"];
            $utilizacion = $ltsTot / $row["capacidad"];
            if (in_array(0, $arrStatus)) {
                if (in_array(1, $arrStatus)) {
                    $colorClass = "cargaIncompleta"; //incompletos - amarillo
                    $accionBoton = "";
                } else {
                    $colorClass = "cargaNoIniciada"; //Sin datos - rojo
                    $accionBoton = "";
                }
            } else {
                $colorClass = "cargaTerminada"; //completos-green
                $accionBoton = "ponerEnCamino";
            }
            $box = "";
            $colorFolio = ($fechaHoy <= $row["fecha_carga"]) ? "green" : "red";
            $box .= "<div class='boxAll boxCar $colorClass' folio='" . $row["id_entrega"] . "'>";
            $box .= "<div class='d-flex justify-content-around align-items-center'><div class='w-50 p-2'><input id_entrega='" . $row["id_entrega"] . "' type='text' class='inputInlineCartaPorte form-control form-control-sm' placeholder='Carta Porte' value='" . $row["numEnvioRaloy"] . "'/></div><div class='folioCarga' style='color:$colorFolio;'>FOLIO: " . $row["id_entrega"] . "</div></div>";
            $box .= "<div class='left'>";
            $box .= "<span class='titleBoxCarga'><i class='fa fa-truck'></i> " . utf8_encode($row["placas"]) . "</span>";
            $box .= "<span class='ltsTotBoxCarga'>" . number_format($ltsTot, 0) . " L</span>";
            $box .= "<span class='utilizacionBoxCarga'>" . number_format($utilizacion * 100, 2) . " %</span>";
            $box .= "<span class='salidaBoxCarga'>" . $row["fecha_carga"] . " " . utf8_encode($row["planta_carga"]) . "</span>";
            $box .= "<span class='salidaBoxCarga'>" . $row["fecha_regreso"] . " " . utf8_encode($row["planta_regreso"]) . "</span>";
            $box .= "</div>";
            $box .= "<div class='right'>";
            $box .= "<div>" . $detalles . "</div>";
            $box .= "</div><br style='clear:both'/>";
            $box .= "<div class='ctrBoxCarga'><div class='boxCargaBtns' identrega='" . $row["id_entrega"] . "'><button title='Cancelar Solicitud' class='cancelarCarga'><i class='fa fa-trash'></i></button><button title='Consultar Solicitud' class='consultar'><i class='fas fa-file-alt'></i></button><button title='Poner En Camino' class='$accionBoton'><i class='fa fa-check'></i></button></div></div>";
            $box .= "</div>";
            $boxes[] = $box;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        exit(json_encode($respuesta));
    }
    $mysqli->close();
    $respuesta["data"]= "<div>" . implode("</div><div>", $boxes) . "</div>";
    $respuesta["status"]=1;
    return json_encode($respuesta);
}
// function boxesRuteo(){
//     $iprs="";
//     $k="";
//     $arrColores=array();
//     $boxes=[];
//     $box='<div class="boxAll" iprs="'.$iprs.'"><div class="left"><div><span atq="'.$atq.'" class="subtots"><span class="cardIcon" style="color:'.$color.'"><i class="fa fa-tag"></i> '.$ruta.'</span></span></div><div><span class="ltsBox">'.$lts.' L</span></div></div><div class="right"><div style="padding: 5px; box-sizing: border-box; width: 100%; float: left; border-top: 1px solid rgb(217, 218, 219);"><span class="tagProdCte">od DAIML SO287805 P11074</span><span class="tagEtayLts">2022-01-11 <span class="tagLtsSurtir">11,000</span>:</span><span class="tagUbic">Calera, ZAC</span></div></div><br style="clear: both;"><div class="ctrBoxRuteo"><div class="boxRuteoBtns" iprs="23390"><button title="Eliminar Ruta" class="cancelarRuteo"><i class="fa fa-trash"></i></button> <button title="Confirmar Ruta" class="ponerEnCarga"><i class="fa fa-check"></i></button></div></div></div>';
// }
function boxesCamino()
{
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    $query =
     <<<SQL
        SELECT 
            c.id_entrega,
            c.placas,
            f.capacidad,
            f.propia_externa tipoTransporte,
            c.fecha_carga,
            c.fecha_regreso,
            c.planta_carga,
            c.planta_regreso,
            c.status,
            c.numEnvioRaloy
        FROM
            smartRoad_entregas c 
            INNER JOIN
            smartRoad_flota f 
            ON f.placas = c.placas 
        WHERE c.STATUS IN ("camino") 
        GROUP BY c.id_entrega 
        ORDER BY c.STATUS,
            c.id_entrega DESC 
        LIMIT 200
SQL;

    if ($result = $mysqli->query($query)) {
        //CON DATOS
        $box = "";
        if ($result->num_rows > 0) {
            $fechaHoy = date("Y-m-d");
            while ($row = $result->fetch_assoc()) {
                $respuesta["status"] = 1;
                $dataDetalles = detallesBox($row["id_entrega"], $mysqli, "camino");
                $detalles = $dataDetalles["detalles"];
                $ltsTot = $dataDetalles["suma"];
                $utilizacion = $ltsTot / $row["capacidad"];
                $colorFolio = ($fechaHoy <= $row["fecha_regreso"]) ? "green" : "red";
                $tipoTransporte = strtolower(substr($row["tipoTransporte"], 0, 3));
                $box .= "<div class='boxAll'>";
                $box .= "<div class='folioCarga' style='color:$colorFolio;'>FOLIO: " . $row["id_entrega"] . "</div>";
                $box .= "<div class='left'>";
                $box .= "<span class='titleBoxCarga'><i class='fa fa-truck'></i> " . utf8_encode($row["placas"]) . "</span>";
                $box .= "<span class='ltsTotBoxCarga'>" . number_format($ltsTot, 0) . " L</span>";
                $box .= "<span class='utilizacionBoxCarga'>" . number_format($utilizacion * 100, 2) . " %</span>";
                $box .= "<span class='salidaBoxCarga'>" . $row["fecha_carga"] . " " . utf8_encode($row["planta_carga"]) . "</span>";
                $box .= "<span class='salidaBoxCarga'>" . $row["fecha_regreso"] . " " . utf8_encode($row["planta_regreso"]) . "</span>";
                $box .= "<span class='salidaBoxCarga'>Núm. Emb.:" . $row["numEnvioRaloy"] . "</span>";
                $box .= "</div>";
                $box .= "<div class='right'>";
                $box .= "<div class='datosencamino'>" . $detalles . "</div>";
                $box .= "</div><br style='clear:both'/>";
                $box .= "<div class='ctrBoxCarga'><div class='boxCargaBtns' placas='" . $row["placas"] . "' numEnvioRaloy='" . $row["numEnvioRaloy"] . "' tipotransporte='" . utf8_encode($row["tipoTransporte"]) . "' ltsTot='" . $ltsTot . "' identrega='" . $row["id_entrega"] . "'><button title='Cancelar Envío' class='cancelarEnvio'><i class='fa fa-trash'></i></button><button title='Consultar Certificado' class='consultarCert'><i class='fas fa-file-alt'></i></button><button title='Concluir Envío' class='concluirEnvio_$tipoTransporte'><i class='fa fa-check'></i></button></div></div>";
                $box .= "</div>";
            }
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $mysqli->close();
        exit(json_encode($respuesta));
    }
    $respuesta["status"]=1;
    $respuesta["data"]=$box;
    return json_encode($respuesta);
}

function boxesSearch()
{
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        exit(json_encode($respuesta));
    }
    $id_entrega = filter_input(INPUT_GET, "i");
    $query = <<<SQL
        SELECT 
            c.id_entrega,
            c.placas,
            f.capacidad,
            f.propia_externa tipoTransporte,
            c.fecha_carga,
            c.fecha_regreso,
            c.planta_carga,
            c.planta_regreso,
            c.status,
            c.numEnvioRaloy
        FROM
            smartRoad_entregas c 
            INNER JOIN
            smartRoad_flota f 
            ON f.placas = c.placas 
        WHERE c.id_entrega IN ($id_entrega)
        GROUP BY c.id_entrega 
        ORDER BY c.STATUS,
            c.id_entrega DESC 
SQL;

    if ($result = $mysqli->query($query)) {
        //CON DATOS
        $box = "";
        if ($result->num_rows > 0) {
            $fechaHoy = date("Y-m-d");
            while ($row = $result->fetch_assoc()) {
                $respuesta["status"] = 1;
                $dataDetalles = detallesBox($row["id_entrega"], $mysqli, "Terminado");
                $detalles = $dataDetalles["detalles"];
                $ltsTot = $dataDetalles["suma"];
                $utilizacion = $ltsTot / $row["capacidad"];
                $colorFolio = ($fechaHoy <= $row["fecha_regreso"]) ? "green" : "red";
                $tipoTransporte = strtolower(substr($row["tipoTransporte"], 0, 3));
                //                $box.="<div class='col-md-8'>";
                $box .= "<div class='card-block col-md-10 my-4'>";
                $box .= "<div class='card-header' style='color:$colorFolio;'>FOLIO: " . $row["id_entrega"] . "</div>";
                $box .= "<div class='row'>";
                $box .= "<div class='col-md-5 mx-2 p-2'>";
                $box .= "<span class='titleBoxCarga'><i class='fa fa-truck'></i> " . utf8_encode($row["placas"]) . "</span>";
                $box .= "<span class='ltsTotBoxCarga'>" . number_format($ltsTot, 0) . " L</span>";
                $box .= "<span class='utilizacionBoxCarga'>" . number_format($utilizacion * 100, 2) . " %</span>";
                $box .= "<span class='salidaBoxCarga'>" . $row["fecha_carga"] . " " . utf8_encode($row["planta_carga"]) . "</span>";
                $box .= "<span class='salidaBoxCarga'>" . $row["fecha_regreso"] . " " . utf8_encode($row["planta_regreso"]) . "</span>";
                $box .= "<span class='salidaBoxCarga'>Núm. Emb.:" . $row["numEnvioRaloy"] . "</span>";
                $box .= "</div>";
                $box .= "<div class='col-md-5 mx-2 p-2'>";
                $box .= "<div class='datosencamino'>" . $detalles . "</div>";
                $box .= "</div>";
                $box .= "</div>";
                $box .= "<div class='card-footer p-0'>"
                    . "<div class='boxCargaBtns btn-group btn-group w-100 p-2' placas='" . $row["placas"] . "' numEnvioRaloy='" . $row["numEnvioRaloy"] . "' tipotransporte='" . utf8_encode($row["tipoTransporte"]) . "' ltsTot='" . $ltsTot . "' identrega='" . $row["id_entrega"] . "'>"
                    . "<button title='Regresar a En Camino' class='btn btn-primary cancelarTerminado'><i class='fa fa-trash'></i></button>"
                    //                        . "<button title='Consultar Certificado' class='consultarCert'><i class='fas fa-file-alt'></i></button>"
                    . "<button title='Hoja de Costos' class='btn btn-primary formCambiaCosto'><i class='fa fa-file-alt'></i></button></div>"
                    . "</div>";
                $box .= "</div>";
                //                $box.="</div>";
            }
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $mysqli->close();
        exit(json_encode($respuesta));
    }
    $mysqli->close();
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $respuesta["box"] = $box;
    return json_encode($respuesta);
}

function detallesBox($id_entrega, $mysqli, $stat)
{
    if ($id_entrega > 0) {
        $restridentrega = "r.id_entrega=$id_entrega AND ";
    } else {
        $restridentrega = "";
    }
    $query = <<<SQL
            SELECT 
                r.eta,
                r.id_pre_ruteo ipr,
                LEFT(em.mpio, 17) mpio,
                em.edoCor edo,
                r.cveProducto cve,
                r.pedido ped,
                LEFT(r.cliente,5) cliente,
                SUM(r.ltsSurtir) lts,
                r.fuente_pedido,
                GROUP_CONCAT(IFNULL(statusCargaZK,0)) statCarga
            FROM
                smartRoad_pre_ruteo r 
                INNER JOIN
                smartRoad_stdEdosMpios em 
                ON r.id_edoMpio = em.id 
            WHERE $restridentrega status='$stat'
            GROUP BY r.id_entrega,
                r.id_pre_ruteo,
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
    $convertFuente = array("Odoo Raloy" => array("label" => "RAL", "color" => "#1a509c"), "SCP ZK" => array("label" => "ZAR", "color" => "#13AC61"));
    while ($row = $result->fetch_assoc()) {
        $ipr = $row["ipr"];
        $eta = $row["eta"];
        $lts = number_format($row["lts"], 2);
        $mpio = utf8_encode($row["mpio"]);
        $edo = utf8_encode($row["edo"]);
        $fuente = (array_key_exists($row["fuente_pedido"], $convertFuente)) ? "<span style='color:" . $convertFuente[$row["fuente_pedido"]]["color"] . "'>" . $convertFuente[$row["fuente_pedido"]]["label"] . "</span>" : substr($row["fuente_pedido"], 0, 2);
        $prodCte = utf8_encode($row["cliente"]) . " " . $row["ped"] . " " . $row["cve"];

        $spans[] = <<<HTML
                <span class='tagUbic'>$mpio, $edo</span>
                <div class='eta w-100 mt-2'><input class='form-control form-control-sm changeEtaInput' curr_eta='$eta' ipr='$ipr' type="date" value="$eta"/></div> 
                <div class="d-flex w-100"><span class='mr-1'>$fuente</span>$prodCte</div>
                <div class="text-primary"><strong>$lts</strong></div>
HTML;
        $stats = array_merge(explode(",", $row["statCarga"]), $stats);
        $suma += $row["lts"];
    }
    $detalles = implode("<hr/>", $spans);
    $respuesta["status"] = array_unique($stats);
    $respuesta["detalles"] = $detalles;
    $respuesta["suma"] = $suma;
    return $respuesta;
}

function guarda_pre_ruteo()
{
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $values = $_GET["strValues"];
    if (strlen($_GET["strToDel"]) >= 1) {
        $deletion = json_decode(delete_from_pre_ruteo());
        if ($deletion["status"] != 1) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $deletion["error"];
            return $respuesta;
        }
    }
    $query = <<<SQL
    INSERT INTO smartRoad_pre_ruteo 
    (
        numCamion,
        ltsSurtir,
        eta,
        causa_cambio_fecha,
        id_edoMpio,
        pedido,
        fechaPedido,
        fechaCompromiso,
        cveCliente,
        cliente,
        determinante,
        cveProducto,
        nombreProducto,
        estado,
        municipio,
        ltsPedido,
        albaran,
        ventas,
        estadoRemision,
        soid,
        usuario_modif,
        id_det_origen,
        id_determinante,
        fuente_pedido
    ) 
    VALUES
        $values
SQL;
    //    echo $query;
    $respuesta["q"] = $query;
    if ($mysqli->query($query)) {
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function delete_from_pre_ruteo()
{
    $idsToDel = $_GET["strToDel"];
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $query = <<<SQL
    DELETE from smartRoad_pre_ruteo where id_pre_ruteo in ($idsToDel) and status='pre';
SQL;
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

//********************* Solicitudes de carga *****************

function previsualizarPapeleta()
{
    $iprs = $_GET["iprs"];
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    $query = <<<SQL
        SELECT 
        pre_ruteo.id_pre_ruteo ipr,
        pre_ruteo.cveCliente,
        pre_ruteo.Determinante,
        pre_ruteo.pedido,
        m.edoCor,
        m.mpio,
        pre_ruteo.ltsSurtir,
        pre_ruteo.cveProducto,
        pre_ruteo.nombreProducto 
      FROM
        smartRoad_pre_ruteo pre_ruteo 
        LEFT JOIN
        smartRoad_stdEdosMpios m 
        ON pre_ruteo.id_edoMpio = m.id 
      WHERE pre_ruteo.id_pre_ruteo IN ($iprs) 
      ORDER BY pre_ruteo.eta 
SQL;
    $respuesta["q"] = $query;
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }

        if ($result->num_rows > 0) {
            $tbody = "";
            $total = 0;
            $tbody = "";
            while ($row = $result->fetch_assoc()) {
                $respuesta["status"] = 1;
                $tbody .= "<tr>";
                $tbody .= "<td>" . $row["cveCliente"] . " " . utf8_encode($row["Determinante"]) . "</td>";
                $tbody .= "<td>" . $row["pedido"] . "</td>";
                $tbody .= "<td>" . utf8_encode($row["mpio"]) . ", " . utf8_encode($row["edoCor"]) . "</td>";
                $tbody .= "<td class='numeric'>" . number_format($row["ltsSurtir"], 2) . "</td>";
                $tbody .= "<td>" . $row["cveProducto"] . " " . utf8_encode($row["nombreProducto"]) . "</td>";
                $tbody .= "</tr>";
                $total += $row["ltsSurtir"];
            }
            $tfoot = "<tr><th colspan='3'>Total</th><th style='text-align:right' id='totalLtsCarga' lts='" . $total . "'>" . number_format($total, 2) . "</th><th colspan='2' style='text-align:left;'></th></tr>";
            $respuesta["tbody"] = $tbody;
            $respuesta["tfoot"] = $tfoot;
            $respuesta["ltsSurtir"] = $total;
        } else {
            $respuesta["status"] = 2;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function generaSolCarga()
{
    $iprs = $_GET["iprs"];
    if (!$iprs) {
        $respuesta["status"] = 0;
        return json_encode($respuesta);
    }
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    $values = "('" . $_GET["placas"] . "','" . $_GET["fecha_carga"] . "','" . $_GET["planta_carga"] . "','" . $_GET["fecha_regreso"] . "','" . $_GET["planta_regreso"] . "','" . $_GET["obs"] . "','" . $_GET["usuario"] . "'," . $_GET["litros"] . ",'carga')";

    $query = <<<SQL
    INSERT INTO smartRoad_entregas
    (
        placas,fecha_carga,planta_carga,fecha_regreso,planta_regreso,obs,usuario,litros,status
    ) 
    VALUES
        $values;
SQL;
    $respuesta["qIns"] = $query;
    if ($mysqli->query($query)) {
        $nuevoId = $mysqli->insert_id;
        $queryUpd_pre_ruteo = "UPDATE smartRoad_pre_ruteo SET id_entrega=$nuevoId,status='carga' WHERE id_pre_ruteo IN ($iprs)";

        $respuesta["qUpd"] = $queryUpd_pre_ruteo;
        if ($mysqli->query($queryUpd_pre_ruteo)) {
            $respuesta["status"] = 1;
            $respuesta["idNuevo"] = $nuevoId;
        } else {
            $respuesta["status"] = 0;
            $respuesta["error"] = "UPD ERR " . $mysqli->error;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "INS ERR " . $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function selAtqs()
{
    $fechaCarga = $_GET["fechaCarga"];
    $ltsSurtir = $_GET["ltsSurtir"];

    $query = <<<SQL
           SELECT 
            f.placas,
            capacidad 
          FROM
            smartRoad_flota f;
SQL;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows == 0) {
            $respuesta["status"] = 2;
        } else {
            $options = "<option value=''></option>";
            while ($row = $result->fetch_assoc()) {
                $options .= "<option value='" . utf8_encode($row["placas"]) . "' capacidad='" . number_format($row["capacidad"], 0) . "'>" . utf8_encode($row["placas"]) . " " . number_format(utf8_encode($row["capacidad"]), 0) . "</option>";
            }
            $respuesta["options"] = $options;
            $respuesta["status"] = 1;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function selPlantas()
{
    $query = "SELECT 
  p.planta,
  AlmacenR,
  CONCAT(LEFT(em.mpio, 8),'.., ', em.edoCor) ubic 
FROM
  smartRoad_plantas p 
  LEFT JOIN
  smartRoad_stdEdosMpios em 
  ON p.id_relEdoMpio = em.id";
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }
    if ($result = $mysqli->query($query)) {
        $options = "<option value=''></option>";
        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='" . utf8_encode($row["planta"]) . "' almacen='".$row["AlmacenR"]."'>" . utf8_encode($row["planta"]) . " " . utf8_encode($row["ubic"]) . "</option>";
        }
        $respuesta["options"] = $options;
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

//Cargas*********************
function ponerEnCamino_cargaDatos()
{
    $folio = $_GET["folio"];
    $fechaHoy = date("Y-m-d");
    $query = <<<SQL
  SELECT
    '$fechaHoy' fechaSolicitud,
    c.usuario usuario,
    c.placas,
    f.capacidad capac,
    c.fecha_carga,
    c.fecha_regreso,
    c.planta_carga,
    c.planta_regreso
  FROM
    smartRoad_entregas c 
    INNER JOIN
    smartRoad_flota f 
    ON c.placas = f.placas 
   WHERE c.id_entrega=$folio
SQL;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $mysqli->query("SET NAMES 'utf8'");
    if ($result = $mysqli->query($query)) {
        //CON DATOS
        if ($result->num_rows > 0) {
            $respuesta["status"] = 1;
            $respuesta["data"] = $result->fetch_assoc();
            $capacidad = $respuesta["data"]["capac"];
        } else {
            $respuesta["status"] = 2;
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }


    //detalles*******
    $detalles = ponerEnCamino_detalles($mysqli);
    if ($detalles["status"] == 1) {
        $trs = $detalles["trs"];
        $totLts = $detalles["lts"];
        $respuesta["data"]["tablaDatos"] = $trs;
        $respuesta["data"]["utilizUnid"] = number_format(($totLts / $capacidad) * 100, 2) . " %";
        $respuesta["data"]["totalLts"] = number_format($totLts);
        $respuesta["data"]["capac"] = number_format($capacidad) . " L";
    } else {
        $respuesta["status"] = $detalles["status"];
        $respuesta["error"] = $detalles["error"];
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function ponerEnCamino_detalles($mysqli)
{
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
        r.sellosDescarga sellos,
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
            LEFT (cliente, 7),
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
    ORDER BY r.cveProducto,
        em.edoCor,
        em.mpio,
        r.eta,
        lt DESC 
SQL;
    $respuesta["status"] = 1;
    $trs = "";
    $respuesta["lts"] = 0;
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {

            $iprs = $row["iprs"];
            $texttoshow = utf8_encode($row["conc"]);
            $inputlept = $row["loteept"];
            $inputlpt = $row["lotept"];
            $inputsellos = $row["sellos"];
            $inputremision = $row["rems"];
            if ($row["statusCargaZK"] == 1) {
                $getcert = "<i class='fa fa-file-alt cert' style='color:#4ca64c' folio='$folio' iprs='$iprs'></i>";
            } else {
                $getcert = "";
            }
            $trs .= "<tr iprs='$iprs' folio='$folio'><td>" . $texttoshow . "</td><td>" . $inputlept . "</td><td>" . $inputlpt . "</td><td>" . $inputsellos . "</td><td>" . $inputremision . "</td><td class='certCtr'>$getcert</td></tr>";
            $respuesta["lts"] += $row["lt"];
        }

        $respuesta["trs"] = $trs;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        exit(json_encode($respuesta));
    }
    return $respuesta;
}

function ponerEnCamino_save()
{
    $idEntrega = $_GET["idEntrega"];
    $numEmbarqueR = $_GET["numEmbarqueR"];
    $pesoNeto = $_GET["pesoNeto"];
    $usuarioEnvio = $_GET["usuarioEnvio"];
    $updQueries = $_GET["updQueries"];
    $fechaHr = date("Y-m-d H:i:s");

    if (!$idEntrega || $idEntrega == "") {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Id no recibido";
        return json_encode($respuesta);
    }
    $queries = <<<SQL
        UPDATE 
            smartRoad_entregas e 
        SET
            numEnvioRaloy='$numEmbarqueR',
            pesoNeto = $pesoNeto,
            usuarioEnvio='$usuarioEnvio',
            status = 'camino',
            fechaSistEnvio = '$fechaHr' 
        WHERE id_entrega = $idEntrega ;
        $updQueries
SQL;
    //    $respuesta["qIns"] = $query;
    $queries = explode(";", $queries);
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->autocommit(false);

    $errors = array();
    $i = 0;
    foreach ($queries as $query) {

        if ($query != "") {
            $i = $i + 1;
            if (!$mysqli->query($query)) {
                $errors[] = $mysqli->error . " #q:" . $i;
            }
        }
    }

    if (count($errors) === 0) {
        $mysqli->commit();
        $respuesta["status"] = 1;
    } else {
        $mysqli->rollback();
        $respuesta["error"] = implode("//", $errors);
        $respuesta["status"] = 0;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function cancelarCarga()
{
    $idEntrega = $_GET["idEntrega"];

    if (!$idEntrega || $idEntrega == "") {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Id no recibido";
        return json_encode($respuesta);
    }
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $queries = [];
    $queries[] = <<<SQL
    UPDATE 
        smartRoad_entregas e 
      SET
        status='carga cancelada'
      WHERE id_entrega = $idEntrega;
SQL;
    $queries[] = "DELETE FROM smartRoad_pre_ruteo WHERE id_entrega=$idEntrega ";
    $mysqli->autocommit(false);

    $errors = array();
    $i = 0;
    foreach ($queries as $query) {

        if ($query != "") {
            $i = $i + 1;
            if (!$mysqli->query($query)) {
                $errors[] = $mysqli->error . " #q:" . $i;
            }
        }
    }

    if (count($errors) === 0) {
        $mysqli->commit();
        $respuesta["status"] = 1;
    } else {
        $mysqli->rollback();
        $respuesta["error"] = implode("//", $errors);
        $respuesta["status"] = 0;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function cancelarEnvio()
{
    $idEntrega = $_GET["idEntrega"];

    if (!$idEntrega || $idEntrega == "") {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Id no recibido";
        return json_encode($respuesta);
    }
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    $query = <<<SQL
    UPDATE 
        smartRoad_entregas e 
      SET
        status = 'carga'
      WHERE id_entrega = $idEntrega; 
      UPDATE smartRoad_pre_ruteo SET status='carga' WHERE id_entrega=$idEntrega;  
SQL;
    if ($mysqli->multi_query($query)) {
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "ERR: " . $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function dimeCostoSCPExterna()
{
    //    $numEnvioRaloy = $_GET["numEnvioRaloy"];
    //    global $host, $user, $pass, $db, $port;
    //    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    //    $query = "SELECT 
    //  SUM(t.CostoReal) costo
    //FROM
    //  FTransporteEnvio t 
    //WHERE 
    //  t.ConsEnvio='$numEnvioRaloy'
    // GROUP BY t.consEnvio";
    //    if ($result = $mysqli->query($query)) {
    //        $row = $result->fetch_assoc();
    $respuesta["status"] = 1;
    $respuesta["costo"] = 0;
    //    } else {
    //        $respuesta["status"] = 0;
    //    }
    //    $mysqli->close();
    return json_encode($respuesta);
}

function concluirEnvio_save()
{
    $idEntrega = $_GET["idEntrega"];
    $date = date("Y-m-d h:i:s");
    $id_usuario = $_SESSION["sessionInfo"]["userSession"];
    $arr = $_GET["valores"];
    $fields = [];
    $values = [];
    $ond = [];
    $fields[] = "id_entrega";
    $values[] = $idEntrega;
    $fields[] = "bitacora";
    $values[] = "'" . utf8_decode($_GET["bitacora"]) . "'";
    $ond[] = "bitacora='" . utf8_decode($_GET["bitacora"]) . "'";
    $fields[] = "idusuariocosteo";
    $values[] = "'" . $id_usuario . "'";
    $ond[] = "idusuariocosteo='$id_usuario'";
    $fields[] = "fechahrcosteo";
    $values[] = "'" . $date . "'";
    $ond[] = "fechahrcosteo='$date'";
    foreach ($arr as $key => $valor) {
        $fields[] = $key;
        $values[] = ($valor > 0) ? $valor : 0;
        $ond[] = "$key=$valor";
    }
    $strfields = implode(",", $fields);
    $strvalues = implode(",", $values);
    $strond = implode(",", $ond);
    $queries[] = "INSERT INTO smartRoad_costeo ($strfields) VALUES($strvalues) ON DUPLICATE KEY UPDATE $strond;";

    $queries[] = <<<SQL
        UPDATE 
        smartRoad_entregas e 
        SET
        e.fechaCierre = '$date',
        usuarioCierre='$id_usuario',
        e.STATUS='Terminado'
        WHERE e.id_entrega = $idEntrega;
SQL;
    $queries[] = <<<SQL
    UPDATE 
        smartRoad_pre_ruteo 
    SET
        status = 'Terminado' 
    WHERE id_entrega = $idEntrega;        
SQL;

    //    $respuesta["queries"] = implode(";", $queries);
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }

    $mysqli->autocommit(false);

    $errors = array();
    foreach ($queries as $query) {
        if (!$mysqli->query($query)) {
            $errors[] = $mysqli->error;
        }
    }

    if (count($errors) === 0) {
        $mysqli->commit();
        $respuesta["status"] = 1;
    } else {
        $mysqli->rollback();
        $respuesta["error"] = implode("//", $errors);
        $respuesta["status"] = 0;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function dimepreciodiesel()
{
    $places = simplexml_load_file("https://publicacionexterna.azurewebsites.net/publicaciones/prices") or die("Failed to load");
    $suma = 0;
    $conteo = 0;
    foreach ($places->children() as $place) {
        if ($place->gas_price[2]["type"] != null) {
            $suma += $place->gas_price[2][0];
            $conteo = $conteo + 1;
        }
    }
    $avg = $suma / $conteo;
    $avgsiniva = round($avg / 1.16, 3);
    return $avgsiniva;
}

function obtenerFijosPipa()
{
    $placas = $_GET["placas"];
    $query = "SELECT pesopipasincargakg pesosincarga,longitudunidadmts longitudm,pesounidadcargakg pesounidadcarga,llantas100k llantas,chofer_mensual chofer,depreciacion_mensual depreciacion,mantenimiento100k mantenimiento,administracion_mensual administracion,seguro_mensual seguro,otrosfijos_mensual otrosfijos FROM smartRoad_flota WHERE placas='$placas'";
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $result = $mysqli->query($query);
    $row = $result->fetch_assoc();
    $llantas = ($row["llantas"] > 0) ? $row["llantas"] : 0;
    $chofer = ($row["chofer"] > 0) ? $row["chofer"] : 0;
    $depreciacion = ($row["depreciacion"] > 0) ? $row["depreciacion"] : 0;
    $mantenimiento = ($row["mantenimiento"] > 0) ? $row["mantenimiento"] : 0;
    $administracion = ($row["administracion"] > 0) ? $row["administracion"] : 0;
    $seguro = ($row["seguro"] > 0) ? $row["seguro"] : 0;
    $otrosfijos = ($row["otrosfijos"] > 0) ? $row["otrosfijos"] : 0;
    $arrValores = [];
    $arrValores["llantas"] = array(0, $llantas);
    $arrValores["chofer"] = array($chofer, 0);
    $arrValores["depreciacion"] = array($depreciacion, 0);
    $arrValores["mantenimiento"] = array(0, $mantenimiento);
    $arrValores["administracion"] = array($administracion, 0);
    $arrValores["seguro"] = array($seguro, 0);
    $arrValores["otrosfijos"] = array($otrosfijos, 0);
    $arrValores["pesosincarga"] = ($row["pesosincarga"] > 0) ? $row["pesosincarga"] : 0;
    $arrValores["longitudm"] = ($row["longitudm"] > 0) ? $row["longitudm"] : 0;
    $arrValores["pesounidadcarga"] = ($row["pesounidadcarga"] > 0) ? $row["pesounidadcarga"] : 0;

    return json_encode($arrValores);
}

function getCostData()
{
    $idEntrega = filter_input(INPUT_GET, "folio", FILTER_SANITIZE_NUMBER_INT);
    $query = <<<SQL
            SELECT 
                id_entrega,
                preciodiesel,
                ltsdiesel,
                odominicial,
                odomfinal,
                ltsentrega,
                tiemporuta,
                kmsrecorr,
                rendcomb,
                llantas,
                llantasu,
                chofer,
                choferu,
                depreciacion,
                depreciacionu,
                mantenimiento,
                mantenimientou,
                administracion,
                administracionu,
                seguro,
                segurou,
                otrosfijos,
                otrosfijosu,
                totalfijos,
                totalfijosu,
                diesel,
                dieselu,
                peajes,
                peajesu,
                alimentos,
                alimentosu,
                hospedaje,
                hospedajeu,
                otrosvar,
                otrosvaru,
                costoext,
                costoextu,
                repartosext,
                repartosextu,
                desviosext,
                desviosextu,
                totalvariables,
                totalvariablesu,
                costototal,
                costototalu,
                tara,
                pesoCarga,
                pesobruto,
                longitudpipa,
                bitacora,
                idusuariocosteo,
                u.nombre usuario,
                fechahrcosteo,
                escosteovigente 
            FROM
                smartRoad_costeo c 
                INNER JOIN
                framework_usuarios u 
                ON c.idusuariocosteo = u.id_usuario 
            WHERE id_entrega = $idEntrega 
            ORDER BY fechahrcosteo DESC  
SQL;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $respuesta["status"] = 1;
            $respuesta["error"] = "";
            $data = array(
                "preciodieselsiniva" => $row["preciodiesel"],
                "ltsDiesel" => $row["ltsdiesel"],
                "odomInicio" => $row["odominicial"],
                "odomFin" => $row["odomfinal"],
                "ltsembarqueconc" => $row["ltsentrega"],
                "restiemporuta" => $row["tiemporuta"],
                "kmsRecorrP" => $row["kmsrecorr"],
                "rendkmlP" => $row["rendcomb"],
                "llantas" => $row["llantas"],
                "llantasu" => $row["llantasu"],
                "chofer" => $row["chofer"],
                "choferu" => $row["choferu"],
                "depreciacion" => $row["depreciacion"],
                "depreciacionu" => $row["depreciacionu"],
                "mantenimiento" => $row["mantenimiento"],
                "mantenimientou" => $row["mantenimientou"],
                "administracion" => $row["administracion"],
                "administracionu" => $row["administracionu"],
                "seguro" => $row["seguro"],
                "segurou" => $row["segurou"],
                "otrosfijos" => $row["otrosfijos"],
                "otrosfijosu" => $row["otrosfijosu"],
                "totalF" => $row["totalfijos"],
                "totalFUnitario" => $row["totalfijosu"],
                "resdiesel" => $row["diesel"],
                "resdieselu" => $row["dieselu"],
                "respeajes" => $row["peajes"],
                "respeajesu" => $row["peajesu"],
                "resalimentos" => $row["alimentos"],
                "resalimentosu" => $row["alimentosu"],
                "reshospedaje" => $row["hospedaje"],
                "reshospedajeu" => $row["hospedajeu"],
                "resotros" => $row["otrosvar"],
                "resotrosu" => $row["otrosvaru"],
                "rescostoext" => $row["costoext"],
                "rescostoextu" => $row["costoextu"],
                "resrepartosext" => $row["repartosext"],
                "resrepartosextu" => $row["repartosextu"],
                "resdesviosext" => $row["desviosext"],
                "resdesviosextu" => $row["desviosextu"],
                "totalV" => $row["totalvariables"],
                "totalVUnitario" => $row["totalvariablesu"],
                "costototP" => $row["costototal"],
                "costototuP" => $row["costototalu"],
                "tara" => $row["tara"],
                "pesocarga" => $row["pesoCarga"],
                "pesobruto" => $row["pesobruto"],
                "longitudpipam" => $row["longitudpipa"],
                "bitacora" => utf8_encode($row["bitacora"])
            );
            $logInfo = $row["fechahrcosteo"] . " $" . number_format($row["costototal"], 2) . " $" . number_format($row["costototalu"], 2) . "/L " . utf8_encode($row["usuario"]);
            $respuesta["alldata"][] = array(
                "info" => $logInfo,
                "data" => $data,
                "esVigente" => $row["escosteovigente"]
            );
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}
function changeEta()
{
    $id_pre_ruteo = $_GET["ipr"];
    $new_eta = $_GET["new_eta"];
    $old_eta = $_GET["old_eta"];

    if (strtotime($new_eta) == strtotime($old_eta)) {
        $respuesta["status"] = 0;
        $respuesta["error"] = "La nueva fecha es igual a la actual";
    }

    $id_user = $_SESSION["sessionInfo"]["userSession"];
    $datetime_reg = date("Y-m-d");
    $sqlInsert = "INSERT INTO smartRoad_pre_ruteo_cambios_eta(fk_pre_ruteo,fk_user,datetime_reg,neweta) VALUES($id_pre_ruteo,$id_user,'$datetime_reg','$new_eta')";
    $sqlUpdate = "UPDATE smartRoad_pre_ruteo SET eta='$new_eta' WHERE id_pre_ruteo=$id_pre_ruteo";
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    try {

        $mysqli->autocommit(false);
        $mysqli->begin_transaction();

        $mysqli->query($sqlInsert);
        $mysqli->query($sqlUpdate);

        $mysqli->commit();

        $respuesta["status"] = 1;
        $respuesta["error"] = "";
    } catch (Exception $e) {
        $mysqli->rollback();
        $respuesta["status"] = 0;
        $respuesta["error"] = "ERROR " . $e;
    }
    $mysqli->close();
    return json_encode($respuesta);
}
function setCartaPorte()
{
    $carta_porte = $_GET["carta_porte"];
    $id_entrega = $_GET["id_entrega"];
    $sql = "UPDATE smartRoad_entregas SET numEnvioRaloy='$carta_porte' WHERE id_entrega=$id_entrega";
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->query($sql)) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}
function ponerEnCamino_save_inline()
{
    $id_entrega = $_GET["id_entrega"];
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $sqlCheckCartaPorte = "SELECT numEnvioRaloy from smartRoad_entregas WHERE id_entrega=$id_entrega";
    $result = $mysqli->query($sqlCheckCartaPorte);
    while ($row = $result->fetch_assoc()) {
        $carta_porte = $row["numEnvioRaloy"];
    }
    if (!$carta_porte > 0) {
        $mysqli->close();
        return json_encode(array("status"=>2,"error"=>"El número de carta porte es requerido"));
    }

    $sql = "UPDATE smartRoad_entregas SET status='camino' WHERE id_entrega=$id_entrega";
    $sql2 = "UPDATE smartRoad_pre_ruteo SET status='camino' WHERE id_entrega=$id_entrega";

    try {

        $mysqli->autocommit(false);
        $mysqli->begin_transaction();

        $mysqli->query($sql);
        $mysqli->query($sql2);

        $mysqli->commit();

        $respuesta["status"] = 1;
        $respuesta["error"] = "";
    } catch (Exception $e) {
        $mysqli->rollback();
        $respuesta["status"] = 0;
        $respuesta["error"] = "ERROR " . $e;
    }
    $mysqli->close();
    return json_encode($respuesta);
}
