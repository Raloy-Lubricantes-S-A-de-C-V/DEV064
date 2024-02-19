<?php
	header("Content-type: application/vnd.ms-excel; name='excel'");
	header("Content-Disposition: filename=Reporte_Especifico.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
$conexion=mysql_connect("sql.skyblue.mx","adblue_ps","pasc1990");
$fec1 = $_GET["fec1"];
$fec2 = $_GET["fec2"];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>
<body>
 <table id="datos" class="mdl-data-table mdl-js-data-table mdl-shadow--2dp siic-tabla__padding">

                                <tr>
                                    <th class="mdl-data-table__cell--non-numeric">Competidor</th>
                                    <th class="mdl-data-table__cell--non-numeric">Presentación</th>
                                    <th class="mdl-data-table__cell--non-numeric">Litros Totales</th>
                                    <th class="mdl-data-table__cell--non-numeric">USDxLitro</th>
                                    <th class="mdl-data-table__cell--non-numeric">Costo</th>
                                    <th class="mdl-data-table__cell--non-numeric">Unidad</th>
                                    <th class="mdl-data-table__cell--non-numeric">Cantidad</th>
                                    <th class="mdl-data-table__cell--non-numeric">Descripción</th>
                                    <th class="mdl-data-table__cell--non-numeric">CDV</th>
                                </tr>

<?PHP
$query =<<<SQL
           SELECT
                NAME_MX,
                IF(NOT IsNull(Presentacion), Presentacion, "Otro") AS PRESENTACION,
                CASE COMER_UNIT
                    WHEN "Kilogram"
                    THEN ROUND(COMER_QTY * 0.9174, 2 )
                    WHEN "Liter"
                    THEN ROUND(COMER_QTY, 2)
                    WHEN "Tons"
                    THEN ROUND(COMER_QTY * 917.432, 2)
                    ELSE IF(
                        NOT IsNull(NumConvertido),
                        ROUND( NumConvertido * COMER_QTY, 2 ),
                        "Vacio")
                END AS TOTAL,
                COMER_QTY/USD AS USDxLitro,
                COMER_VALUE,
                COMER_UNIT,
                COMER_QTY,
                DESCRIPTION_8DIGITS AS Description,
                IF(NOT IsNull(NumConvertido), NumConvertido,
                CASE COMER_UNIT
                    WHEN "Kilogram"
                    THEN 0.9174
                    WHEN "Liter"
                    THEN 1
                    WHEN "Tons"
                    THEN 917.432
                    END) AS CDV
            FROM
                piers LEFT JOIN Conver ON Conver.CveReal = DESCRIPTION_8DIGITS
                      LEFT JOIN ConverDesc AS cd ON cd.CveReal = DESCRIPTION_8DIGITS
            WHERE
            NOT IsNull(Convertido)
            AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) >= '$fec1'
            AND DATE(CONCAT(YEAR, '-', MONTH, '-', DAY )) <= '$fec2'
            GROUP BY Description, NAME_MX
            ORDER BY NAME_MX
SQL;

mysql_select_db("adblue_ps");
$result = mysql_query($query) or die(mysql_error());
echo $result;
while($res=mysql_fetch_array($result)){

	$name=$res[NAME_MX];
	$pres=$res[PRESENTACION];
	$total=$res[TOTAL];
	$usdxlit=$res[USDxLitro];
	$costo=$res[COMER_VALUE];
	$unidad=$res[COMER_UNIT];
	$cantidad=$res[COMER_QTY];
	$desc=$res[Description];
	$cdv=$res[CDV];

?>
<tr>
	<td><?php echo $name; ?></td>
	<td><?php echo $pres; ?></td>
	<td><?php echo $total; ?></td>
	<td><?php echo $usdxlit; ?></td>
	<td><?php echo $costo; ?></td>
	<td><?php echo $unidad; ?></td>
	<td><?php echo $cantidad; ?></td>
	<td><?php echo $desc; ?></td>
	<td><?php echo $cdv; ?></td>
 </tr>
  <?php
}
  ?>

</table>

</body>
</html>