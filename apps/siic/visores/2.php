<?php
	// $cliente = $_GET["cliente"];
	$fec1 = $_GET["fec1"];
	$fec2 = $_GET["fec2"];
	$id = $_GET["id"];
	// $titulo = $_GET["titulo"];
?>

<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" media="screen" type="text/css" href="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.css">
    <link rel="stylesheet" media="screen" type="text/css" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" media="screen" type="text/css" href="css/siic-common.css">
    <link rel="stylesheet" media="print" type="text/css" href="css/siic-common-printer.css">
    <link rel="stylesheet" media="screen" type="text/css" href="css/<?=$id?>.css">
    <link rel="stylesheet" href="../datatables/jquery.dataTables.min.css" type="text/css" />
    <link rel="stylesheet" href="../datatables/extensions/Buttons/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="../datatables/extensions/FixedHeader/css/fixedHeader.dataTables.min.css">
    <script src="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.js"></script>
    <script type="text/javascript" src="../js/jquery.min.js"></script>
    <script type="text/javascript" src="../datatables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../datatables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../datatables/media/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/extrasrequeridos/jszip.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/extrasrequeridos/pdfmake.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/extrasrequeridos/vfs_fonts.js"></script>
    <script type="text/javascript" src="../datatables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/Buttons/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/Buttons/js/buttons.print.js"></script>
    <script type="text/javascript" src="../datatables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js"></script>
</head>
<body>
    <div class="">
	<!-- <div class=""> -->


        <!-- <main class="mdl-layout__content"> -->
            <section class="" >
                <a name="misreportes"></a>


                <!-- <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-grid "> -->
                <div class="siic-content" >
                    <header class="mdl-layout__header">
            <div class="mdl-layout__header-row">
                <span class="mdl-layout-title">SkyBlue</span>
                <div class="mdl-layout-spacer"></div>
                <span class="mdl-layout-title mdl-layout--large-screen-only">
                </span>
            </div>
        </header>
      				<h4 id="rep" class="mdl-cell mdl-cell--12-col" align=center>Reporte de competencia</h4>
					<h4>Del <span class="fec1" value="<?=$fec1?>"><?=date("d/m/Y", strtotime($fec1))?></span> Al <span class="fec2" value="<?=$fec2?>"><?=date("d/m/Y", strtotime($fec2))?></span></h4>
      			<div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate siic-loading"></div>

      				<div>
                    <table class="display" id="tabla"></table>
                    </div>

                    <footer class="mdl-mini-footer siic-footer" >
                <div class="mdl-mini-footer__left-section">
                    Todos los Derechos Reservados Zar Kruse, S.A. de C.V. &trade;
                </div>
                <div class="mdl-mini-footer__right-section">
                    2015
                </div>
            </footer>
                </div>
      			<!-- </div> -->

	      	</section>
      	<!-- </main> -->








    <script type="text/javascript" src="js/2.js"></script>
</body>
</html>