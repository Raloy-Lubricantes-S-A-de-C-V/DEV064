<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["sessionInfo"]) || !in_array(21, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: ../../login.html');
}
?>
<html>
    <head>
        <title>Envasados ZK</title>
        <link rel="icon" type="image/png" href="img/icono_camiones.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--DataTables-->
        <link rel="stylesheet" href="../../libs/DataTables-1.10.16/media/css/jquery.dataTables.min.css">
        <script type="text/javascript" src="../../libs/DataTables-1.10.16/media/js/jquery.dataTables.min.js"></script>

        <style>
            h1{
                color: #0d4672;
            }
            .alta{
                width:305px;
            }
            .label{
                width:150px;
                color: #3e3e3e;
                display:inline-block;
                margin-right:5px;
            }
            .textInput{
                width:150px;
                border:solid 1px #eeeeee;
                border-radius:5px;
                display:inline-block;
            }
            .btn{
                width:150px;
                background: #28a745;
                color:#fff;
                cursor:pointer;
                float:right;
                padding:5px;
                border:none;
            }
            .btn:hover{
                background: #34ce57;
            }
            i{
                color: #0d4672;
                font-size:10pt;
            }
            div{
                margin:3px 0;
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#btnSubmit").click(function () {
                    let param = {
                        "fase": "envasados_guardarRelacion",
                        "numRemi": $("#remision").val(),
                        "cartaPorte": $("#cartaPorte").val()
                    };
                    $.get("php/fCrud.php", param, function (response) {
                        if (response.status === 1) {
                            envasados_cargarTabla();
                            $("#statusSave").html("<span style='color:#28a745;font-weight:bold;'>Guardado</span>");
                            setTimeout(function () {
                                $("#statusSave").html("");
                            }, "2000");
                            $(".textInput").val("");
                            $("#remision").focus();
                        } else {
                            $("#statusSave").html("<span style='color:red;font-weight:bold;'>Error</span>");
                            setTimeout(function () {
                                $("#statusSave").html("");
                            }, "2000");
                            console.log(response.error);
                        }
                    }, "json");
                });
                envasados_cargarTabla();
            });
            function envasados_cargarTabla() {
                let param = {
                    "fase": "envasados_cargarTabla",
                    "remZK": $("#remision").val(),
                    "cartaPorteRal": $("#cartaPorte").val()
                }
                $.get("php/fCrud.php", param, function (response) {
                    if (response.status === 1) {
                        $("#tblEnvasadosZK tbody").html(response.tbody);
                    } else {
                        $("#tblEnvasadosZK tbody").html("");
                        console.log("Error al recuperar los datos " + response.error);
                    }
                }, "json");
            }
        </script>
    </head>
    <body>
        <h1>RELACIÓN DE EMBARQUES DE ENVASADOS ZARKRUSE</h1>
        <div class="alta">
            <div><span class="label">Remisión ZarKruse:</span><input class="textInput" type="text" id="remision"/></div>
            <div><span class="label">Carta Porte Raloy:</span><input class="textInput" type="text" id="cartaPorte"/></div>
            <div><input class="btn" type="button" value="Agregar" id="btnSubmit"/></div>
            <div id="statusSave"></div>
            <br style="clear:both;"/>
        </div>

        <hr/>
        <div id="filters">
            <i class="fa fa-filter"></i> Remisión: <input class="textInput" type="text" id="searchRemision"/><br/>
            <i class="fa fa-filter"></i> Carta Porte: <input class="textInput" type="text" id="searchRemision"/><br/>
            <i class="fa fa-filter"></i> Fechas Remisión: <input class="textInput" type="text" id="searchF1"/><input type="text" id="searchF2"/><br/>
            <i class="fa fa-filter"></i> Fechas Registro: <input class="textInput" type="text" id="searchF1"/><input type="text" id="searchF2"/><br/>
        </div>

        <table id="tblEnvasadosZK">
            <thead>
                <tr><th>Fecha Remisión ZK</th><th>Remisión ZK</th><th>Prods</th><th>Carta Porte Raloy</th><th>Fecha Registro</th><th>Usuario Registro</th></tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </body>
</html>


