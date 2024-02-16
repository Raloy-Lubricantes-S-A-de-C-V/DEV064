$(document).ready(function () {
    var param = {
        "fase": "allData",
        "folio": $("#folio").val()
    };
    console.log(param);
    $.get("php/functionsAMP.php", param, function (respuesta) {
        $.each(respuesta.grales, function (i, v) {
            $("#" + i).html(v);
        });
        $(".ltsTot").html(respuesta.grales.ltsTot);
        $("#datos table tbody").html(respuesta.detalles.tbody);
        $("#selloqr").qrcode({width: "25mm", height: "25mm", text: respuesta.grales.selloAMP});
        $("#tblRemisiones tbody").html(respuesta.remisiones);
    }, "json");
});