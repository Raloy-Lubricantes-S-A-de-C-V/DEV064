<?php
session_start();
date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>LOGÍSTICA SKYBLUE</title>
        <meta charset="UTF-8">

        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>

        <!--Fonts Awesome-->
        <link rel="stylesheet" href="../../libs/fontawesome-free-5.4.2/css/all.min.css">

        <!--Propias-->
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/styleLogin.css">
        <script type="text/javascript" src="js/flogin.js"></script>
    </head>
    <body>
        <header>
            <img src="img/Logo Skyblue horizontal.png" alt="SkyBlue"/>
            <div class="headerRight">Logística</div>
            <br style="clear:both"/>
        </header>
        <div id="cuerpo">
            <div id="loginForm">
                <div class="left">
                    <div><i class="fa fa-user"></i></div>
                    <div><i class="fa fa-lock"></i></div>
                </div>
                <div class="right">
                    <div><input type="text" id="user"/></div>
                    <div><input type="password" id="pssw"/></div>
                </div>
                <div class="bottom">
                    <div><button id="loginBtn"><i class="fa fa-check"></i> Ingresar</button></div>
                </div>
            </div>
        </div>    
    </body>
</html>