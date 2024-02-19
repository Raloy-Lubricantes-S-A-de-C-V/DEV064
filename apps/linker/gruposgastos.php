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
        <title>Sesiones</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--DataTables-->
        <link rel="stylesheet" href="../../libs/DataTables-1.10.16/media/css/jquery.dataTables.min.css">
        <script type="text/javascript" src="../../libs/DataTables-1.10.16/media/js/jquery.dataTables.min.js"></script>

        <!--JqueryUI-->
        <link rel="stylesheet" href="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.css">
        <script type="text/javascript" src="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
        
        <!-- jHtmlArea-->
        <link rel="stylesheet" href="../../libs/jHtmlArea-0.8.0/style/jHtmlArea.css">
        <script type="text/javascript" src="../../libs/jHtmlArea-0.8.0/scripts/jHtmlArea-0.8.min.js"></script>

        <!--Autonumeric-->
        <script type="text/javascript" src="../../libs/autonumeric/numberformatter/libs/jshashtable-3.0.js"></script>
        <script type="text/javascript" src="../../libs/autonumeric/numberformatter/src/numberformatter.js"></script>
        <script type="text/javascript" src="../../libs/autonumeric/autoNumeric.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/bodyCuerpo.css">
        <link rel="stylesheet" href="css/stylesLinker.css">
        <script type="text/javascript" src="js/fgruposgastos.js"></script>
        <link rel="icon" href="img/linker.png">
    </head>
    <body>
        <?php require_once "php/menu.php"; ?>
        <?php echo $header; ?>
        <?php echo $menu; ?>
        <div id="cuerpo">
            <div id="maincontent">
                <section id="sabana"  style='width:700px;margin:0 auto;'></section>
            </div>
        </div>
    </body>
</html>






