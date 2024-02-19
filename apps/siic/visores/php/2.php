<?php

    function repcomp(){
//         //echo "entro a query";
//         echo "string";
        $fec1 = $_GET["fec1"];
        $fec2 = $_GET["fec2"];

        $query =<<<SQL
        Select NAME_MX, CONCAT(MONTH, '-', YEAR) as Fecha,  piers.`NAME` AS Distribuidor,  ROUND(SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615)),2) as Litros, ROUND(SUM(USD),2) as USD, ROUND(SUM(USD)/(SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615))),3) as USDxLt, ROUND(SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615))/(Select SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615))
           FROM piers LEFT JOIN ConverDesc_nvo AS cd ON piers.DESCRIPTION_8DIGITS = cd.CveReal
           LEFT JOIN Conver_nvo AS con ON piers.DESCRIPTION_8DIGITS = con.CveReal
           WHERE (cd.Convertido='DEF' || piers.NAME_MX='ZAR/KRUSE, S.A. DE C.V.' || piers.NAME_MX='RALOY LUBRICANTES SA DE CV')
      AND piers.HSCODE_8DIGITS = '31021001'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2') * 100, 3) AS Porc
from piers LEFT JOIN ConverDesc_nvo AS cd ON piers.DESCRIPTION_8DIGITS = cd.CveReal
           LEFT JOIN Conver_nvo AS con ON piers.DESCRIPTION_8DIGITS = con.CveReal


WHERE (cd.Convertido='DEF' || piers.NAME_MX='ZAR/KRUSE, S.A. DE C.V.' || piers.NAME_MX='RALOY LUBRICANTES SA DE CV')
      AND piers.HSCODE_8DIGITS = '31021001'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2'
GROUP BY NAME_MX
ORDER BY Porc DESC

SQL;

        mysql_select_db("bopi");
        $result = mysql_query($query) or die(mysql_error());

        while ($rs = mysql_fetch_assoc($result)){
            $renglones[] = array(
                    "Competidor" => utf8_encode($rs[NAME_MX]),
                    "Fecha"=> date($rs[Fecha]),
                    "Distribuidor" => utf8_encode($rs[Distribuidor]),
                    "Litros_Totales" => number_format($rs[Litros],2),
                    "USD" => number_format($rs[USD],2),
                    "USDxLitro" => number_format($rs[USDxLt],2),
                    "Porcentaje" => number_format($rs[Porc], 3)
                );
        }
        return json_encode($renglones);
        // return $query;
    }

    require "conexion.php";
    $f = $_GET["f"];
    $response = call_user_func($f);
    echo $response;
?>