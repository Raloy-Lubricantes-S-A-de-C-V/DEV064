<?php

    function repcomp(){
//         //echo "entro a query";
//         echo "string";
        $fec1 = $_GET["fec1"];
        $fec2 = $_GET["fec2"];

        $query =<<<SQL
           SELECT
            IMPORT_EXPORT,
            HSCODE_6DIGITS,
            DESCRIPTION_6DIGITS,
            HSCODE_8DIGITS,
            DESCRIPTION_8DIGITS,
            IRS_MX,
            NAME_MX,
            ADDRESS_MX,
            ZIP_MX,
            CITY_MX,
            STATE_MX,
            CUSTOM_KEY,
            SECTION_KEY,
            CUSTOM,
            CUSTOM_PORT_STATE,
            CUSTOM_BROKER,
            DOCUMENT,
            DAY,
            MONTH,
            YEAR,
            TRANSPORT,
            ORIGIN_DESTINY,
            BUYER_SELLER,
            EXCHANGE_RATE,
            WEIGHT,
            QUANTITY,
            UNIT,
            if(DESCRIPTION_8DIGITS like '% 50%',QUANTITY*.9174311*2,QUANTITY*.9174311) LTS,
            PESOS,
            USD,
            FORMAT(USD/if(DESCRIPTION_8DIGITS like '% 50%',QUANTITY*.9174311*2,QUANTITY*.9174311),3) USD_LT,
            COMER_QTY,
            COMER_UNIT,
            COMER_VALUE,
            IRS,
            NAME,
            ADDRESS,
            INTERIOR,
            EXTERIOR,
            ZIP,
            CITY_STATE,
            (SELECT Convertido FROM ConverDesc_nvo cdn WHERE cdn.CveReal=piers.DESCRIPTION_8DIGITS GROUP BY CveReal) CLASIFICACION,
            (SELECT Presentacion FROM Conver_nvo cn WHERE cn.CveReal=piers.DESCRIPTION_8DIGITS GROUP BY CveReal) PRESENTACION
            FROM piers 
            WHERE HSCODE_8DIGITS='31021001'
            AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
            AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2'
SQL;

        mysql_select_db("bopi");
        $result = mysql_query($query) or die(mysql_error());
        while ($rs = mysql_fetch_assoc($result)){

            $pres = utf8_encode($rs[PRESENTACION]);            
            $clas = utf8_encode($rs[CLASIFICACION]);
            $desc = utf8_encode($rs[DESCRIPTION_8DIGITS]);

            //Declaracion de variables para presentacion
            $Bidon = '<option value="Bidon 10L" ' . ($pres == "Bidón 10L" || $pres == "Bidon 10L" ? "selected" : "")  . '>Bidón 10L</option>';
            $Desconocida = '<option value="Desconocida" ' . ($pres == "Desconocida" ? "selected" : "")  . '>Desconocida</option>';
            $Tambor = '<option value="Tambor" ' . ($pres == "Tambor" ? "selected" : "")  .'>Tambor</option>';
            $Gal = '<option value="Gal" ' . ($pres == "Gal" ? "selected" : "")  . '>Gal</option>';
            $Tote = '<option value="Tote" ' . ($pres == "Tote" ? "selected" : "")  . '>Tote</option>';

            //Declaracion de variables para la clasificacion
            $PDEF = '<option value="PDEF" ' . ($clas == "PDEF" ? "selected" : "")  . '>PDEF</option>';
            $DEF = '<option value="DEF" ' . ($clas == "DEF" ? "selected" : "")  . '>DEF</option>';
            $OTRO = '<option value="OTRO" ' . ($clas == "OTRO" ? "selected" : "")  .'>OTRO</option>';

            $combo_pres = <<<HTML
            <select class="combito_pres" ant="" value="$pres"  desc="$desc">
                    $Desconocida
                    $Bidon
                    $Tambor
                    $Gal
                    $Tote
            </select>
HTML;



            $combo = <<<HTML
                <select class="combito" ant="" value="$clas"  desc="$desc">
                    $PDEF
                    $DEF
                    $OTRO   
                </select>
HTML;

            $renglones[] = array(
                    "IMPORT_EXPORT" => utf8_encode($rs[IMPORT_EXPORT]),
                    "HSCODE_6DIGITS" => utf8_encode($rs[HSCODE_6DIGITS]),
                    "DESCRIPTION_6DIGITS" => utf8_encode($rs[DESCRIPTION_6DIGITS]),
                    "HSCODE_8DIGITS" => utf8_encode($rs[HSCODE_8DIGITS]),
                    "DESCRIPTION_8DIGITS" => utf8_encode($rs[DESCRIPTION_8DIGITS]),
                    "CLASIFICACION" => $combo,
                    "PRESENTACION" => $combo_pres,
                    "IRS_MX" => utf8_encode($rs[IRS_MX]),
                    "NAME_MX" => utf8_encode($rs[NAME_MX]),
                    "ADDRESS_MX" => utf8_encode($rs[ADDRESS_MX]),
                    "ZIP_MX" => utf8_encode($rs[ZIP_MX]),
                    "CITY_MX" => utf8_encode($rs[CITY_MX]),
                    "STATE_MX" => utf8_encode($rs[STATE_MX]),
                    "CUSTOM_KEY" => utf8_encode($rs[CUSTOM_KEY]),
                    "SECTION_KEY" => utf8_encode($rs[SECTION_KEY]),
                    "CUSTOM" => utf8_encode($rs[CUSTOM]),
                    "CUSTOM_PORT_STATE" => utf8_encode($rs[CUSTOM_PORT_STATE]),
                    "CUSTOM_BROKER" => utf8_encode($rs[CUSTOM_BROKER]),
                    "DOCUMENT" => utf8_encode($rs[DOCUMENT]),
                    "DAY" => utf8_encode($rs[DAY]),
                    "MONTH" => utf8_encode($rs[MONTH]),
                    "YEAR" => utf8_encode($rs[YEAR]),
                    "TRANSPORT" => utf8_encode($rs[TRANSPORT]),
                    "ORIGIN_DESTINY" => utf8_encode($rs[ORIGIN_DESTINY]),
                    "BUYER_SELLER" => utf8_encode($rs[BUYER_SELLER]),
                    "EXCHANGE_RATE" => utf8_encode($rs[EXCHANGE_RATE]),
                    "WEIGHT" => number_format($rs[WEIGHT],2),
                    "QUANTITY" => number_format($rs[QUANTITY],2),
                    "UNIT" => utf8_encode($rs[UNIT]),
                    "LTS" => number_format($rs[LTS],2),
                    "PESOS" => number_format($rs[PESOS],2),
                    "USD" => number_format($rs[USD],2),
                    "USD_LT" => number_format($rs[USD_LT],2),
                    "COMER_QTY" => number_format($rs[COMER_QTY],2),
                    "COMER_UNIT" => utf8_encode($rs[COMER_UNIT]),
                    "COMER_VALUE" => number_format($rs[COMER_VALUE],2),
                    "IRS" => utf8_encode($rs[IRS]),
                    "NAME" => utf8_encode($rs[NAME]),
                    "ADDRESS" => utf8_encode($rs[ADDRESS]),
                    "INTERIOR" => utf8_encode($rs[INTERIOR]),
                    "EXTERIOR" => utf8_encode($rs[EXTERIOR]),
                    "ZIP" => utf8_encode($rs[ZIP]),
                    "CITY_STATE" => utf8_encode($rs[CITY_STATE])
                );
        }
         return json_encode($renglones);
        // return json_encode($);

}

