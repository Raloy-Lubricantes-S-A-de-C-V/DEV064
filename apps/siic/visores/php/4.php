<?php

    function repcomp(){
//         //echo "entro a query";
//         echo "string";
        $fec1 = $_GET["fec1"];
        $fec2 = $_GET["fec2"];

        $query =<<<SQL
           Select NAME_MX, ROUND(SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615)),2) as Litros, ROUND(SUM(USD),2) as USD, ROUND(SUM(USD)/(SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615))),3) as USDxLt, ROUND(SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615))/(Select SUM(IF(NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                 piers.QUANTITY*0.917384615))
           FROM piers LEFT JOIN ConverDesc_nvo AS cd ON piers.DESCRIPTION_8DIGITS = cd.CveReal
           LEFT JOIN Conver_nvo AS con ON piers.DESCRIPTION_8DIGITS = con.CveReal
           WHERE (cd.Convertido='DEF' || piers.NAME_MX='ZAR/KRUSE, S.A. DE C.V.' || piers.NAME_MX='RALOY LUBRICANTES SA DE CV')
      AND piers.HSCODE_8DIGITS = '31021001'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2') * 100, 2) AS Porc
      
      
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
          $nom = utf8_encode($rs[NAME_MX]);
          $boton = "<button class=\"boton_desc\" comp=\"$nom\">Detalles</button>";
          $renglones[] = array(
                    "Competidor" => utf8_encode($rs[NAME_MX]),
                    "Litros_Totales" => number_format($rs[Litros],2),
                    "USD" => number_format($rs[USD],2),
                    "USDxLitro" => number_format($rs[USDxLt],3),
                    "Porcentaje" => number_format($rs[Porc], 3),
                    "Detalles" => $boton
                );
        }
        return json_encode($renglones);
        // return $query;
    }

    function boton(){
      $fec1 = $_GET["fec1"];
      $fec2 = $_GET["fec2"];
      $comp= $_GET["comp"];

      $query =<<<SQL
        SELECT (SELECT Presentacion FROM Conver_nvo cn WHERE cn.CveReal=piers.DESCRIPTION_8DIGITS GROUP BY CveReal)  AS Presentacion,
        ROUND(SUM(piers.QUANTITY*0.917384615),2) as Litros, 
        ROUND(SUM(USD),2) as USD, 
        ROUND(SUM(USD)/(SUM(piers.QUANTITY*0.917384615)),3) as USDxLt,
                ROUND(SUM(piers.QUANTITY*0.917384615)/(Select SUM(piers.QUANTITY*0.917384615)
                  FROM piers 
                  WHERE (piers.NAME_MX='$comp')
                  AND piers.HSCODE_8DIGITS = '31021001'
                  AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
                  AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2') * 100, 2) AS Porc
                  
        FROM piers 
        WHERE piers.NAME_MX="$comp"
        AND piers.HSCODE_8DIGITS = '31021001'
        AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
        AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2'
        
        GROUP BY Presentacion
        ORDER BY Porc DESC
           
SQL;
      mysql_select_db("bopi");
       $result = mysql_query($query) or die(mysql_error());
       while ($rs = mysql_fetch_assoc($result)){
          $renglones[] = array(
                    "Presentacion" => utf8_encode($rs[Presentacion]),
                    "Litros_Totales" => number_format($rs[Litros],2),
                    "USD" => number_format($rs[USD],2),
                    "USDxLitro" => number_format($rs[USDxLt],3),
                    "Porcentaje" => number_format($rs[Porc], 3)
                );
        }
        return json_encode($renglones);



    }

    function grafica_pop(){
      $fec1 = $_GET["fec1"];
      $fec2 = $_GET["fec2"];
      $comp= $_GET["comp"];

      $query =<<<SQL
        SELECT IF(con.Presentacion='Desconocida', 'No Especificado', con.Presentacion) AS Presentacion,
                ROUND(SUM(piers.QUANTITY*0.917384615)/(Select SUM(piers.QUANTITY*0.917384615)
                  FROM piers LEFT JOIN ConverDesc_nvo AS cd ON piers.DESCRIPTION_8DIGITS = cd.CveReal
                    LEFT JOIN Conver_nvo AS con ON piers.DESCRIPTION_8DIGITS = con.CveReal
                  WHERE (piers.NAME_MX='$comp')
                  AND piers.HSCODE_8DIGITS = '31021001'
                  AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
                  AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2') * 100, 2) AS Porc
                  
        FROM piers LEFT JOIN ConverDesc_nvo AS cd ON piers.DESCRIPTION_8DIGITS = cd.CveReal
           LEFT JOIN Conver_nvo AS con ON piers.DESCRIPTION_8DIGITS = con.CveReal
           

        WHERE piers.NAME_MX="$comp"
        AND piers.HSCODE_8DIGITS = '31021001'
        AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
        AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2'
        
        Group by Presentacion
           
SQL;
      mysql_select_db("bopi");
       $result = mysql_query($query) or die(mysql_error());
       while ($rs = mysql_fetch_assoc($result)){
          $renglones[] = array(
                    "Presentacion" => utf8_encode($rs[Presentacion]),
                    "Porcentaje" => number_format($rs[Porc], 3)
                );
        }
        return json_encode($renglones);
    }

    function graficos()
    {
       $fec1 = $_GET["fec1"];
        $fec2 = $_GET["fec2"];

        $query =<<<SQL
           Select NAME_MX, 
                  ROUND(
                    SUM(
                      IF(
                        NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                        if(
                          DESCRIPTION_8DIGITS like '% 50%',QUANTITY*.9174311*2,QUANTITY*.9174311
                        )
                      )
                    ),2
                  ) as Litros, 
                  ROUND(SUM(USD),2) as USD, 
                  ROUND(
                    SUM(USD)/
                    (SUM
                      (
                        IF(
                          NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                          if(
                            DESCRIPTION_8DIGITS like '% 50%',QUANTITY*.9174311*2,QUANTITY*.9174311
                          )
                        )
                      )
                    ),3
                  ) as USDxLt, 
                  ROUND(
                    SUM(
                      IF(
                        NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                        IF(
                          piers.DESCRIPTION_8DIGITS LIKE '% 50%',piers.QUANTITY*0.917384615*2,piers.QUANTITY*0.917384615
                        )
                      )
                    )/
                    (Select SUM
                      (
                        IF(
                          NAME_MX = 'ZAR/KRUSE, S.A. DE C.V.', piers.QUANTITY * 3.07692,
                          IF(
                            piers.DESCRIPTION_8DIGITS LIKE '% 50%',piers.QUANTITY*0.917384615*2,piers.QUANTITY*0.917384615
                          )
                        )
                    ),2
                  ) as Porc
           FROM piers LEFT JOIN ConverDesc_nvo AS cd ON piers.DESCRIPTION_8DIGITS = cd.CveReal
           LEFT JOIN Conver_nvo AS con ON piers.DESCRIPTION_8DIGITS = con.CveReal
           WHERE (cd.Convertido='DEF' || piers.NAME_MX='ZAR/KRUSE, S.A. DE C.V.' || piers.NAME_MX='RALOY LUBRICANTES SA DE CV')
      AND piers.HSCODE_8DIGITS = '31021001'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2') * 100, 2) AS Porc
      
      
from piers LEFT JOIN ConverDesc_nvo AS cd ON piers.DESCRIPTION_8DIGITS = cd.CveReal
           LEFT JOIN Conver_nvo AS con ON piers.DESCRIPTION_8DIGITS = con.CveReal
           
           


WHERE (cd.Convertido='DEF' || piers.NAME_MX='ZAR/KRUSE, S.A. DE C.V.' || piers.NAME_MX='RALOY LUBRICANTES SA DE CV')
      AND piers.HSCODE_8DIGITS = '31021001'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
      AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2'
GROUP BY NAME_MX
ORDER BY Porc DESC
limit 7

SQL;

        mysql_select_db("bopi");
        $result = mysql_query($query) or die(mysql_error());

        while ($rs = mysql_fetch_assoc($result)){
          $nom = utf8_encode($rs[NAME_MX]);
          $boton = "<button class=\"boton_desc\" comp=\"$nom\">Detalles</button>";
          $renglones[] = array(
                    "Competidor" => utf8_encode($rs[NAME_MX]),
                    "Litros_Totales" => $rs[Litros],
                    "USD" => $rs[USD],
                    "USDxLitro" => $rs[USDxLt],
                    "Porcentaje" => $rs[Porc],
                    "Detalles" => $boton
                );
        }
        return json_encode($renglones);

    }

    require "conexion.php";
    $f = $_GET["f"];
    $response = call_user_func($f);
    echo $response;



?>