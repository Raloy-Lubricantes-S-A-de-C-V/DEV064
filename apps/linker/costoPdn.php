<?php
session_start();
if (!array_key_exists("sessionInfo", $_SESSION) || !in_array(1,explode(",",$_SESSION["sessionInfo"]["strIdsMods"]))) {
    header("location:../../login.html?app=linker");
}
?>
<?php header('Content-Type: text/html; charset=utf-8'); ?>
<html>
    <head>
        <title>OC FUERA DE LINKER</title>
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
        <link rel="stylesheet" href="css/bodyCuerpo.css">
        <link rel="stylesheet" href="css/stylesLinker.css">
        <script type="text/javascript" src="js/ocSinLink.js"></script>
        <link rel="icon" href="img/compass.png">
    </head>
    <body>

        <header class="mdl-layout__header is-casting-shadow" style="width:100% !important;color:#fff !important;background:#333 !important;">
            <div class="mdl-layout__header-row">
                <span class="mdl-layout-title mdl-layout--large-screen-only"><i class="fa fa-tint fa-3" aria-hidden="true" style="color:rgba(253, 212, 41, 1) !important;"></i> <span style="font-weight:bold;font-style:italic;">RALOY</span></span>
                <div class="mdl-layout-spacer"></div>
                <span class="mdl-layout-title"><span style="font-weight:bold;"> C<i class="fa fa-compass fa-3" aria-hidden="true" style="color:rgba(253, 212, 41, 1) !important;"></i>MPASS</span></span>
            </div>
        </header>
        <div id="cuerpo">
            <div id="intro">
                <h1>LINKER</h1>
                <h2>OC FUERA DE LINKER</h2>
            </div><br/>
            <section id="inputs">
                <h2>Filtros:</h2>
                <div>
                    <label for='material'>Cve. Material:</label><input type='text' id='material' value="0001"/>
                    <br/><label for='fec1'>Fecha Inicial:</label><input type="text" id="fec1"/>
                    <br/><label for='fec2'>Fecha Final:</label><input type="text" id="fec2"/>
                    <br/><span style="color:#c1c1c1;">*Fechas de Elaboración de OC</span>
                    <br/><button id='mostrarOCs'>Mostrar</button> <span id="loadingStatus"> <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i> </span>
                </div>
            </section>
            <section id="sabana">
                <div>
                    <table id="sabanaTable">
                    </table>

                </div>
            </section>
        </div>
    </body>
</html>



