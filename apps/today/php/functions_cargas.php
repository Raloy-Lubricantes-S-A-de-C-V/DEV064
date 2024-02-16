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

function pg()
{
    return pg_connect(dataconn("odoozar"));
}

function resultQueryPG($sql)
{
    $pg = pg_connect(dataconn("odoozar"));
    $result = pg_query($pg, $sql);
    $data = [];
    while ($row = pg_fetch_assoc($result)) {
        $data[] = $row;
    }

    // $respuesta["sql"]=$sql;
    $respuesta["error"] = pg_last_error($pg);
    $respuesta["error"] .= pg_result_error($result);
    $respuesta["data"] = $data;
    pg_close($pg);

    if (strlen($respuesta["error"]) > 0) {
        $respuesa["status"] = 0;
    } else {
        $respuesta["status"] = 1;
    }

    return $respuesta;
}

function dimeCargas()
{
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $strPlantas = $_SESSION["sessionInfo"]["strPlantas"]["plants"];
    $fechaUnMesAtras = date("Y-m-d", strtotime("-1 months"));
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
        WHERE c.STATUS IN ("carga") AND c.planta_carga IN ($strPlantas)
        AND c.fecha_carga>="$fechaUnMesAtras"
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
            $colorFolio = ($fechaHoy <= $row["fecha_carga"]) ? "green" : "red";
            //Status de validación AMP
            if ($row["valAMP"] == 1) {
                $colorValAMP = "#4ca64c";
                $textValAMP = $row["usuarioValAMP"] . "<br/>" . $row["fechahoraValAMP"];
            } else {
                $colorValAMP = "#DF5252";
                $textValAMP = "";
            }
            $classPesaje = ($infopesajes["conteo"] > 0) ? "ok" : "notyet";
            $classOcs = ($infoocs["conteo"] > 0) ? "ok" : "notyet";
            $classDocs = ($classCerts == "ok" && $classRem == "ok") ? "okbig" : "notyetbig";
            //Botones dependiendo las funciones y los módulos autorizados para el usuario
            //Guardar datos de carga
            $classEdit = (revisaPermisos(10) === 1 && $textValAMP == "") ? "editallowed" : "";
            //Validación AMP
            $permisoValAMP = (revisaPermisos(11) === 1 && $textValAMP == "" && $classCerts == "ok" && $classRem == "ok") ? "editallowed" : "";
            //Validación Liberación por desviación
            //            $permisoLibDesv = revisaPermisos(12);
            //Crear todos los objetos
            $box .= "<tr folio='" . $row["id_entrega"] . "' class='trcarga $classEdit'>";
            $box .= "<td>" . utf8_encode($row["planta_carga"]) . "</td>";
            $box .= "<td>" . $row["fecha_carga"] . "</td>";
            $box .= "<td style='color:$colorFolio;font-weight:bold;'>" . $row["id_entrega"] . "</td>";
            $box .= "<td>" . number_format($ltsTot, 0) . " L</td>";
            $box .= "<td> " . utf8_encode($row["placas"]) . "</td>";
            $box .= "<td class='detallesEnvio'>" . $detalles . "</td>";
            $box .= "<td class='getPapeleta' style='color:#4ca64c;font-size: calc(1em + 1vw);' folio='" . $row["id_entrega"] . "'><i class='fas fa-file-alt'></i></td>";
            $box .= "<td class='folderDoctos' style='color:#fff;text-align:center;font-size:calc(0.8em + 0.8vh)'><i class='fa fa-folder $classDocs'></i><br/><span class='onmeter'><i class='fas fa-square $classCerts'></i><i class='fas fa-square $classOcs'></i><i class='fas fa-square $classRem'></i><i class='fas fa-square $classPesaje'></i></span></td>";
            $box .= "<td class='valAMP $permisoValAMP'><i style='color:$colorValAMP;font-size: calc(0.8em + 1vw);' class='fa fa-circle'></i><br/>$textValAMP</td>";
            $box .= "</tr>";
        }
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        exit(json_encode($respuesta));
    }
    $mysqli->close();
    $respuesta["boxes"] = $box;
    return json_encode($respuesta);
}

