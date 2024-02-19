<?php
include_once "connParam.php";
$userName=$_SESSION["sessionInfo"]["userName"];
$datesession=$_SESSION["sessionInfo"]["sessionDate"];
$header=<<<HTML
        <header>
            <div id='leftHeader'>
                <a id="logo" href="../../index.php"><img src="../../img/Logo Skyblue horizontal.png" alt="SkyBlue"/></a>
                <span id="appName" ><img class='appicon' src='../../img/linker.png'/> Linker Zar Kruse</span>
            </div>
            <div id='rightHeader'>
                <span><i class="fas fa-user"></i> <span id="userSession">$userName</span></span>
                <span><i class="fa fa-clock-o"></i> <span>$datesession</span>
                <span><a href='../../login.html'><i class="fa fa-sign-out-alt"></i></a></span>
            </div>
        </header>
HTML;
$menu = <<<HTML
        <div id="menu">
            <div id='tabInicio' class="tab" page="index.php">
                Inicio
            </div>
            <div id='tabLinker' class="tab" page="linker.php">
                Crear / Consultar
            </div>
            <div id='tabCostoOC' class="tab" page="evolucionCosto.php">
                Costo por OC
            </div>
            <div id='tabOCSinLink' class="tab" page="ocSinLink.php">
                OC fuera de Linker
            </div>
            <div id='tabgruposgastos' class="tab" page="gruposgastos.php">
                Grupos de Gastos
            </div>
            
        <br style='clear:both'/>
        </div>
HTML;

//<div id='tabSesiones' class="tab" page="sesiones.php">
//                Sesiones
//            </div>