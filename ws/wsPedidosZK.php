<?php
date_default_timezone_set('America/Mexico_City');
require_once("../php/conexion.php");

echo json_encode(getData());

function getData()
{
    $respuesta = [];
    $respuesta["status"] = 0;
    $respuesta["error"] = "Error desconocido";
    if (!password_verify("ZarKruse2021", $_GET["t"])) {
        $respuesta["error"] = "No autorizado";
        return $respuesta;
    }

    // echo password_hash("ZarKruse2021",PASSWORD_DEFAULT);
    // return;
    $queryLogPedidos = <<<SQL
            SELECT 
    CONCAT(c.CveCliente, "-", p.NumPedido) soid,
    p.NumRemi pedido,
    p.FechElabo create_date,
    DATE(p.FehcEntre) commitment_date2,
    CONCAT(c.CveCliente, "@@", Enviar) id_destino,
    e.nombre destino,
    IFNULL(e.Pais, c.PaisCliente) estado,
    IFNULL(e.Ciudad, c.CiudadCliente) ciudad,
    IFNULL(e.Direccion, c.DirCliente) calle,
    IFNULL(e.Colonia, c.Colonia) colonia,
    Observ2 obs,
    c.CveCliente id_cliente,
    c.CveCliente cliente,
    c.NomCliente cliente_nombre,
    CONCAT(p.Producto," ",p.Acabado) clave,
    pt.PTDesc descripcion,
    SUM(p.CantiOrden - p.CantiDada)*CDV litros,
    p.NumRemi albaran,
    p.Autoriza estado_remision,
    p.Usuario ventas,
    "SCP ZK" AS fuente 
FROM
    FPedidos p 
    INNER JOIN
    FClientes c 
    ON p.Cliente = c.CveCliente 
    INNER JOIN
    FClienteEnvio e 
    ON p.Cliente = e.Cliente 
    AND p.Enviar = e.Determinante 
    INNER JOIN
    InvProdTerm pt 
    ON p.Producto = pt.PTNumArticulo AND p.Acabado=pt.PTTipo
WHERE fechElabo >= "2019-01-01" 
    AND p.cantiDada < p.cantiOrden 
    AND c.cveCliente <> 1 
    AND pt.PTCatalogo = "SKYBLUE" 
GROUP BY soid,
    p.NumRemi,
    p.Cliente,
    p.Enviar,
    p.Producto
SQL;

    $query = <<<SQL
            SELECT 
    CONCAT(c.CveCliente, "@@", Enviar) id_det_origen,
    c.CveCliente,
    c.NomCliente,
    e.Determinante,
    e.Nombre,
    IFNULL(e.Direccion, c.DirCliente) calle,
    IFNULL(e.Colonia, c.Colonia) col,
    IFNULL(e.Ciudad, c.CiudadCliente) ciudad,
    IFNULL(e.Pais, c.PaisCliente) edo,
    IFNULL(e.CP, c.CPCliente) cp,
    "SCP ZK" AS fuenteDatos 
FROM
    FPedidos p 
    INNER JOIN
    FClientes c 
    ON p.Cliente = c.CveCliente 
    INNER JOIN
    FClienteEnvio e 
    ON p.Cliente = e.Cliente 
    AND p.Enviar = e.Determinante 
WHERE fechElabo >= "2019-01-01" 
GROUP BY id_det_origen,
    c.CveCliente,e.Determinante,
    edo,
    ciudad 
SQL;
    $queries = [];
    
    $dataconn = dataconn("scpzar");
    $mysqli = new mysqli($dataconn["host"], $dataconn["user"], $dataconn["pass"], $dataconn["db"], $dataconn["port"]);
    $mysqli->set_charset("utf8");


    if ($mysqli->connect_errno) {
        $respuesta["error"] = "Falló conexión a Base de Datos: " . $mysqli->connect_errno;
        return $respuesta;
    }

    $result = $mysqli->query($queryLogPedidos);
    if ($mysqli->error) {
        $respuesta["error"] = "Falló conexión a Base de Datos: " . $mysqli->error;
        return $respuesta;
    }

    $fechaHrLog = date("Y-m-d H:i:s");
    $columns = "";
    if ($result->num_rows > 0) {
        $respuesta["status"] = 1;
        $respuesta["error"] = "";
        $fieldnames = [];
        foreach ($result->fetch_fields() as $field) {
            $respuesta["fields"][] = $field->name;
        }
        while ($row = $result->fetch_assoc()) {
            $respuesta["data"][] = $row;
        }
    }
    $result->free();
    $mysqli->close();
    return $respuesta;
}