function dimeCargasOdoo()
{

    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    $strPlantas = $_SESSION["sessionInfo"]["strPlantas"]["plants"];
    $insRaloy = [];
    $resultAptInsRaloy = get_aptins_raloy();
    if ($resultAptInsRaloy["status"] == 1) {
        $insRaloy = $resultAptInsRaloy["data"];
    }

    $fechaUnMesAtras = date("Y-m-d", strtotime("-1 months"));
    $sql = <<<SQL
        SELECT          
            sml.product_id                              producto_id,
            pp.default_code                             producto_clave,
            LEFT(pp.default_code,2)                     producto_marca,
            RIGHT(LEFT(pp.default_code,4),2)            producto_empaque,
            pt.NAME                                     producto_nombre_padre,
            sm.NAME                                     producto_nombre_variante,
            pt.categ_id                                 producto_categoria_id,
            sml.product_uom_id                          producto_unidad_medida_id,
            uom.name                                    producto_unidad_medida,
            (1/uom.factor)                              producto_cdv,
            pp.id                                       producto_id,
            pt.id                                       producto_template_id,
            sml.picking_id                              picking_id,
            sml.id                                      movimiento_id_linea,
            sml.product_qty                             movimiento_cantidad_original,
            sml.product_uom_qty                         movimiento_cantidad_uom,
            to_char(sml.DATE- interval '5 hour','YYYY-MM-DD HH24:MI:SS')   movimiento_fecha,
            sml.state                                   movimiento_status_linea,
            sml.reference                               movimiento_referencia_linea,
            sml.production_id                           movimiento_production_id,
            sm.id                                       movimiento_id,
            sp.NAME                                     remision,
            sp.id                                       remision_id,
            so.NAME                                     pedido,
            sp.sale_id                                  pedido_id,
            sol.NAME                                    pedido_linea_id,
            so.client_order_ref                         pedido_ref_cliente,
            so.plant_id                                 planta_id,
            CONCAT('[',plantas.id,'] ',plantas.name)    planta,
            so.route                                    ruta,
            rp.name                                     cliente,
            rp.id                                       cliente_id,
            CONCAT(
                '[',so.partner_shipping_id,'] ',
                determinantes.name
            )                                           determinante,
            geo_pais.name                               determinante_pais,
            geo_estado.name                             determinante_estado,
            determinantes.city                          determinante_municipio,
            so.partner_shipping_id                      determinante_id,
            fact.move_name                              factura,
            sml.qty_done                                cantidad,
            (sml.qty_done*(1/uom.factor))               litros
            
        FROM
            stock_move_line sml 
            INNER JOIN
            stock_move sm 
            ON sml.move_id = sm.id 
            INNER JOIN
            stock_picking sp 
            ON sml.picking_id = sp.id 
            LEFT JOIN
            sale_order_line sol 
            ON sm.sale_line_id = sol.id 
            AND sp.sale_id = sol.order_id 
            INNER JOIN
            sale_order so 
            ON sol.order_id = so.id 
            LEFT JOIN
            product_product pp 
            ON sml.product_id = pp.id 
            INNER JOIN
            product_template pt 
            ON pp.product_tmpl_id = pt.id 
            INNER JOIN uom_uom uom ON sml.product_uom_id=uom.id
            LEFT JOIN res_partner rp ON so.partner_id=rp.id
            LEFT JOIN res_partner determinantes ON so.partner_shipping_id=determinantes.id
            LEFT JOIN base_plant bp ON so.plant_id=bp.id
            LEFT JOIN res_partner plantas ON bp.partner_id=plantas.id
            LEFT JOIN res_country geo_pais ON determinantes.country_id=geo_pais.id
            LEFT JOIN res_country_state geo_estado ON determinantes.state_id=geo_estado.id
            LEFT JOIN 
                (SELECT 
                    solir.order_line_id,
                    aml.move_name,
                    to_char(aml.create_date - interval '5 hour','YYYY-MM-DD') fecha_factura
                FROM    
                    account_move_line aml
                    INNER JOIN 
                    sale_order_line_invoice_rel solir
                    ON solir.invoice_line_id=aml.id
                WHERE
                    aml.parent_state='posted'
                    AND aml.price_subtotal>0
                ) fact
            ON sol.id=fact.order_line_id
        WHERE 
            sp.state <> 'cancel' 
            AND sm.state='done'
            AND sml.done_move='t' 
            /* AND sp.date_done>= '$fechaUnMesAtras 00:00:00' */
            AND (so.route<>'')
            AND rp.id IN ('4004')
            ORDER BY movimiento_fecha desc, ruta desc LIMIT 900
SQL;
    $pg = pg_connect(dataconn("odoozar"));
    pg_set_client_encoding($pg, "LATIN");
    $result = pg_query($pg, $sql);
    // $respuesta["sql"]=$sql;
    $respuesta["error"] = pg_last_error($pg);
    $respuesta["error"] .= pg_result_error($result);

    if (strlen($respuesta["error"]) > 0) {
        $respuesa["status"] = 0;
    } else {
        $respuesta["status"] = 1;
        $box = "";
        while ($row = pg_fetch_assoc($result)) {
            $respuesta["datos"][]=$row;
            $classDocs = "";
            $classCerts = "";
            $classDocs = "";
            $classDocs = "";
            $inRaloy = "";
            $info_val = "";
            if (array_key_exists($row["movimiento_id"], $insRaloy)) {
                $inRaloy = $insRaloy[$row["movimiento_id"]]["aptin"];
                $info_val = $insRaloy[$row["movimiento_id"]]["user"] . "<br/>" . $insRaloy[$row["movimiento_id"]]["date_val"];
            }
            $box .= "<tr class='tr" . $row["ruta"] . "'>";
            $box .= "<td class='text-bold'>" . $row["ruta"] . "</td>";
            $box .= "<td>" . utf8_encode($row["planta"]) . "</td>";
            $box .= "<td>" . $row["movimiento_fecha"] . "</td>";
            $box .= "<td>" . $row["producto_clave"] . " " . $row["producto_nombre_variante"] . "</td>";
            $box .= "<td>" . $row["pedido"] . "</td>";
            $box .= "<td>" . $row["remision"] . "</td>";
            $box .= "<td>" . $row["factura"] . "</td>";
            $box .= "<td>" . $row["determinante"] . ":" . $row["determinante_municipio"] . "," . $row["determinante_estado"] . "</td>";
            $box .= "<td>" . number_format($row["litros"], 2) . " L</td>";
            $folderColorClass=checkFolder($row["ruta"], $row["remision"]);
            $box .= "<td><button remision='" . $row["remision"] . "' litros='".number_format($row["litros"], 2)."' pedido='".$row["pedido"]."' ruta='" . $row["ruta"] . "' class='btn' type='button' data-bs-target='#modal-doctos' data-bs-toggle='modal'><i class='fa fa-folder $folderColorClass'></i></button></td>";
            // $box .= "<td class='folderDoctos' style='color:#000;text-align:center;font-size:calc(0.8em + 0.8vh)'><i class='fa fa-folder $classDocs'></i></td>";
            $permisos=explode(",",$_SESSION["sessionInfo"]["strIdsPerms"]);
            $enabled = (strlen($inRaloy) > 0 || !in_array(11,$permisos)) ? "disabled='disabled'" : "";
            if(strlen($inRaloy) > 0){
                $box .= "<td class='text-apt-in'>$inRaloy</td>";
                $box .= "<td class='valAMP-info' style='font-size:9px;'>" . $info_val . "</td>";
            }else{
                $box .= "<td class='valAMP'><input $enabled class='form-control form-control-sm input-in-raloy input-move-" . $row["movimiento_id"] . "' liters='" . $row["litros"] . "' route='" . $row["ruta"] . "' move-id='" . $row["movimiento_id"] . "' apt-out-zk='" . $row["remision"] . "' invoice-zk='" . $row["factura"] . "'   type='text' class='form-control' value='$inRaloy' placeholder='APT/IN RALOY'/></td>";
                $box .= "<td class='valAMP-info' style='font-size:9px;'>Sin Recibir</td>";
            }
            
            $box .= "</tr>";
        }
        $respuesta["boxes"] = $box;
    }

    pg_close($pg);
    return json_encode($respuesta);
}
function checkFolder($ruta, $remision)
{
    $folder_1 = "../../../uploads/ruta_" . $ruta;
    
    $folderColorClass="text-secondary";
    if(is_dir($folder_1)){
        $folder_2=$folder_1."/".str_replace("/","_",$remision);
        $filecount = count(glob($folder_2 . "*"));
        if(is_dir($folder_2) && $filecount>0){
            $folderColorClass= "text-warning";
        }
    }
    return $folderColorClass;
}
function get_aptins_raloy()
{
    $sql = <<<SQL
        SELECT 
            move_id,
            apt_in_raloy,
            u.nombre,
            datetime_val_amp 
        FROM
            smartRoad_routes_aptin_raloy_odoo_rel today 
            LEFT JOIN
            framework_usuarios u 
            ON today.intranet_user_id = u.id_usuario 
SQL;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        return array("status" => 0, "Connect error" => $mysqli->connect_error);
    }
    $result = $mysqli->query($sql);
    if ($mysqli->errno) {
        return array("status" => 0, "error" => $mysqli->error);
    }
    $respuesta["status"] = 1;
    while ($row = $result->fetch_assoc()) {
        $respuesta["data"][$row["move_id"]] = array("aptin" => $row["apt_in_raloy"], "user" => $row["nombre"], "date_val" => $row["datetime_val_amp"]);
    }
    return $respuesta;
}
function apt_in_exists($apt_in)
{

    $sql = <<<SQL
    SELECT move_id FROM smartRoad_routes_aptin_raloy_odoo_rel WHERE apt_in_raloy="$apt_in"
SQL;

    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);

    $result = $mysqli->query($sql);

    if ($result->num_rows == 0) {
        return -1;
    }

    $mysqli->close();
}
function save_aptin_raloy()
{

    $moveId = $_GET["move_id"];
    $route = $_GET["route"];
    $aptin = $_GET["aptin"];
    $aptoutZk = $_GET["aptout_zk"];
    $invoiceZk = $_GET["invoice_zk"];
    $liters = $_GET["liters"];
    $userId = $_SESSION["sessionInfo"]["userSession"];
    $date = date("Y-m-d H:i:s");

    if (apt_in_exists($aptin) != -1) {
        return json_encode(array("status" => -1, "error" => "El albarán ya existe"));
    };

    $sql = <<<SQL
        INSERT INTO smartRoad_routes_aptin_raloy_odoo_rel
            (move_id,route,apt_in_raloy,apt_out_zk,invoice_zk,liters,intranet_user_id,datetime_val_amp) 
        VALUES
            ("$moveId","$route","$aptin","$aptoutZk","$invoiceZk","$liters","$userId","$date")
        ON DUPLICATE KEY UPDATE route="$route",apt_in_raloy="$aptin",apt_out_zk="$aptoutZk",intranet_user_id="$userId",datetime_val_amp="$date"
SQL;

    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        return json_encode(array("status" => 0, "Connect error" => $mysqli->connect_error));
    }
    if ($mysqli->query($sql)) {
        $mysqli->close();
        return json_encode(array("status" => 1));
    } else {
        $error = $mysqli->error;
        $mysqli->close();
        return json_encode(array("status" => 0, "sql error" => $error));
    }
}
function detallesBox($id_entrega, $mysqli, $stat)
{
    $query = <<<SQL
            SELECT 
                r.eta,
                LEFT(em.mpio, 17) mpio,
                em.edoCor edo,
                r.cveProducto cve,
                r.pedido ped,
                LEFT(r.cliente,5) cliente,
                SUM(r.ltsSurtir) lts,
                GROUP_CONCAT(IFNULL(statusCargaZK,0)) statCarga,
                r.fuente_pedido
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
    $mysqli->set_charset("utf8");
    $result = $mysqli->query($query);
    $spans = [];
    $stats = [];
    $suma = 0;
    $convertFuente = array("Odoo Raloy" => array("label" => "RAL", "color" => "#1a509c"), "SCP ZK" => array("label" => "ZAR", "color" => "#13AC61"));
    while ($row = $result->fetch_assoc()) {
        $eta = $row["eta"];
        $lts = $row["cve"] . " " . number_format($row["lts"], 2) . " L";
        $mpio = $row["mpio"];
        $edo = $row["edo"];
        $fuente = (array_key_exists($row["fuente_pedido"], $convertFuente)) ? "<tag style='color:" . $convertFuente[$row["fuente_pedido"]]["color"] . "'>" . $convertFuente[$row["fuente_pedido"]]["label"] . "</tag>" : substr($row["fuente_pedido"], 0, 2);
        $cliente = $row["cliente"];
        $spans[] = "<span style='display:block;width:100%;'>" . $fuente . " " . $lts . " (" . $cliente . " " . $mpio . "," . $edo . " " . $eta . ")</span>";
        $stats = array_merge(explode(",", $row["statCarga"]), $stats);
        $suma += $row["lts"];
    }
    $detalles = implode("", $spans);
    $respuesta["status"] = array_unique($stats);
    $respuesta["detalles"] = $detalles;
    $respuesta["suma"] = $suma;
    return $respuesta;
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

function dimeCertificados()
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
        r.sellosDescarga sellosD,
        r.sellosEscotilla sellosE,
        r.remisionZK rems,
        r.numOCaZK ocs,
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
    $dataconn = dataconn("intranet");
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
            $inputlept = "<input type='text' class='lept' lts_a_surtir='" . $row["lt"] . "' value='" . $row["loteept"] . "'/>";
            $inputlpt = "<input  type='text' class='lpt' value='" . $row["lotept"] . "'/>";
            $inputsellosE = "<input  type='text' class='sellosE' value='" . $row["sellosE"] . "'/>";
            $inputsellosD = "<input  type='text' class='sellosD' value='" . $row["sellosD"] . "'/>";
            $inputremision = "<input  type='text' class='rems' value='" . $row["rems"] . "'/>";
            $inputocs = "<input  type='text' readonly='readonly' class='ocs' value='" . $row["ocs"] . "'/>";
            //            $btnvalidar = "<button class='validarCert' iprs='" . $iprs . "'><i class='fa fa-save'></i></button>";
            if ($row["statusCargaZK"] == 1) {
                $getcert = "<i class='fa fa-file-alt cert' style='color:#4ca64c' folio='$folio' iprs='$iprs'></i>";
            } else {
                $getcert = "<i class='fa fa-square-full' style='color:#f2f2f2'></i>";
            }
            //            $trs .= "<tr class='editableRow' iprs='$iprs' folio='$folio'><td>" . $texttoshow . "</td><td>" . $inputlept . "</td><td>" . $inputlpt . "</td><td>" . $inputsellosE . "</td><td>" . $inputsellosD . "</td><td>" . $inputremision . "</td><td>" . $btnvalidar . "</td><td class='certCtr'>$getcert</td></tr>";
            $trs .= "<tr class='editableRow' iprs='$iprs' folio='$folio'><td>" . $texttoshow . "</td><td>" . $inputlept . "</td><td>" . $inputlpt . "</td><td>" . $inputsellosE . "</td><td>" . $inputsellosD . "</td><td>" . $inputremision . "</td><td>" . $inputocs . "</td><td class='certCtr'>$getcert</td></tr>";
        }
        $respuesta["trs"] = $trs;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        exit(json_encode($respuesta));
    }
    return json_encode($respuesta);
}

