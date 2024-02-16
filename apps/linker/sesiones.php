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

        <!--jQuery UI-->
        <link rel="stylesheet" href="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.css">
        <link rel="stylesheet" href="../../libs/jquery-ui-1.11.4.custom/jquery-ui.structure.min.css">
        <link rel="stylesheet" href="../../libs/jquery-ui-1.11.4.custom/jquery-ui.theme.min.css">
        <script type="text/javascript" src="../../libs/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
        <!--mdl-->
        <link rel="stylesheet" media="screen" type="text/css" href="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.css">
        <script src="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--Morris (Gráficas)-->
        <link rel="stylesheet" href="../../libs/morris.js-0.5.1/morris.css">
        <script type="text/javascript" src="../../libs/morris.js-0.5.1/raphael-min.js"></script>
        <script type="text/javascript" src="../../libs/morris.js-0.5.1/morris.min.js"></script>

        <!-- jHtmlArea-->
        <link rel="stylesheet" href="../../libs/jHtmlArea-0.8.0/style/jHtmlArea.css">
        <script type="text/javascript" src="../../libs/jHtmlArea-0.8.0/scripts/jHtmlArea-0.8.min.js"></script>

        <!--Autonumeric-->
        <script type="text/javascript" src="../../libs/autonumeric/numberformatter/../../libs/jshashtable-3.0.js"></script>
        <script type="text/javascript" src="../../libs/autonumeric/numberformatter/src/numberformatter.js"></script>
        <script type="text/javascript" src="../../libs/autonumeric/autoNumeric.js"></script>

        <!-- jqplot -->
        <link rel="stylesheet" type="text/css" href="../../libs/jqplot/src/jquery.jqplot.css">
        <script type="text/javascript" language="javascript" src="../../libs/jqplot/src/jquery.jqplot.js"></script>
        <script class="include" language="javascript" type="text/javascript" src="../../libs/jqplot/src/plugins/jqplot.bubbleRenderer.js"></script>
        <script type="text/javascript" src="../../libs/jqplot/src/plugins/jqplot.highlighter.js"></script>
        <script type="text/javascript" src="../../libs/jqplot/src/plugins/jqplot.cursor.js"></script>
        <script type="text/javascript" src="../../libs/jqplot/src/plugins/jqplot.dateAxisRenderer.js"></script>

        <!--DataTables (Tablas)-->
        <link rel="stylesheet" href="../../libs/DataTables-1.10.10/media/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="../../libs/DataTables-1.10.10/extensions/Buttons/css/buttons.dataTables.min.css">
        <link rel="stylesheet" href="../../libs/DataTables-1.10.10/extensions/FixedHeader/css/fixedHeader.dataTables.min.css">
        <link rel="stylesheet" href="../../libs/DataTables-1.10.10/extensions/FixedColumns/css/fixedColumns.dataTables.min.css">
        <link rel="stylesheet" href="../../libs/DataTables-1.10.10/extensions/FixedColumns/css/FixedColumns.dataTables.min.css">

        <script type="text/javascript" src="../../libs/DataTables-1.10.10/media/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="../../libs/DataTables-1.10.10/extensions/extrasrequeridos/jszip.min.js"></script>
        <script type="text/javascript" src="../../libs/DataTables-1.10.10/extensions/extrasrequeridos/pdfmake.min.js"></script>
        <script type="text/javascript" src="../../libs/DataTables-1.10.10/extensions/extrasrequeridos/vfs_fonts.js"></script>
        <script type="text/javascript" src="../../libs/DataTables-1.10.10/extensions/Buttons/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="../../libs/DataTables-1.10.10/extensions/Buttons/js/buttons.html5.min.js"></script>
        <script type="text/javascript" src="../../libs/DataTables-1.10.10/extensions/Buttons/js/buttons.print.js"></script>
        <script type="text/javascript" src="../../libs/DataTables-1.10.10/extensions/FixedHeader/js/dataTables.fixedHeader.min.js"></script>

        <!--Dates-->
        <script type="text/javascript" src="../../libs/dates/date.js"></script>

        <!--Propias-->
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/bodyCuerpo.css">
        <link rel="stylesheet" href="css/stylesLinker.css">
        <script type="text/javascript" src="js/fsesiones.js"></script>
        <link rel="icon" href="img/linker.png">
    </head>
    <body>
        <?php require_once "php/menu.php"; ?>
        <?php echo $header; ?>
        <?php echo $menu; ?>
        <div id="cuerpo">
            <div id="maincontent">
                <section id="sabana"></section>
            </div>
        </div>
    </body>
</html>




