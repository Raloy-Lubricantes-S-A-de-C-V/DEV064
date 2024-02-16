<?php

//$conn = mysql_connect("dbp.raloy.com.mx", "hyescas", "dub+but");
//$conn2 = mysql_connect("dbp.raloy.com.mx:3385", "adblue", "Veoos133");
$connRaloy = array("server" => "34.68.173.102", "user" => "hyescas", "password" => "dub+but", "db" => "scp9000", "port" => "3306");
$connSCPZK = array("server" => "35.238.27.108", "user" => "adblue", "password" => "Veoos133", "db" => "adblue_scp_dump", "port" => "3306");
$connIntranetZK = array("server" => "www.zar-kruse.com", "user" => "zarkruse_intrane", "password" => "Totich182308", "db" => "zarkruse_intranet", "port" => "3306");
$conns = array("Raloy" => $connRaloy, "zk" => $connSCPZK, "intranet" => $connIntranetZK);

function dataconn($source)
{
    $dataconn = array(
        "laboratorio" => array(
            "host" => "201.163.122.33",
            "user" => "laboratorio",
            "pass" => "salamalecum",
            "db" => "laboratorio",
            "port" => "56092"
        ),
        "scpzar" => array(
            "host" => "35.239.187.187",
            "user" => "zarkruse",
            "pass" => "Totich182308",
            "db" => "zk_zcp",
            "port" => "3306"
        ),
        "intranet" => array(
            "host" => "localhost",
            "user" => "root",
            "pass" => "",
            "db" => "zk_today",
            "port" => "3306"
        ),
        "competitividad" => array(
            "host" => "35.239.187.187",
            "user" => "zarkruse",
            "pass" => "Totich182308",
            "db" => "panjiva",
            "port" => "3306"
        ),
        "odoozar" => "
            host = 70.35.200.186 
            user =  zar-kruse
            password = NdRrsLr4yUvb
            dbname = Zarkruse_V13_Productivo_R2 
            port = 8069
            ",
        "odooRaloyProductivo" => "
            host = odoo.raloy.com.mx    
            user = apps 
            password = JrKFtfoUwydjVGC8 
            dbname = Zarkruse_V13_Productivo_R2 
            port = 5433
            ",
        "odooRaloyPruebas" => "
            host = odoo-pruebas.raloy.com.mx    
            user = odoo  
            password = 0d00r4loY 
            dbname = _20221010_raloy_productivo 
            port = 5432 
            "
    );
    //interna
    // "host": "10.150.4.90",
    // "port": "5432",
    //externas
    //201.163.122.33 //primario
    //201.132.104.114 //secundario
    //raloy-santiago-hjmkczptpq.dynamic-m.com //dns
    return $dataconn[$source];
}

function pg_query_result($strConn,$sql)
{
    $pg = pg_connect(dataconn($strConn));
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
function mariadb_query_result($strConn, $sql)
{
    $connData = dataconn($strConn);
    $mysqli = new mysqli($connData["host"], $connData["user"], $connData["pass"], $connData["db"], $connData["port"]);

    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        die(json_encode($respuesta));
    }

    $mysqli->set_charset("uf8");

    $result = $mysqli->query($sql);
    if ($mysqli->error) {
        $mysqli->close();
        return json_encode(array("status" => 0, "error" => $mysqli->error));
    }

    $respuesta["status"] = 1;
    $respuesta["numRows"] = $result->num_rows;
    $respuesta["data"] = [];

    while ($row = $result->fetch_assoc()) {
        $respuesta["data"][] = $row;
    }

    $mysqli->close();

    return $respuesta;
}
function file_read_json($file)
{
    $respuesta["status"] = 1;
    $ctx = stream_context_create(array(
        'http' =>
        array(
            'timeout' => 1200,  //1200 Seconds is 20 Minutes
        )
    ));
    $respuesta["eTime"]["start"] = date("Y-m-d H:i:s");
    if (!$array = file($file, false, $ctx)) {
        $respuesta["status"] = 0;
        $respuesta["error"] = "Host Desconocido";
        return $respuesta;
    }
    $respuesta["data"] = json_decode($array[0], True);
    $respuesta["eTime"]["end"] = date("Y-m-d H:i:s");
    $elapsedTime = (array) date_diff(date_create($respuesta["eTime"]["end"]), date_create($respuesta["eTime"]["start"]));
    $respuesta["eTime"]["took"] = $elapsedTime["h"] . "h:" . $elapsedTime["i"] . "m:" . $elapsedTime["s"] . "s";
    return $respuesta;
}