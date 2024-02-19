var file = "php/fCertificadoCalidad.php";
$(document).ready(function () {
    llenaCertificado();
});

function llenaCertificado() {
    if ($("#idEntrega").val() !== "") {
        var param = {
            fase: "llenaCertificado",
            folio: $("#folio").val(),
            iprs:$("#iprs").val()
        };
        $.get(file, param, function (proceso) {
            if (proceso.status === 1) {
                $("#lote").html(proceso.data.lote);
                $("#densidad").html(proceso.data.densidad);
                $("#concentracion").html(proceso.data.concentracion);
                $("#apariencia").html(proceso.data.apariencia);
                $("#sellosFijos").html(proceso.data.sellosFijos);
                $("#sellosEscot").html(proceso.data.sellosEscot);
                $("#sellosDescar").html(proceso.data.sellosDescar);
                $("#placas").html(proceso.data.placas);
                $("#qtyL").html(proceso.data.lts);
                $("#producto").html(proceso.data.prod);
                $("#pedInt").html(proceso.data.pedInt);
                $("#indicer").html(proceso.data.indicer);
                $("#fechaHoraCertificado").html(proceso.data.fechaHoraCertificado);
                $("#datos tbody").html(proceso.dataresults.table);
            } else {
                alert(proceso.error);
                return;
            }
        }, "json");
    } else {
        alert("Datos inválidos");
    }
}