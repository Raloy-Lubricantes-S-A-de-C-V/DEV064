<html>
    <head>
        <!-- START SIGMA IMPORTS -->
        <script src="lib/sigma.js-1.2.1/src/sigma.core.js"></script>
        <script src="lib/sigma.js-1.2.1/src/conrad.js"></script>
        <script src="lib/sigma.js-1.2.1/src/utils/sigma.utils.js"></script>
        <script src="lib/sigma.js-1.2.1/src/utils/sigma.polyfills.js"></script>
        <script src="lib/sigma.js-1.2.1/src/sigma.settings.js"></script>
        <script src="lib/sigma.js-1.2.1/src/classes/sigma.classes.dispatcher.js"></script>
        <script src="lib/sigma.js-1.2.1/src/classes/sigma.classes.configurable.js"></script>
        <script src="lib/sigma.js-1.2.1/src/classes/sigma.classes.graph.js"></script>
        <script src="lib/sigma.js-1.2.1/src/classes/sigma.classes.camera.js"></script>
        <script src="lib/sigma.js-1.2.1/src/classes/sigma.classes.quad.js"></script>
        <script src="lib/sigma.js-1.2.1/src/classes/sigma.classes.edgequad.js"></script>
        <script src="lib/sigma.js-1.2.1/src/captors/sigma.captors.mouse.js"></script>
        <script src="lib/sigma.js-1.2.1/src/captors/sigma.captors.touch.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/sigma.renderers.canvas.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/sigma.renderers.webgl.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/sigma.renderers.svg.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/sigma.renderers.def.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.labels.def.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.hovers.def.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.nodes.def.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.edges.def.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.edges.curve.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.edges.arrow.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.edges.curvedArrow.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.edgehovers.def.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.edgehovers.curve.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.edgehovers.arrow.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.edgehovers.curvedArrow.js"></script>
        <script src="lib/sigma.js-1.2.1/src/renderers/canvas/sigma.canvas.extremities.def.js"></script>
        <script src="lib/sigma.js-1.2.1/src/middlewares/sigma.middlewares.rescale.js"></script>
        <script src="lib/sigma.js-1.2.1/src/middlewares/sigma.middlewares.copy.js"></script>
        <script src="lib/sigma.js-1.2.1/src/misc/sigma.misc.animation.js"></script>
        <script src="lib/sigma.js-1.2.1/src/misc/sigma.misc.bindEvents.js"></script>
        <script src="lib/sigma.js-1.2.1/src/misc/sigma.misc.bindDOMEvents.js"></script>
        <script src="lib/sigma.js-1.2.1/src/misc/sigma.misc.drawHovers.js"></script>
        <!-- Sigma plugins -->
        <script src="lib/sigma.js-1.2.1/plugins/sigma.layout.forceAtlas2/supervisor.js"></script>
        <script src="lib/sigma.js-1.2.1/plugins/sigma.layout.forceAtlas2/worker.js"></script>
        <script src="lib/sigma.js-1.2.1/plugins/sigma.renderers.edgeLabels/settings.js"></script>
        <script src="lib/sigma.js-1.2.1/plugins/sigma.renderers.edgeLabels/sigma.canvas.edges.labels.def.js"></script>
        <script src="lib/sigma.js-1.2.1/plugins/sigma.renderers.edgeLabels/sigma.canvas.edges.labels.curve.js"></script>
        <script src="lib/sigma.js-1.2.1/plugins/sigma.renderers.edgeLabels/sigma.canvas.edges.labels.curvedArrow.js"></script>
        <script src="lib/sigma.js-1.2.1/plugins/sigma.renderers.customEdgeShapes/sigma.canvas.edges.dotted.js" type="text/javascript"></script>
        <script src="lib/jquery-3.2.1.min.js"></script>
        <script src="js/nodeschart.js"></script>
        <style type="text/css">
            #container {
                max-width: 100%;
                height: 100%;
                margin: auto;
            }
        </style>
    </head>
    
    <body>
        <input type="hidden" id="projectid" value="<?php echo $_GET["pid"];?>"/>
        <div id="container"></div>
    </body>
</html>

