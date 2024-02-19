$(document).ready(function () {
//    $('body').bind('copy cut', function (e) {
//        e.preventDefault();
//        return false;
//    });
    $("#numLote,#numLoteT").html($("#nl").val());
    dimeResults();
//    $("#consultar")
//            .click(function () {
//                dimeResults();
//            })
//            .click();
    changeLang();
//    dimeLotes();
});
function dimeResults() {
    var file = "php/functions_calidad.php";
    var param = {
        fase: "dimeResults",
        nl: $("#nl").val()
    };
    $(".tdValor").html("").css("background", "none");
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        $("#ureaVal").html(proceso.valores.ureaVal);
        $("#densVal").html(proceso.valores.densVal);
        $("#IrVal").html(proceso.valores.IrVal);
        $("#NH3Val").html(proceso.valores.NH3Val);
        $("#biuretVal").html(proceso.valores.biuretVal);
        $("#aldVal").html(proceso.valores.aldVal);
        $("#insolVal").html(proceso.valores.insolVal);
        $("#PO4Val").html(proceso.valores.PO4Val);
        $("#CaVal").html(proceso.valores.CaVal);
        $("#HeVal").html(proceso.valores.HeVal);
        $("#CuVal").html(proceso.valores.CuVal);
        $("#ZVal").html(proceso.valores.ZVal);
        $("#CrVal").html(proceso.valores.CrVal);
        $("#NiVal").html(proceso.valores.NiVal);
        $("#AlVal").html(proceso.valores.AlVal);
        $("#MgVal").html(proceso.valores.MgVal);
        $("#NaVal").html(proceso.valores.NaVal);
        $("#KVal").html(proceso.valores.KVal);
        $("#identidadVal").html(proceso.valores.identidadVal);
        $("#f1").html(proceso.valores.f1);
        $("#f2").html(proceso.valores.f2);
        $("#an").html(proceso.valores.an);

    }, "json").done(
            function () {
                $("#identidadVal").css({
                    "text-align": "right",
                    "background": "rgba(182, 240, 169, 0.7)"
                });
                revisaConformidad();
            });
}
function revisaConformidad() {
    $("#tblResults tbody tr").each(
            function () {
                var min = $(':nth-child(3)', this).html();
                var max = $(':nth-child(4)', this).html();
                var valor = $(':nth-child(5)', this).html();
                var color = "rgba(182,240,169,0.7)";
                if (min === "undefined" || max === "undefined" || valor === "undefined") {
                    $(':nth-child(5)', this).css("color", color);
                } else {
                    try {
                        if (min > 0 && eval(valor) < eval(min)) {
                            color = "rgba(181,0,43,.6)";
                        }
                        if (eval(max) > 0 && eval(valor) > eval(max)) {
                            color = "rgba(181,0,43,.6)";
                        }
                    } catch (e) {
                        console.log(e);
                    }


                    $(':nth-child(5)', this).css("background", color);
                }

            });
}