function prod_Cambia(){
    $desc = $_GET["desc"];
    $ant = $_GET["ant"];
    $value = $_GET["valor"];
    

    mysql_select_db("bopi");


        $query =<<<SQL
           UPDATE ConverDesc_nvo
           SET Convertido='$value'
           WHERE CveReal='$desc'

SQL;

mysql_query($query) or die(mysql_error());

    $response = "";

    if (mysql_affected_rows() > 0) {
        # code...
        $response = 'is-changed';
    }else{



            $query2 =<<<SQL
            INSERT INTO ConverDesc_nvo
            VALUES ('$desc', '$value')
SQL;


    mysql_query($query2) or die(mysql_error());

    $response = "";

    if (mysql_affected_rows() > 0) {
        # code...
        $response = 'is-changed';
    }


}

return $response;



}



function pres_Cambia(){
    $desc = $_GET["desc"];
    $ant = $_GET["ant"];
    $value = utf8_encode($_GET["valor"]);
/*    $numconv= if($value=="Gal"){3.7854} elseif($value=="Tambor"){208}elseif ($value=="Bidón 10L") {9.4635}elseif ($value=="Tote") {1040.9}else {1};
*/
    mysql_select_db("bopi");


        $query =<<<SQL
           UPDATE Conver_nvo
           SET Presentacion='$value'
           WHERE CveReal='$desc'

SQL;

mysql_query($query) or die(mysql_error());

    $response = "";

    if (mysql_affected_rows() > 0) {
        # code...
        $response = 'is-changed';
    }else{



            $query2 =<<<SQL
            INSERT INTO Conver_nvo
            VALUES ('$desc', '$numconv', '$value')
SQL;


    mysql_query($query2) or die(mysql_error());

    $response = "";

    if (mysql_affected_rows() > 0) {
        # code...
        $response = 'is-changed';
    }


}

return $response;




}



 require "conexion.php";
    $f = $_GET["f"];
    $response = call_user_func($f);
    echo $response;




?>