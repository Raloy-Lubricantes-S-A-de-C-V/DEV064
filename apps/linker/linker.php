<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!array_key_exists("sessionInfo", $_SESSION) || !in_array(1,explode(",",$_SESSION["sessionInfo"]["strIdsMods"]))) {
    header("location:../../login.html?app=linker");
}
?>
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
        <script type="text/javascript" src="js/fLinker.js?v=2.2"></script>
        <link rel="icon" href="img/linker.png">
    </head>
    <body>
        <?php require_once "php/menu.php"; ?>
        <?php echo $header; ?>
        <?php echo $menu; ?>
        <input id="nomUsuario" type='hidden' value='<?php echo $_SESSION["sessionInfo"]["user"];?>'/>
        <div id="cuerpo">
            <section id="seccMaterial">
                    <div id='matOpts'>
                        <div class='selector selectorMat'>
                            <label for='mainMaterial'>Clave</label>
                            <input  id='mainMaterial'  placeholder='' type='text'/>
                        </div>
                        <div class='selector selectorOC'>
                            <label for='PONumber'>OC</label>
                            <select id='PONumber'></select>
                            <!--<input  id='PONumber' placeholder='' type='text'/>-->
                        </div>
                    </div>
                    <div id='matButtons'>
                        <div class='selectorB'><button id='getMaterialBtn'>Aceptar</button></div>
                        <div class='selectorC' stat='' id='menuDisplayer'></div>
                    </div>
                    <div id='menuGral'>
                        <ul>
                            <li id='accionStatus'></li>
                        </ul>
                    </div>
                    <br style="clear:both;"/>
                </section>
            <div id="maincontent">
                <div id='mensajes'>Updated</div>

                
                <section>
                    <div id='mainMaterialMain'>
                        <div id='matInfoCont'>
                            <table id='materialInfo'>
                                <thead>
                                    <tr>
                                        <th rowspan="2"></th><th colspan='5'>Datos de la Orden de Compra</th>
                                    </tr>
                                    <tr>
                                        <th></th><th  colspan='5'></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class='tdLabel'>OC</td>
                                        <td id='tdOC'  class='materialInfoTd'></td>
                                        <td class='tdLabel'>Fecha</td>
                                        <td id='tdFecElabo'  class='materialInfoTd'></td>
                                        <td class='tdLabel'>CC</td>
                                        <td id='tdCC'  class='materialInfoTd'></td>

                                    </tr>
                                    <tr>
                                        <td class='tdLabel'>Proveedor</td>
                                        <td id='tdCveProveedor' class='materialInfoTd'></td>
                                        <td colspan='4' id='tdProveedor' class='materialInfoTd'></td>
                                    </tr>
                                    <tr>
                                        <td class='tdLabel'>Artículo</td>
                                        <td id='tdMaterial' class='materialInfoTd'></td>
                                        <td colspan='4' id='tdMaterialDesc' class='materialInfoTd'></td>
                                    </tr>
                                    <tr>
                                        <td class='tdLabel'>Cantidad</td>
                                        <td id='tdCantidad' class='materialInfoTd numeric'></td>                                    
                                        <td class='tdLabel'>Unidad</td>
                                        <td id='tdUnidad' class='materialInfoTd'></td>                                    
                                        <td class='tdLabel'>Usuario</td>
                                        <td id='tdUsuario'  class='materialInfoTd'></td>

                                    </tr>
