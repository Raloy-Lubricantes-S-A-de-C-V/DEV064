<!DOCTYPE html>
<html>
    <head>
        <!--jQuery-->
        <script type="text/javascript" src="../../libs/jquery-3.2.1.min.js"></script>
    </head>
    <body>
        <div id="result"><button id="buttontry">Toggle</button></div>
        <div id="resumen">
            <table>
                <thead>
                    <tr>
                        <th>Planta</th>
                        <th>Fecha de Carga</th>
                        <th>Folio</th>
                        <th>Litros</th>
                        <th>Placas</th>
                        <th>Detalle de Envíos</th>
                        <th>Papeleta</th>
                        <th>Certificados</th>
                        <th>Remisiones ZK</th>
                        <th>AMP RALOY</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <h1>Getting server updates</h1>
        

        <script>
            if (typeof (EventSource) !== "undefined") {
                var source = new EventSource("php/cargas_sse.php");
                $("#buttontry").click(function(){$(this).parent().toggleClass("shown")});
                source.onmessage = function (event) {
//                    $("#result").append(event.data + "<br>");
                    if ($("#result").hasClass("shown")) {
                        alert("hola");
                    } else {
                        var respuesta=JSON.parse(event.data);
                        console.log(event.data);
                        if(respuesta.status===0){
                            alert("Error "+respuesta.error);
                        }
                        $("#resumen tbody").html(respuesta.box);
                    }
                    //    dimeCargas();
                };
            } else {
                document.getElementById("result").innerHTML = "Sorry, your browser does not support server-sent events...";
            }
        </script>

    </body>
</html>