function save_all()
{
    $lept = $_GET["lept"];
    $lpt = $_GET["lpt"];
    $sellosE = $_GET["sellosE"];
    $sellosD = $_GET["sellosD"];
    $rems = $_GET["rems"];
    $iprs = $_GET["iprs"];


    $checkLab = save_checkLab($lept);
    $checkSave = save_saveData($lept, $lpt, $sellosE, $sellosD, $rems, $iprs, $checkLab["status"]);
    $respuesta["status"]["datos"] = $checkSave["status"];
    $respuesta["error"]["datos"] = $checkSave["error"];

    if ($checkLab["status"] == 1) {
        $results = $checkLab["resultados"];
        save_saveDataResultsLab($iprs, $results["1093"], $results["1032"], $results["1080"], "Id&eacute;ntico a Referencia");
        $respuesta["status"]["certificado"] = $checkSave["status"];
        $respuesta["error"]["certificado"] = $checkSave["error"];
    } else {
        $respuesta["status"]["certificado"] = $checkLab["status"];
        $respuesta["error"]["certificado"] = $checkLab["error"];
    }
    return json_encode($respuesta);
}
function validate_tank_capacity()
{
    $loteEPT = $_GET["lept"];
    $lts_a_surtir = $_GET["lts_a_surtir"];
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $plant = substr($loteEPT, 6, 1);
    $tank = substr($loteEPT, 7, 1);
    $res = $mysqli->query("SELECT planta from smartRoad_plantas WHERE nomenc_lab ='$plant'");
    $row = $res->fetch_assoc();
    $res2 = $mysqli->query("SELECT capacidad_l FROM today_tanques WHERE numTanquePlanta=$tank AND planta='" . $row["planta"] . "'");
    $row = $res2->fetch_assoc();
    $capacidad = ($row["capacidad_l"] > 0) ? $row["capacidad_l"] : 0;
    $res3 = $mysqli->query("SELECT SUM(ltsSurtir) sumaLitros FROM smartRoad_pre_ruteo WHERE loteEPT='$loteEPT'");
    $row = $res3->fetch_assoc();
    $lts_surtidos_lote = ($row["sumaLitros"] > 0) ? $row["sumaLitros"] : 0;
    $mysqli->close();
    $total_lts = $lts_a_surtir + $lts_surtidos_lote;
    $alert = ($total_lts > $capacidad) ? "EXCESO" : "OK";
    return json_encode(array("capacidad" => number_format($capacidad), "lts_surtidos" => number_format($lts_surtidos_lote), "alert" => $alert));
}

