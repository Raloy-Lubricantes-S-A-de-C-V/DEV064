$(document).ready(function () {
    cargaDatos();
    $("#print").click(function () {
        $(this).parent().hide();
        window.print();
        setTimeout(function () {
            $("#options").show();
        }, 5000);
    });
    $("#sendEmail").click(function () {
        sendEmail();
    });
});
function cargaDatos() {
    var file = "php/fSolCarga.php";
    var param = {
        fase: "cargaDatos",
        folio: $("#folio").html()
    };
//    console.log(param.folio);
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        $("#fechaSolicitud").html(proceso.data.fechaSolicitud);
        $("#solicitante").html(decode_utf8(encode_utf8(proceso.data.solicitante)));
        $("#placasUnidad").html(proceso.data.placas);
        $("#capacidadUnidad").html(proceso.data.capac);
        $("#fechaCarga").html(proceso.data.fecha_carga);
        $("#fechaRegreso").html(proceso.data.fecha_regreso);
        $("#plantaCarga").html(proceso.data.planta_carga);
        $("#plantaRegreso").html(proceso.data.planta_regreso);
        $("#datosPapeleta tbody").html(proceso.data.tablaDatos);
        $("#totalLts").html(proceso.data.totalLts);
        $("#utilizUnid").html(proceso.data.utilizUnid);
        $("#obs").html(proceso.data.obs);
        
        $("#lote").html(proceso.data.loteZK);
        $("#remisionesZK").html(proceso.data.remisionZK);
        $("#sellosEscotilla").html(proceso.data.sellosEscotilla);
        $("#sellosDescarga").html(proceso.data.sellosDescarga);
        $("#numEnvio").html(proceso.data.numEnvioRaloy);
        $("#pesoNeto").html(proceso.data.pesoNeto);
        $("#respCar").html(proceso.data.responsableCarga);
        $("#densidad").html(proceso.data.densidad);
        $("#concentracion").html(proceso.data.concentracion);
        $("#apariencia").html(proceso.data.apariencia);

    }, "json");

}
function decode_utf8(s) {
  return decodeURIComponent(escape(s));
}
function encode_utf8(s) {
  return unescape(encodeURIComponent(s));
}
function sendEmail() {
    var address = prompt("Indique las direcciones de destino separadas por coma", "");
    var file = "php/fSolCarga.php";
    var param = {
        fase: "sendMail",
        data: $("#hojaSolicitud").html(),
        address: address,
        folio: $("#folio").html()
    };
    $.post(file, param, function (proceso) {
        if (proceso.respuesta === 1) {
            alert("e-mail Enviado con éxito");
        } else {
            console.log(proceso);
            alert("Error al intentar enviar la solicitud");
        }
    }, "json").done(function () {
        $(".logo img").show();
        $(".toSend").hide();
    });
}
function saveData(){
    
}