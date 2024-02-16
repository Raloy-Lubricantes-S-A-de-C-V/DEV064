<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>TODO supply a title</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--        <script type="text/javascript">
            function searchPOs(after) {

                setTimeout(function () {
                    location.reload();
                }, 1440000);//CADA 24 HRS
            }

        </script>-->

    </head>
<!--    <body onload="searchPOs();">-->
<body>
        <div>
            <?php
            date_default_timezone_set('America/Mexico_City');
            header('Content-Type: text/html; charset=utf-8');

            $host = "200.94.73.194";
            $user = "hyescas";
            $pass = "!Zardiamond1";
            $db = "diamonddb";
            $port = "3305";

            $mysqli = new mysqli($host, $user, $pass, $db, $port);
            if ($mysqli->connect_errno) {
                $MySQLerrors[] = array("type" => "connect", "error" => $mysqli->connect_error); //Error
                $respuesta["errors"] = $MySQLerrors;
                return json_encode($respuesta);
            }
            $query = <<<SQL
   SELECT 
  FOC.NumPedido,
  FOC.Proveedor,
  p.NomProvee,
  FOC.FechElabo,
  FOC.Producto,
  FOC.DescArti,
  FOC.CantiOrden,
  FOC.CantiDada,
  FOC.FechaTermino,
  FOC.Usuario 
FROM
  FOC 
  LEFT JOIN
  FProveedor p 
  ON p.CveProvee = FOC.Proveedor 
WHERE FOC.OCCERRADA = 1 AND CantiDada=0 
  AND FOC.NumPedido IN 
  (SELECT DISTINCT 
    (l.numOC) 
  FROM
    z_linker_main l)  
SQL;

//RESULTADOS DEL QUERY
            if ($result = $mysqli->query($query)) {
                if ($result->num_rows > 0) {
                    //CON DATOS
                    $cuerpo = "Órdenes de compra canceladas en Diamond con datos en linker";
                    $cuerpo.= "<table>";
                    $cuerpo.="<thead><tr><th colspan='10'>Detalles</th></tr><tr><th>Número OC</th><th>Cve Proveedor</th><th>Proveedor</th><th>Fecha Elab.</th><th>Cve Producto</th><th>Producto</th><th>Qty Orden</th><th>Qty Recibida</th><th>Fecha Término</th><th>Usuario</th></tr></thead>";
                    $cuerpo.= "<tbody>";
                    while ($row = $result->fetch_assoc()) {
                        $cuerpo.="<tr><td>" . $row["NumPedido"] . "</td><td>" . $row["Proveedor"] . "</td><td>" . utf8_encode($row["NomProvee"]) . "</td><td>" . $row["FechElabo"] . "</td><td>" . $row["Producto"] . "</td><td>" . utf8_encode($row["DescArti"]) . "</td><td>" . $row["CantiOrden"] . "</td><td>" . $row["CantiDada"] . "</td><td>" . $row["FechaTermino"] . "</td><td>" . $row["Usuario"] . "</td></tr>";
                    }
                    $cuerpo.="</tbody>";
                    $cuerpo.="</table>";
                }
                $to = [];
                $to[] = 'gblancas@consorcionova.com';
                $to[] = 'mlinerio@consorcionova.com';
                $to[] = 'amorfin@diamondoil.com.mx';
                $to[] = 'aloya@consorcionova.com';
                $to[] = 'hyescas@raloy.com.mx';

                $subject = 'OCs CANCELADAS';

                $headers = "From: Linker Diamond <hector.yescas@raloycrm.com.mx>" . "\r\n";
                $headers .= "Reply-To: hyescas@raloy.com.mx" . "\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                if (mail(implode(",", $to), $subject, $cuerpo, $headers)) {
                    echo "Correo enviado ". date("d-m-Y  H:i:s");
                }else{
                    echo "Errores de envío ". date("d-m-Y  H:i:s");
                }
//                require_once 'libs/PHPMailer-FE_v4.11/_lib/phpmailer-fe.php';
//                require_once 'libs/PHPMailer-master/class.phpmailer.php';
//                require_once 'libs/PHPMailer-master/PHPMailerAutoload.php';
//                $mail = new PHPMailer(true);
//                $mail->isSMTP(true);
//                $mail->isHTML(true);
//                $mail->SMTPDebug = 2;
//
//                try {
//                    $mail->Host = "smtp.uservers.net";
//                    $mail->Username = 'hector.yescas@raloycrm.com.mx';
//                    $mail->Password = 'dub+but15';
//                    $mail->Port = '2525';
//                    $mail->SMTPAuth = true;
//                    $mail->addAddress('hectoryescas@gmail.com');
//                    $mail->AddReplyTo('hyescas@raloy.com.mx', 'Linker Diamond');
//                    $mail->Setfrom('hector.yescas@raloycrm.com.mx', 'Linker Diamond');
//                    $mail->Subject = 'OCs Canceladas';
//                    $mail->Body = $cuerpo;
//                    $mail->Send();
//                    echo "Mail enviado " . date("d-m-Y  H:i:s");
//                } catch (phpmailerException $e) {
//                    echo $e->errorMessage(); //Pretty error messages from PHPMailer
//                } catch (Exception $e) {
//                    echo $e->getMessage(); //Boring error messages from anything else!
//                }
                echo "<br/><hr/>" . $cuerpo . " <br/>" . date("d-m-Y  H:i:s");
            } else {
                echo "Actualizado sin datos " . date("d-m-Y H:i:s");
            }
            ?>
        </div>
    </body>
</html>


