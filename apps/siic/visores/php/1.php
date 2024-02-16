<?php

    function repcomp(){
//         //echo "entro a query";
//         echo "string";
        $fec1 = $_GET["fec1"];
        $fec2 = $_GET["fec2"];

        $query =<<<SQL
           Select NAME_MX,  piers.IRS_MX, piers.STATE_MX, SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615)) as Litros, SUM(USD) AS USD, SUM(USD)/(SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615))) as USDxLt, con.Presentacion, piers.`NAME` AS Name, cd.Convertido,  piers.CITY_STATE, piers.ORIGIN_DESTINY,piers.TRANSPORT,
                 piers.CUSTOM_PORT_STATE, piers.CUSTOM, piers.COMER_UNIT, con.NumConvertido, piers.DESCRIPTION_8DIGITS,
                    SUM(piers.COMER_QTY) AS COMER_QTY, SUM(piers.QUANTITY) AS QUANTITY, CONCAT(MONTH, '-', YEAR) as Fecha
from piers LEFT JOIN ConverDesc_nvo AS cd ON piers.DESCRIPTION_8DIGITS = cd.CveReal
           LEFT JOIN Conver_nvo AS con ON piers.DESCRIPTION_8DIGITS = con.CveReal
where HSCODE_8DIGITS='31021001'
AND cd.Convertido="DEF"
AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2'
GROUP BY NAME_MX, piers.CUSTOM_PORT_STATE, piers.CUSTOM, piers.DESCRIPTION_8DIGITS, piers.IRS_MX, piers.STATE_MX, piers.`NAME`, piers.ORIGIN_DESTINY, piers.CITY_STATE, piers.TRANSPORT, piers.COMER_UNIT
ORDER BY NAME_MX, con.Presentacion
SQL;

        mysql_select_db("bopi");
        $result = mysql_query($query) or die(mysql_error());

        while ($rs = mysql_fetch_assoc($result)){
            $renglones[] = array(
                    "Competidor" => utf8_encode($rs[NAME_MX]),
                    "IRS" => utf8_encode($rs[IRS_MX]),
                    "Estado" => utf8_encode($rs[STATE_MX]),
                    "Litros_Totales" => number_format($rs[Litros],2),
                    "Costo" => number_format($rs[USD], 2),
                    "USDxLt" => number_format($rs[USDxLt], 3),
                    "Presentacion" => utf8_encode($rs[Presentacion]),
                    "Name" => utf8_encode($rs[Name]),
                    "Convertido" => utf8_encode($rs[Convertido]),
                    "CITY_STATE" => utf8_encode($rs[CITY_STATE]),
                    "ORIGIN_DESTINY" => utf8_encode($rs[ORIGIN_DESTINY]),
                    "Transporte" => utf8_encode($rs[TRANSPORT]),
                    "CUSTOM_PORT_STATE" => utf8_encode($rs[CUSTOM_PORT_STATE]),
                    "CUSTOM" => utf8_encode($rs[CUSTOM]),
                    "COMER_UNIT" => utf8_encode($rs[COMER_UNIT]),
                    "CDV" => number_format($rs[NumConvertido],4),
                    "Descripcion" => utf8_encode($rs[DESCRIPTION_8DIGITS]),
                    "Cantidad" => number_format($rs[COMER_QTY], 2),
                    "Cantidad_kg" => number_format($rs[QUANTITY], 2),
                    "Fecha" => $rs[Fecha]
                );
        }
        return json_encode($renglones);
        // return $query;
}

function prod_Cambia(){
    $desc = $_GET["desc"];
    $ant = $_GET["ant"];
    $value = $_GET["valor"];

    mysql_select_db("bopi");


        $query =<<<SQL
           UPDATE ConverDesc_nvo
           SET Convertido='$valor'
           WHERE CveReal='$desc'

SQL;

mysql_query($query) or die(mysql_error());

    $response = "";

    if (mysql_affected_rows() > 0) {
        # code...
        $response = 'CAMBIO CAMPO';
    }else{



            $query2 =<<<SQL
            INSERT INTO ConverDesc_nvo
            VALUES ('$desc', '$valor')
SQL;


    mysql_query($query2) or die(mysql_error());

    $response = "";

    if (mysql_affected_rows() > 0) {
        # code...
        $response = 'ENTRO NUEVO CAMPO';
    }


}

return $response;



}



 require "conexion.php";
    $f = $_GET["f"];
    $response = call_user_func($f);
    echo $response;




?>