<?php
session_start();
if (!array_key_exists("sessionInfo", $_SESSION) || !in_array(1,explode(",",$_SESSION["sessionInfo"]["strIdsMods"]))) {
    header("location:../../login.html?app=linker");
}
?>
<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html>
    <head>
        <title>LINKER</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>
        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--Own-->
        <script type="text/javascript" src="js/index.js"></script>
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/bodyCuerpo.css">
    </head>
    <body>
        <?php require_once "php/menu.php"; ?>
        <?php echo $header; ?>
        <?php echo $menu; ?>
        <div id="cuerpo">
            
            <div id="maincontent">
                <div style="width:40%;text-align: left;">
                    <h3><?php echo $_SESSION["sessionInfo"]["userName"]; ?>, Bienvenido a Linker</h3>
                    Linker es una herramienta desarrollada para las empresas del Grupo Nova y tiene el objetivo de ayudar a registrar y consultar todos los gastos incurridos en una operación de importación.
                    <br/><br/>
                    La herramienta es muy sencilla de utilizar ya que se conecta con el ERP de la compañía para evitar capturar varias veces la información, de esta manera, Linker relaciona los números de factura con números de Órdenes de Compra y permite conocer el costo total de un artículo en su destino.<br/>
                    <br/>
                    Es importante tener claridad de las facturas que integran una importación. Conocer el Incoterm de compra puede ayudar para identificar los gastos que deben relacionarse. <br/><br/>



                </div>
            </div>

        </div>
    </body>
</html>