<!--                                    <tr>
                                        <td class="tdLabel">Diferencia</td>
                                        <td colspan="1" fZ="" id="difCant" class="materialInfoTd numeric"></td>
                                        <td class='tdLabel'>Porcentaje</td>
                                        <td id='tdPorcentaje' class='materialInfoTd'></td> 
                                    </tr>-->
                                    <tr>
                                        <td class='tdLabel'>P.U.</td>
                                        <td id='tdPrecio' class='materialInfoTd currency'></td>
                                        <td class='tdLabel'>Subtotal</td>
                                        <td id='tdTotal' class='materialInfoTd currency'></td>
                                        <td class='tdLabel'>Moneda</td>
                                        <td id='tdMoneda' class='materialInfoTd'></td>
                                    </tr>
                                    <tr>
                                        <td class="tdLabel">Observaciones</td><td  colspan='5' id='tdObs' class='materialInfoTd'></td>
                                    </tr>
                                    <tr>
                                        <td class="tdLabel">Status OC</td><td  colspan='2' id="statusOC" class=" materialInfoTd"></td>
                                        <td class="tdLabel">Id Linker</td><td  colspan='2' id="idLink" class="materialInfoTd"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdLabel">Status Linker</td><td  colspan='2' id='tdStatusLinker' class='materialInfoTd'></td>
                                        <td class="redLabel">Fecha Zarpe</td><td colspan="2" fZ="" id="fechaZarpe" class="redLabel materialInfoTd"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id='resumenCosto'>
                        </div>
                        <br style='clear:both'/>

                    </div>
                    <br>
                    <div id="partidasEnlazadas">
                        <ul id="tabs"><li boxContent="linkedInvoicesTbl" class="boxTab current">FACTURAS ENLAZADAS</li><li boxContent="linkedCNTbl" class="boxTab">NOTAS DE CRÉDITO</li></ul>
                        <div id='partidasEnlazadasBox'>
                            <table id='linkedInvoicesTbl' class="boxContent">
                                <thead>
                                    <tr><th>Categoría</th><th>Artículo / Servicio</th><th>Proveedor</th><th>No. Factura</th><th>Cantidad</th><th>USD</th><th>USD/Unidad</th><th>T.C.</th><th>% Apl</th><th>Monto Orig.</th><th>Pago</th><th>Id Link</th><th class="clickableSH"></th></tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <table id='linkedCNTbl'  class="boxContent">
                                <thead>
                                    <tr><th>Número NC</th><th>Proveedor</th><th>Factura Madre</th><th>Cantidad</th><th>% Apl</th><th>USD</th><th>USD/Unidad</th></tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
                <br/>
                <br/>
                <section id='buscaFacturasSect' class="onlyEditUsers">
                    <div id='buscaFacturas'>
                        <div id='selectorContainer'>
                            <h2>Enlace de Partidas de Factura</h2>
                            <div>
                                <label for="supplierInvoiceSearch">Número de Factura:</label>
                                <input id="supplierInvoiceSearch" type="text"/>
                                <button id='getInvoicesBtn'><i class='fa fa-search'></i> Buscar</button>
                                <div id='facturasBusquedaCont'>
                                    <table id="facturasBusqueda">
                                        <thead>
                                            <tr><th></th><th>Factura</th><th>Proveedor<th>Subtotal</th><th>Moneda</th></tr>

                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <br style='clear:both'/>
                            </div>
                            <div id='invoiceInfoCont'>
                                <div id='supplierInvoiceInfo'></div>
                                <div>
                                    <table>
                                        <tr>
                                            <!-- DATOS QUE SE APLICAR�?N A TODAS LAS PARTIDAS DE LA FACTURA QUE SE SELECCIONE-->
                                            <td>T.C. a USD</td>
                                            <td><input type="number" id="tcToUSD"/></td>
                                            <td>Pedimento</td>
                                            <td><input id='pedimento' type='text' class='input2Char inputPedimento' maxlength="2"/><input id='pedimento' type='text' class='input2Char inputPedimento' maxlength="2"/><input id='pedimento' class='input4Char inputPedimento' type='text' maxlength="4"/><input id='pedimento' type='text'  class='input10Char inputPedimento' maxlength="10"/></td>
                                            <td>Clase Pedimiento</td>
                                            <td>
                                                <select id='clasePedimento'>
                                                    <option value='A1'>A1</option>
                                                    <option value='R1'>R1</option>
                                                    <option value='R2'>R2</option>
                                                </select>
                                            </td>
                                            <td><button id='linkInvoicesBtn'><i class='fa fa-paperclip'></i> Enlazar Partidas</button></td>
                                        </tr>
                                    </table> 
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>



