<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION["sessionInfo"]) || !in_array(9, $_SESSION["sessionInfo"]["idsModulos"])) {
    header('Location: ../../login.html?app=today/index.php');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Iventario EPT</title>
        <link rel="icon" type="image/png" href="../../img/today.png" />
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--Propias-->
        <link rel="stylesheet" href="../../css/bhc.css">
        <link rel="stylesheet" href="css/sinvept.css">
        <script type="text/javascript" src="js/finvept.js"></script>
    </head>
    <body>
        <div id='cuerpo'>
            <select id="plantas">
                <option></option>
                <option>STG</option>
            </select>
            <div id="formInventario">
                <div class="tankctr">
                    <div class="tanknamectr">
                        <div class="tanknumber">1</div>
                        <div class="tankname">
                            <span class="tankplant">STG</span> <span class="tankproduct">EPT</span>
                        </div>

                        <div class="tankid">STG1</div>
                        <br style="clear:both;"/>
                    </div>

                    <div class="tankdata">
                        <div class="tankdataline">
                            <span class="label">Lote</span>
                            <input type="text" class="lote"/>
                        </div>
                        <div class="tankdataline">
                            <span class="label">Litros</span>
                            <input type="text" class="qtylts"/>
                        </div>
                    </div>
                    <div class="tankview">
                        <div class="tank fill" data-fill="85">
                            <p class="tank-text">85%</p>
                        </div>
                    </div>
                    <div class="tankinfo">
                        <span class="ltsactuales">10,000</span> L / 
                        <span class="capacidad">23,000</span>L<br/>
                        <span class="liberacion">Liberado</span>
                    </div>
                    <div class="graph">
                        <div class="bardays" data-fill="100"></div>
                        <div class="bardays" data-fill="70"></div>
                        <div class="bardays" data-fill="40"></div>
                        <div class="bardays" data-fill="20"></div>
                        <div class="bardays" data-fill="15"></div>
                        <div class="bardays" data-fill="70"></div>
                        <div class="bardays" data-fill="48"></div>
                    </div>
                </div>
                <div class="tankctr">
                    <div class="tankname">TANQUE 1 GDL</div>
                    <div class="tankdata">
                        <div class="tankdataline">
                            <span class="label">Lote</span>
                            <input type="text" class="lote"/>
                        </div>
                        <div class="tankdataline">
                            <span class="label">Litros</span>
                            <input type="text" class="qtylts"/>
                        </div>
                    </div>
                    <div class="tankview">
                        <div class="tankinfo">
                            <span class="ltsactuales">15,000</span> L / 
                            <span class="capacidad">23,000</span>L<br/>
                            <span class="liberacion">Liberado</span>
                        </div>
                        <div class="tankdrawing">
                            <div class="tank fill" data-fill="65">
                                <p class="tank-text">65%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
