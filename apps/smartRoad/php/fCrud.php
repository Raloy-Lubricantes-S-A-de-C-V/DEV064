<?php

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
require_once("../../../php/conexion.php");
$fase = $_GET["fase"];
$response = call_user_func($fase);
echo $response;

function tablaAdminFlota() {
    $query = <<<SQL
          SELECT * FROM smartRoad_flota
SQL;
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }
        //CON DATOS
        if ($result->num_rows > 0) {
            //Tabla datos
            $tbody = "";
            $tbody .= "<tr>";
            $tbody .= "<td></td>";
            $tbody .= "<td><input type='text' value=''/></td>";
            $tbody .= "<td><input class='numeric' type='text' value=''/></td>";
            $tbody .= "<td><input type='text' value=''/></td>";
            $tbody .= "<td><input class='numeric' type='text' value=''/></td>";
            $tbody .= "<td><input type='text' value=''/></td>";
            $tbody .= "<td><input type='text' value=''/></td>";
            $tbody .= "<td><input type='text' value=''/></td>";
            $tbody .= "<td><input type='text' class='inputFecha' value=''/></td>";
            $tbody .= "<td><input type='text' class='inputFecha' value=''/></td>";
            $tbody .= "<td><input type='text' class='inputFecha' value=''/></td>";
            $tbody .= "<td><input type='text' class='inputFecha' value=''/></td>";
            $tbody .= "<td><input class='numeric' type='text' value='1'/></td>";
            $tbody .= "<td><input type='text' value=''/></td>";
            $tbody .= "<td><input class='numeric' type='text' value=''/></td>";
            $tbody .= "<td><input class='numeric' type='text' value=''/></td>";
            $tbody .= "<td><input type='text' value=''/></td>";
            $tbody .= "</tr>";
            while ($row = $result->fetch_assoc()) {
                $tbody .= "<tr>";
                $tbody .= "<td>" . $row["id_autotanque"] . " <i class='delAtq fa fa-trash' idAtq='" . $row["id_autotanque"] . "'></i></td>";
                $tbody .= "<td><input type='text' readonly='readonly' value='" . $row["placas"] . "'/></td>";
                $tbody .= "<td><input class='numeric' type='text' value='" . $row["capacidad"] . "'/></td>";
                $tbody .= "<td><input type='text'  value='" . $row["sellosFijos"] . "'/></td>";
                $tbody .= "<td><input class='numeric' type='text' value='" . $row["numEjes"] . "'/></td>";
                $tbody .= "<td><input type='text' value='" . $row["propia_externa"] . "'/></td>";
                $tbody .= "<td><input type='text' value='" . $row["propietario"] . "'/></td>";
                $tbody .= "<td><input type='text' value='" . $row["modelCtaLts"] . "'/></td>";
                $tbody .= "<td><input type='text' class='inputFecha' value='" . $row["ultVerif"] . "'/></td>";
                $tbody .= "<td><input type='text' class='inputFecha' value='" . $row["sigVerif"] . "'/></td>";
                $tbody .= "<td><input type='text' class='inputFecha' value='" . $row["ultCalib"] . "'/></td>";
                $tbody .= "<td><input type='text' class='inputFecha' value='" . $row["sigCalib"] . "'/></td>";
                $tbody .= "<td><input class='numeric' type='text' value='" . $row["statusUnidad"] . "'/></td>";
                $tbody .= "<td><input type='text' value='" . $row["carpetaDoctos"] . "'/></td>";
                $tbody .= "<td><input class='numeric' type='text' value='" . $row["costoDepMes"] . "'/></td>";
                $tbody .= "<td><input class='numeric' type='text' value='" . $row["costoOtrosFijosMes"] . "'/></td>";
                $tbody .= "<td><input type='text' value='" . $row["descOtrosFijos"] . "'/></td>";

                $tbody .= "</tr>";
            }
            $respuesta["tbody"] = $tbody;
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

function guardatablaAdminFlota() {
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $values = $_GET["strValues"];
    $query = <<<SQL
    INSERT INTO smartRoad_flota 
    (
        placas,capacidad,sellosFijos,numEjes,propia_externa,propietario,modelCtaLts,ultVerif,sigVerif,ultCalib,sigCalib,statusUnidad,carpetaDoctos,costoDepMes,costoOtrosFijosMes,descOtrosFijos
    ) 
    VALUES
        $values
    ON DUPLICATE KEY UPDATE 
        placas = VALUES(placas),
        capacidad = VALUES(capacidad),
        sellosFijos = VALUES(sellosFijos),
        numEjes = VALUES(numEjes),
        propia_externa = VALUES(propia_externa),
        propietario = VALUES(propietario),
        modelCtaLts = VALUES(modelCtaLts),
        ultVerif = VALUES(ultVerif),
        sigVerif = VALUES(sigVerif),
        ultCalib = VALUES(ultCalib),
        sigCalib = VALUES(sigCalib),
        statusUnidad = VALUES(statusUnidad),
        carpetaDoctos = VALUES(carpetaDoctos),
        costoDepMes = VALUES(costoDepMes),
        costoOtrosFijosMes = VALUES(costoOtrosFijosMes),
        descOtrosFijos = VALUES(descOtrosFijos);
SQL;
    $respuesta["q"] = $query;
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function borraAtq() {
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return $respuesta;
    }
    $idAtq = $_GET["idAtq"];
    $query = <<<SQL
    DELETE FROM smartRoad_flota where id_autotanque=$idAtq;
SQL;
    $respuesta["q"] = $query;
    if ($result = $mysqli->query($query)) {
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        ;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function tablaAdminDet() {
    $query = <<<SQL
        SELECT 
            id_determinante idunico,
            id_det_origen idDet,
            cliente_nombre NomCliente,
            destino Enviar,
            id_formato_cert formato_coa,
            capacidad_L capacidad,
            IFNULL(tanques_detalle,'') tanques,
            d.id_relEdoMpio idEM,
            em.edoCor,
            em.mpio,
            calle,
            colonia,
            d.estado edoOrig,
            d.ciudad mpioOrig
        FROM
            smartRoad_stdDet d
        LEFT JOIN 
            smartRoad_stdEdosMpios em
            ON d.id_relEdoMpio=em.id 
        ORDER BY 
            edoCor,
            mpio,
            NomCliente,
            Enviar,
            Capacidad
SQL;

    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $respuesta["status"] = 1;
    $respuesta["error"] = "";

    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }

    if ($result = $mysqli->query($query)) {
        if ($mysqli->errno) {
            $respuesta["status"] = 0;
            $respuesta["error"] = $mysqli->error;
            return json_encode($respuesta);
        }

        //CON DATOS
        if ($result->num_rows > 0) {
            //Tabla datos
            $tbody = "";
            $respuesta["status"] = 1;

            while ($row = $result->fetch_assoc()) {

                //Información del Estado y Municipio estándar
                $idunico = $row["idunico"];
                $idDet = $row["idDet"];
                $idEM = $row["idEM"];
                $edoCorEM = utf8_encode($row["edoCor"]);
                $mpioEM = utf8_encode($row["mpio"]);
                $edoCorOrig = utf8_encode($row["edoOrig"]);
                $mpioOrig = utf8_encode($row["mpioOrig"]);
                $tanques = utf8_encode($row["tanques"]);

                $tbody .= "<tr idunico='$idunico' idem='$idEM'>";
                $tbody .= "<td style='width:10%'>" . $idDet . "</td>";
                $tbody .= "<td  style='width:15%;'>" . utf8_encode($row["NomCliente"]) . "</td>";
                $tbody .= "<td style='width:15%;'>" . utf8_encode($row["Enviar"]) . "</td>";
                $tbody .= "<td><input type='text' size='1' class='inp txtformatocoa' value='" . $row["formato_coa"] . "'/></td>";
                $tbody .= "<td><input type='text' size='6' class='inp txtcapacity' value='" . $row["capacidad"] . "'/></td>";
                $tbody .= "<td><input type='text' size='10' class='inp txttanques' value='" . $tanques . "'/></td>";
                $tbody .= "<td><input type='text' readonly='readonly' class='idEM' value='" . $idEM . "'/></td>";
                $textoBotonEdos = ($edoCorEM == "" || $edoCorEM == null) ? "<i class='fa fa-pencil-alt'></i>" : utf8_encode($edoCorEM);
                $tbody .= "<td><button class='btnSelEdos' valor='" . $edoCorEM . "'>" . $textoBotonEdos . "</button></td>";
                $tbody .= "<td><select class='selMpios'><option selected='selected' value='" . $idEM . "'>" . $mpioEM . "</option></select></td>";
                $tbody .= "<td style='width:14%;'>" . $edoCorOrig . "</td>";
                $tbody .= "<td style='width:13%;'>" . $mpioOrig . "</td>";
                $tbody .= "<td style='width:15%;'>" . utf8_encode($row["calle"]) . "</td>";
                $tbody .= "<td style='width:15%;'>" . utf8_encode($row["colonia"]) . "</td>";
                $tbody .= "</tr>";
            }
            $respuesta["tbody"] = $tbody;
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

function guardatablaAdminDet() {
    $queries = [];
//    $lines = explode(";", $_GET["lines"]);
    $lines = $_GET["lines"];
    foreach ($lines as $valores) {
//        $arrValores = explode("@@", $valores);
        $arrValores = $valores;
        $idunico = $arrValores[0];
        $idEM = $arrValores[1];
        $capac = $arrValores[2];
        $tanques = $arrValores[3];
        $formatocoa = $arrValores[4];
        $queries[] = <<<SQL
            UPDATE smartRoad_stdDet SET id_relEdoMpio=$idEM,capacidad_L=$capac,tanques_detalle='$tanques',id_formato_cert=$formatocoa where id_determinante=$idunico
SQL;
    }
//    $respuesta["queries"]=$queries;
//    return json_encode($respuesta);
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
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

function selectEdos() {
    $query = "select edoCor from smartRoad_stdEdosMpios group by edoCor";

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
            if ($_GET["selected"] != "" && $_GET["selected"] != "undefined" && $row["edoCor"] == $_GET["selected"]) {
                $selected = "selected='selected' ";
            } else {
                $selected = "";
            }
            $options .= "<option $selected value='" . utf8_encode($row["edoCor"]) . "'>" . utf8_encode($row["edoCor"]) . "</option>";
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

function selectMpios() {
    $edo = $_GET["edo"];
    $query = "select id,mpio from smartRoad_stdEdosMpios where edoCor='$edo'";
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
            $options .= "<option value='" . $row["id"] . "'>" . utf8_encode($row["mpio"]) . "</option>";
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

function nom012() {
    $query = "select id_nom012 idtipov,clase,nomenclatura,numejes,numllantas,imgvehiculo,pbv_t_ETyA,pbv_t_B,pbv_t_C,pbv_t_D,lgm_m_ETyA,lgm_m_B,lgm_m_C,lgm_m_D from smartRoad_nom012";
    $dataconn = dataconn("intranet");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }
    $tbody = "";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $tbody .= "<tr idtipov='" . $row["idtipov"] . "' pbv_t_ETyA='" . $row["pbv_t_ETyA"] . "' pbv_t_B='" . $row["pbv_t_B"] . "' pbv_t_C='" . $row["pbv_t_C"] . "' pbv_t_D='" . $row["pbv_t_D"] . "' lgm_m_ETyA='" . $row["lgm_m_ETyA"] . "' lgm_m_B='" . $row["lgm_m_B"] . "' lgm_m_C='" . $row["lgm_m_C"] . "' lgm_m_D='" . $row["lgm_m_D"] . "'>";
            $tbody .= "<td><input type='radio' class='radiotipos'/></td>";
            $tbody .= "<td class='claseveh'>" . utf8_encode($row["clase"]) . "</td>";
            $tbody .= "<td>" . $row["nomenclatura"] . "</td>";
            $tbody .= "<td>" . $row["numejes"] . "</td>";
            $tbody .= "<td>" . $row["numllantas"] . "</td>";
            $tbody .= "<td><img src='../../img/nom012/" . $row["imgvehiculo"] . "'/></td>";
            $tbody .= "</tr>";
        }
        $respuesta["tbody"] = $tbody;
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}

function envasados_guardarRelacion() {
    $numRemi = $_GET["numRemi"];
    $cartaPorte = $_GET["cartaPorte"];
    $datetime = date("Y-m-d H:i:s");
    $query = "INSERT INTO zk_cartaPorte_envasados (numRemi,folioCartaPorte,fechCaptura,usuarioCaptura) VALUES($numRemi,$cartaPorte,'$datetime','" . utf8_decode($_SESSION["sessionInfo"]["user"]) . "')";
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Error de conexión " . $mysqli->connect_error;
        return json_encode($respuesta);
    }
    $tbody = "";
    if (!$mysqli->query($query)) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        $mysqli->close();
        return json_encode($respuesta);
    } else {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
        $mysqli->close();
        return json_encode($respuesta);
    }
}

function envasados_cargarTabla() {
    $query = "SELECT r.numRemi,r.fechElabo,GROUP_CONCAT(CONCAT(r.producto,' ',r.Acabado) SEPARATOR ';') prods,e.folioCartaPorte,e.fechCaptura,e.usuarioCaptura FROM zk_cartaPorte_envasados e LEFT JOIN FRemision r ON e.numRemi=r.numRemi  WHERE YEAR(r.fechElabo)>=2020 GROUP BY r.numRemi ORDER BY e.folioCartaPorte DESC,r.fechElabo DESC";
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }
    $tbody = "";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $tbody .= "<tr>";
            $tbody .= "<td>" . $row["fechElabo"] . "</td>";
            $tbody .= "<td>" . $row["numRemi"] . "</td>";
            $tbody .= "<td>" . utf8_encode($row["prods"]) . "</td>";
            $tbody .= "<td>" . $row["folioCartaPorte"] . "</td>";
            $tbody .= "<td>" . $row["fechCaptura"] . "</td>";
            $tbody .= "<td>" . utf8_encode($row["usuarioCaptura"]) . "</td>";
            $tbody .= "</tr>";
        }
        $respuesta["tbody"] = $tbody;
        $respuesta["status"] = 1;
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
    }
    $mysqli->close();
    return json_encode($respuesta);
}
