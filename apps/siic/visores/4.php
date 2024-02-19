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
    <link rel="stylesheet" type="text/css" href="../jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="../jquery-ui/jquery-ui.theme.min.css">
    <script type="text/javascript" src="../js/awesomechart.js"></script>
    <script src="https://storage.googleapis.com/code.getmdl.io/1.0.5/material.min.js"></script>
    <script type="text/javascript" src="../js/jquery.min.js"></script>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="../datatables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../datatables/media/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/extrasrequeridos/jszip.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/extrasrequeridos/pdfmake.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/extrasrequeridos/vfs_fonts.js"></script>
    <script type="text/javascript" src="../datatables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/Buttons/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="../datatables/extensions/Buttons/js/buttons.print.js"></script>
    <script type="text/javascript" src="../datatables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js"></script>
    <script type="text/javascript" src="../jquery-ui/jquery-ui.min.js"></script>
    <!-- <script type="text/javascript" src="js/jquery.canvasjs.min.js"></script> -->
</head>
<body>
	<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
		<header class="mdl-layout__header">
			<div class="mdl-layout__header-row">
				<span class="mdl-layout-title">SkyBlue</span>
				<div class="mdl-layout-spacer"></div>
				<span class="siic-nomUsuario-large mdl-layout-title mdl-layout--large-screen-only"></span>
			</div>
		</header>

		<main class="mdl-layout__content">
	      	<section class="mdl-grid" >
      			<a name="misreportes"></a>


      			<!-- <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-grid "> -->
      			<div class="mdl-color--white mdl-color-text--grey-800 mdl-shadow--4dp mdl-cell mdl-cell--8-col siic-content" >
      				<h4 id="rep" class="mdl-cell mdl-cell--12-col" align=center>Reporte de competencia</h4>
					<h4>Del <span class="fec1" value="<?=$fec1?>"><?=date("d/m/Y", strtotime($fec1))?></span> Al <span class="fec2" value="<?=$fec2?>"><?=date("d/m/Y", strtotime($fec2))?></span></h4>
      			<div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate siic-loading"></div>


      				<!-- <table id="datos" class="mdl-data-table mdl-js-data-table mdl-shadow--2dp siic-tabla__padding">
                            <thead>
                                <tr>
                                    <th class="mdl-data-table__cell--non-numeric">Competidor</th>
                                    <th class="mdl-data-table__cell--non-numeric">Litros Totales</th>
                                    <th class="mdl-data-table__cell--non-numeric">USD_DAT</th>
                                    <th class="mdl-data-table__cell--non-numeric">USDxLitro</th>
                                    <th class="mdl-data-table__cell--non-numeric">Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table> -->

                        <table class="display" id="tabla"></table>

                          <div id="dialogo" title="Detalle del competidor">
                            <p></p>
                          </div>
                          <div id="dialogo2" title="Grafico">
                            <div id="grafica_pop"></div>
                          </div>

      			</div>

	      	</section>

          <section class="mdl-grid" >
            <div class="mdl-color--white mdl-color-text--grey-800 mdl-shadow--4dp mdl-cell mdl-cell--8-col siic-content" >
              <h4 class="mdl-cell mdl-cell--12-col">Grafico Scatter</h4>
              <div class="mdl-layout mdl-cell mdl-cell--12-col mdl-grid Graph"  id="graficos"></div>
            </div>

          </section>

          <section class="mdl-grid" >
            <div class="mdl-color--white mdl-color-text--grey-800 mdl-shadow--4dp mdl-cell mdl-cell--8-col siic-content" >
              <h4 class="mdl-cell mdl-cell--12-col">Grafico Pastel</h4>
              <div class="mdl-layout mdl-cell mdl-cell--12-col mdl-grid Graph"  id="grafica_pastel"></div>
            </div>

          </section>

	      	</main>
	      	<!-- <a href="#" id="savebutton" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-color--primary mdl-color-text--accent-contrast" data-upgraded=",MaterialButton,MaterialRipple">
	      		<i class="material-icons">get_app</i>
            <span class="mdl-button__ripple-container">
                <span class="mdl-ripple is-animating" style="width: 255.952px; height: 255.952px; transform: translate(-50%, -50%) translate(9px, 23px);"></span>
            </span>
        <span class="mdl-button__ripple-container"><span class="mdl-ripple"></span></span></a> -->


	      	<footer class="mdl-mini-footer siic-footer" >
	      		<div class="mdl-mini-footer__left-section">
	      			Todos los Derechos Reservados Zar Kruse, S.A. de C.V. &trade;
	      		</div>
	      		<div class="mdl-mini-footer__right-section">
	      			2015
	      		</div>
	      	</footer>



	</div>
    <script type="text/javascript" src="js/4.js"></script>
</body>
</html>