function save_checkLab($loteEPT)
{
    if (!isset($_SESSION["parametros"]["liberacionlotes"]) || $_SESSION["parametros"]["liberacionlotes"] <= 0) {
        $respuesta["error"] = "Error al validar sus permisos. Por favor cierre sesión y vuelva a ingresar";
        $respuesta["status"] = 2;
        return $respuesta;
    }
    $querypruebasachecar = "SELECT p.Pid FROM merit_formatosCert f INNER JOIN merit_referenciaISO p ON f.id_prueba=p.Pid WHERE f.id_formato_cert=" . $_SESSION["parametros"]["liberacionlotes"];
    $dataconn = dataconn("intranet");
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
    $dataconn = dataconn("laboratorio");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($qCheckLab)) {
        if ($result->num_rows <= 0) {
            $respuesta["status"] = 2;
            $respuesta["error"] = "NO ES POSIBLE EMITIR EL CERTIFICADO\nEl lote especificado no ha sido liberado o está fuera de conformidad.\nLos datos no han sido guardados. " . $qCheckLab;
            $mysqli->close();
            return $respuesta;
        }
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
            $respuesta["error"] = "NO ES POSIBLE EMITIR EL CERTIFICADO\nEl lote especificado no ha sido liberado o está fuera de conformidad.\nLos datos no han sido guardados." . $qCheckLab;
        }
    } else {
        $respuesta["error"] = "lab: " . $mysqli->error;
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
function getAllOCs()
{
    $conn_scp = dataconn("scpzar");
    $dataconn = dataconn("intranet");
    $folio1 = $_GET["f1"];
    $folio2 = $_GET["f2"];
    $sql = "Select DISTINCT(remisionZK) remision FROM smartRoad_pre_ruteo WHERE remisionZK <>'' AND id_entrega>=$folio1 and id_entrega<=$folio2 ORDER BY remisionZK";
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $result_rems = $mysqli->query($sql);
    $mysqli->close();
    while ($remisiones = $result_rems->fetch_assoc()) {
        $rem = $remisiones["remision"];
        $mysqli = new mysqli($conn_scp["host"], $conn_scp["user"], $conn_scp["pass"], $conn_scp["db"], $conn_scp["port"]);
        $sql = "select GROUP_CONCAT(DISTINCT CONCAT(NumRemi,'@',Pedido)) ocs FROM FRemision WHERE numRemi in ($rem) GROUP BY NumRemi,Pedido";
        $result = $mysqli->query($sql);
        $mysqli->close();
        $row = $result->fetch_assoc();
        $ocs = $row["ocs"];
        $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
        $sql = "UPDATE smartRoad_pre_ruteo SET numOCaZK='$ocs' WHERE remisionZK=$rem";
        $mysqli->query($sql);
        $mysqli->close();
    }
    return json_encode(array("Terminado"));
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
        move_uploaded_file($_FILES['file']['tmp_name'], 'https://suministroconfiable.com/intranet/uploads/today/' . $_FILES['file']['name']);
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
    $id_usuario = $_SESSION["sessionInfo"]["userSession"];
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

function kSA()
{
    //Keep Session Alive
    $rand = rand(1, 15000);
    if (array_key_exists("rand", $_SESSION)) {
        if ($_SESSION["rand"] == $rand) {
            $rand = $rand + 1;
        }
    }
    $_SESSION["rand"] = $rand;
    $respuesta["status"] = 1;
    $respuesta["rand"] = $rand;

    return json_encode($respuesta);
}

function changeStatus()
{
    $folio = $_GET["folio"];
    $sql_check = "
                SELECT 
                    IF(
                        SUM(pr.ltsSurtir) > 0,
                        validacionAMP,
                        1
                    ) valido
                FROM
                    smartRoad_entregas e 
                    LEFT JOIN
                    smartRoad_pre_ruteo pr 
                    ON e.id_entrega = pr.id_entrega 
                WHERE e.id_entrega = 8270 
                    AND pr.fuente_pedido = 'odooRaloy'";

    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($result = $mysqli->query($sql_check)) {
        $validation = $result->fetch_assoc();
        if ($validation["valido"] == 1) {
            $sql = "UPDATE smartRoad_entregas SET status='cargado' WHERE id_entrega=$folio and status='carga' AND validacionAMP=1";
            $sql2 = "UPDATE smartRoad_pre_ruteo SET status='cargado' WHERE id_entrega=$folio and status='carga'";
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
        } else {
            $respuesta["status"] = 0;
            // $respuesta["error"] = "ERROR " . $e;
        }
    }

    $mysqli->close();
    return json_encode($respuesta);
}
