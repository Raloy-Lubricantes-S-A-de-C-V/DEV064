var file = "php/fIndex.php";
$(document).ready(function () {
    $("#buscarEntrega").click(function () {
        boxesSearch();
    });
    $("#cambiarcosto").click(function () {
        $("#divConcluirEnvio").show();
    });
    $("#closeConcluirEnvio").click(function () {
        $("#divConcluirEnvio").hide();
    });
    $("#odomFin,#ltsDiesel,#odomInicio").change(function () {
        var odomFinal = ($("#odomFin").val() > 0) ? $("#odomFin").val() : 0;
        var odomInicio = ($("#odomInicio").val() > 0) ? $("#odomInicio").val() : 0;
        var ltsDiesel = ($("#ltsDiesel").val() > 0) ? $("#ltsDiesel").val() : 0;
        var kmsRecorrido = eval(odomFinal - odomInicio);
        var kms_lt = eval(kmsRecorrido / ltsDiesel);
        $("#kmsRecorr").val(kmsRecorrido);
        $("#rendimiento").val(kms_lt);
    });

    //Botón en formulario para guardar el envío de pipas propias
    $("#divConcluirEnvio input").change(function () {
        calculatotales($(this));
    });
    $("input[behave-as='number']")
            .focus(function () {
                var valor = $.parseNumber($(this).val(), {format: "#,###.00", locale: "us"});
                $(this).val(valor).select();
            })
            .blur(function () {
                $(this).formatNumber({format: "#,###.00", locale: "us"});
            });
    $("#enableEdit").click(function () {
        $(this).toggle();
        $("#editBtns").toggle();
    });
    $("#cancelEdit").click(function () {
        getCostData();
    });
    $("#saveEdit").click(function () {
        concluirEnvio_save();
    });
});
function boxesSearch() {
    var param = {
        i: $("#searchString").val(),
        fase: "boxesSearch"
    };
    $.get(file, param, function (response) {
//        console.log(response.box);
        if (response.status === 1) {
            $("#muestraEntregas").html(response.box);
        } else {
            alert("Error " + response.error);
        }
    }, "json").done(function () {
        $(".formCambiaCosto").off("click").on("click", function () {
            var obj = $(this).parent();
            showDetails(obj);
        });
        $(".cancelarTerminado").off("click").on("click", function () {
            var c = confirm("Desea regresar el embarque a 'En Camino'?");
            if (c === true) {
                var obj = $(this).parent();
                cancelarTerminado(obj);
            } else {
                return false;
            }

        });
    });
}
function cancelarTerminado(obj) {

}
function showDetails(obj) {
    var identrega = $(obj).attr("identrega");
    var ltsTot = $(obj).attr("ltsTot");
    var numEnvioRaloy = $(obj).attr("numEnvioRaloy");
    $("#concEnviIdEntrega").val(identrega);
    //Limpiar formulario
    $("#datosConcEnvio").find("input")
            .val(0)
            .off("click")
            .on("click", function () {
                $(this).select();
            });
    $(".cvt").each(function () {
        $(this).html("");
    });
    $(".cvu").each(function () {
        $(this).html("");
    });
    $(".currency2").each(function () {
        $(this).html("");
    });
    $(".currency3").each(function () {
        $(this).html("");
    });
    $(".numeric0").each(function () {
        $(this).html("");
    });
    $(".numeric2").each(function () {
        $(this).html("");
    });
    $("#datosConcEnvio").find("textarea").val("");
    $("#numEnvioRaloyCE").html(numEnvioRaloy);
    $("#ltsembarqueconc").html(ltsTot).formatNumber({format: "#,##0.00", locale: "us"});
    $("#datosConcEnvio").find(".folio").html(identrega);
    $("#concluyePropias").attr("identrega", identrega);
    obtenerFijosPipa(obj.attr("placas"));
    var preciodieseltoday = $("#preciodieseltoday").val();
    $("#preciodieselsiniva").val(preciodieseltoday);
    $("#divConcluirEnvio").show();
    getCostData();
}
function getCostData() {
    var folio = $("#divConcluirEnvio").find(".folio").html();
    var lts = $("#ltsembarqueconc").html();
    lts = $.parseNumber(lts, {format: "#,##0.00", locale: "us"});
    $("input[behave-as='number']").each(function () {
        $(this).formatNumber({format: "#,##0.00", locale: "us"});
    });
    $("#enableEdit").show();
    $("#editBtns").hide();
    $("#logCosto").html("");
    var param = {
        fase: "getCostData",
        folio: folio
    };
    $.get(file, param, function (response) {
        if (response.status === 1) {
            $.each(response.alldata, function (i, v) {
                var classSelected = (v.esVigente == 1) ? "logCosto-item-selected" : "";
                var div = $("<div class='card  my-1 logCosto-item " + classSelected + "'><div class='card-body  p-2'>" + v.info + "</div></div>");
                div.attr(v.data);
                div.appendTo("#logCosto");
            });
        } else {
            alert("Error");
            console.log(response.error);
        }
    }, "json").done(function () {
        selectCosteo()
        $(".logCosto-item").off("click").on("click", function () {
            selectCosteo();
        });
    });
}
function selectCosteo() {
    var el = $(".logCosto-item-selected");
    el.css("border-color", "green");

    //inputs
    $("#preciodieselsiniva").val($.formatNumber(el.attr("preciodieselsiniva"), {format: "$#,##0.00", locale: "us"}));
    $("#ltsDiesel").val($.formatNumber(el.attr("ltsDiesel"), {format: "#,##0.00", locale: "us"}));
    $("#odomInicio").val($.formatNumber(el.attr("odomInicio"), {format: "#,##0.00", locale: "us"}));
    $("#odomFin").val($.formatNumber(el.attr("odomFin"), {format: "#,##0.00", locale: "us"}));
    $("#diesel").val($.formatNumber(el.attr("resdiesel"), {format: "#,##0.00", locale: "us"}));
    $("#pesosincarga").val($.formatNumber(el.attr("tara"), {format: "#,##0.00", locale: "us"}));
    $("#pesocarga").val($.formatNumber(el.attr("longitudpipam"), {format: "#,##0.00", locale: "us"}));
    $("#tiemporuta").val($.formatNumber(el.attr("restiemporuta"), {format: "#,##0.00", locale: "us"}));
    $("#peajes").val($.formatNumber(el.attr("respeajes"), {format: "#,##0.00", locale: "us"}));
    $("#alimentos").val($.formatNumber(el.attr("resalimentos"), {format: "#,##0.00", locale: "us"}));
    $("#hospedaje").val($.formatNumber(el.attr("reshospedaje"), {format: "#,##0.00", locale: "us"}));
    $("#otros").val($.formatNumber(el.attr("resotros"), {format: "#,##0.00", locale: "us"}));
    $("#costoext").val($.formatNumber(el.attr("rescostoext"), {format: "#,##0.00", locale: "us"}));
    $("#repartosext").val($.formatNumber(el.attr("resrepartosext"), {format: "#,##0.00", locale: "us"}));
    $("#desviosext").val($.formatNumber(el.attr("resdesviosext"), {format: "#,##0.00", locale: "us"}));
    $("#bitacoraConcEnv").val(el.attr("bitacora"));
    $("#longitudpipam").val($.formatNumber(el.attr("longitudpipam"), {format: "#,##0.00", locale: "us"}));
    //Resumen
    $("#ltsembarqueconc").html($.formatNumber(el.attr("ltsembarqueconc"), {format: "#,##0.00", locale: "us"}));
    $("#restiemporuta").html($.formatNumber(el.attr("restiemporuta"), {format: "#,##0.00", locale: "us"}));
    $("#kmsRecorrP").html($.formatNumber(el.attr("kmsRecorrP"), {format: "#,##0.00", locale: "us"}));
    $("#rendkmlP").html($.formatNumber(el.attr("rendkmlP"), {format: "#,##0.00", locale: "us"}));
    $("#pesobruto").html($.formatNumber(el.attr("pesobruto"), {format: "#,##0.00", locale: "us"}));
    $("#llantas").html($.formatNumber(el.attr("llantas"), {format: "$#,##0.00", locale: "us"}));
    $("#llantasu").html($.formatNumber(el.attr("llantasu"), {format: "$#,##0.000", locale: "us"}));
    $("#chofer").html($.formatNumber(el.attr("chofer"), {format: "$#,##0.00", locale: "us"}));
    $("#choferu").html($.formatNumber(el.attr("choferu"), {format: "$#,##0.000", locale: "us"}));
    $("#depreciacion").html($.formatNumber(el.attr("depreciacion"), {format: "$#,##0.00", locale: "us"}));
    $("#depreciacionu").html($.formatNumber(el.attr("depreciacionu"), {format: "$#,##0.000", locale: "us"}));
    $("#mantenimiento").html($.formatNumber(el.attr("mantenimiento"), {format: "$#,##0.00", locale: "us"}));
    $("#mantenimientou").html($.formatNumber(el.attr("mantenimientou"), {format: "$#,##0.000", locale: "us"}));
    $("#administracion").html($.formatNumber(el.attr("administracion"), {format: "$#,##0.00", locale: "us"}));
    $("#administracionu").html($.formatNumber(el.attr("administracionu"), {format: "$#,##0.000", locale: "us"}));
    $("#seguro").html($.formatNumber(el.attr("seguro"), {format: "$#,##0.00", locale: "us"}));
    $("#segurou").html($.formatNumber(el.attr("segurou"), {format: "$#,##0.000", locale: "us"}));
    $("#otrosfijos").html($.formatNumber(el.attr("otrosfijos"), {format: "$#,##0.00", locale: "us"}));
    $("#otrosfijosu").html($.formatNumber(el.attr("otrosfijosu"), {format: "$#,##0.000", locale: "us"}));
    $("#totalF").html($.formatNumber(el.attr("totalF"), {format: "$#,##0.00", locale: "us"}));
    $("#totalFUnitario").html($.formatNumber(el.attr("totalFUnitario"), {format: "$#,##0.000", locale: "us"}));
    $("#resdiesel").html($.formatNumber(el.attr("resdiesel"), {format: "$#,##0.00", locale: "us"}));
    $("#resdieselu").html($.formatNumber(el.attr("resdieselu"), {format: "$#,##0.000", locale: "us"}));
    $("#respeajes").html($.formatNumber(el.attr("respeajes"), {format: "$#,##0.00", locale: "us"}));
    $("#respeajesu").html($.formatNumber(el.attr("respeajesu"), {format: "$#,##0.000", locale: "us"}));
    $("#resalimentos").html($.formatNumber(el.attr("resalimentos"), {format: "$#,##0.00", locale: "us"}));
    $("#resalimentosu").html($.formatNumber(el.attr("resalimentosu"), {format: "$#,##0.000", locale: "us"}));
    $("#reshospedaje").html($.formatNumber(el.attr("reshospedaje"), {format: "$#,##0.00", locale: "us"}));
    $("#reshospedajeu").html($.formatNumber(el.attr("reshospedajeu"), {format: "$#,##0.000", locale: "us"}));
    $("#resotros").html($.formatNumber(el.attr("resotros"), {format: "$#,##0.00", locale: "us"}));
    $("#resotrosu").html($.formatNumber(el.attr("resotrosu"), {format: "$#,##0.000", locale: "us"}));
    $("#rescostoext").html($.formatNumber(el.attr("rescostoext"), {format: "$#,##0.00", locale: "us"}));
    $("#rescostoextu").html($.formatNumber(el.attr("rescostoextu"), {format: "$#,##0.000", locale: "us"}));
    $("#resrepartosext").html($.formatNumber(el.attr("resrepartosext"), {format: "$#,##0.00", locale: "us"}));
    $("#resrepartosextu").html($.formatNumber(el.attr("resrepartosextu"), {format: "$#,##0.000", locale: "us"}));
    $("#resdesviosext").html($.formatNumber(el.attr("resdesviosext"), {format: "$#,##0.00", locale: "us"}));
    $("#resdesviosextu").html($.formatNumber(el.attr("resdesviosextu"), {format: "$#,##0.000", locale: "us"}));
    $("#totalV").html($.formatNumber(el.attr("totalV"), {format: "$#,##0.00", locale: "us"}));
    $("#totalVUnitario").html($.formatNumber(el.attr("totalVUnitario"), {format: "$#,##0.000", locale: "us"}));
    $("#costototP").html($.formatNumber(el.attr("costototP"), {format: "$#,##0.00", locale: "us"}));
    $("#costototuP").html($.formatNumber(el.attr("costototuP"), {format: "$#,##0.000", locale: "us"}));
}
function concluirEnvio_save() {
    var param = {
        fase: "concluirEnvio_save",
        idEntrega: $("#datosConcEnvio").find(".folio").html(),
        valores: {
            preciodiesel: $("#preciodieselsiniva").val(),
            ltsdiesel: $("#ltsDiesel").val(),
            odominicial: $("#odomInicioP").val(),
            odomfinal: $("#odomFinP").val(),
            ltsentrega: $.parseNumber($("#ltsembarqueconc").html(), {format: "#,##0.00", locale: "us"}),
            tiemporuta: $.parseNumber($("#restiemporuta").html(), {format: "#,##0.00", locale: "us"}),
            kmsrecorr: $.parseNumber($("#kmsRecorrP").html(), {format: "#,##0", locale: "us"}),
            rendcomb: $.parseNumber($("#rendkmlP").html(), {format: "$#,##0.00", locale: "us"}),
            llantas: $.parseNumber($("#llantas").html(), {format: "$#,##0.00", locale: "us"}),
            llantasu: $.parseNumber($("#llantasu").html(), {format: "$#,##0.000", locale: "us"}),
            chofer: $.parseNumber($("#chofer").html(), {format: "$#,##0.00", locale: "us"}),
            choferu: $.parseNumber($("#choferu").html(), {format: "$#,##0.000", locale: "us"}),
            depreciacion: $.parseNumber($("#depreciacion").html(), {format: "$#,##0.00", locale: "us"}),
            depreciacionu: $.parseNumber($("#depreciacionu").html(), {format: "$#,##0.000", locale: "us"}),
            mantenimiento: $.parseNumber($("#mantenimiento").html(), {format: "$#,##0.00", locale: "us"}),
            mantenimientou: $.parseNumber($("#mantenimientou").html(), {format: "$#,##0.000", locale: "us"}),
            administracion: $.parseNumber($("#administracion").html(), {format: "$#,##0.00", locale: "us"}),
            administracionu: $.parseNumber($("#administracionu").html(), {format: "$#,##0.000", locale: "us"}),
            seguro: $.parseNumber($("#seguro").html(), {format: "$#,##0.00", locale: "us"}),
            segurou: $.parseNumber($("#segurou").html(), {format: "$#,##0.000", locale: "us"}),
            otrosfijos: $.parseNumber($("#otrosfijos").html(), {format: "$#,##0.00", locale: "us"}),
            otrosfijosu: $.parseNumber($("#otrosfijosu").html(), {format: "$#,##0.000", locale: "us"}),
            totalfijos: $.parseNumber($("#totalF").html(), {format: "$#,##0.00", locale: "us"}),
            totalfijosu: $.parseNumber($("#totalFUnitario").html(), {format: "$#,##0.000", locale: "us"}),
            diesel: $.parseNumber($("#resdiesel").html(), {format: "$#,##0.00", locale: "us"}),
            dieselu: $.parseNumber($("#resdieselu").html(), {format: "$#,##0.000", locale: "us"}),
            peajes: $.parseNumber($("#respeajes").html(), {format: "$#,##0.00", locale: "us"}),
            peajesu: $.parseNumber($("#respeajesu").html(), {format: "$#,##0.000", locale: "us"}),
            alimentos: $.parseNumber($("#resalimentos").html(), {format: "$#,##0.00", locale: "us"}),
            alimentosu: $.parseNumber($("#resalimentosu").html(), {format: "$#,##0.000", locale: "us"}),
            hospedaje: $.parseNumber($("#reshospedaje").html(), {format: "$#,##0.00", locale: "us"}),
            hospedajeu: $.parseNumber($("#reshospedajeu").html(), {format: "$#,##0.000", locale: "us"}),
            otrosvar: $.parseNumber($("#resotros").html(), {format: "$#,##0.00", locale: "us"}),
            otrosvaru: $.parseNumber($("#resotrosu").html(), {format: "$#,##0.000", locale: "us"}),
            costoext: $.parseNumber($("#rescostoext").html(), {format: "$#,##0.00", locale: "us"}),
            costoextu: $.parseNumber($("#rescostoextu").html(), {format: "$#,##0.000", locale: "us"}),
            repartosext: $.parseNumber($("#resrepartosext").html(), {format: "$#,##0.00", locale: "us"}),
            repartosextu: $.parseNumber($("#resrepartosextu").html(), {format: "$#,##0.000", locale: "us"}),
            desviosext: $.parseNumber($("#resdesviosext").html(), {format: "$#,##0.00", locale: "us"}),
            desviosextu: $.parseNumber($("#resdesviosextu").html(), {format: "$#,##0.000", locale: "us"}),
            totalvariables: $.parseNumber($("#totalV").html(), {format: "$#,##0.000", locale: "us"}),
            totalvariablesu: $.parseNumber($("#totalVUnitario").html(), {format: "$#,##0.000", locale: "us"}),
            costototal: $.parseNumber($("#costototP").html(), {format: "$#,##0.00", locale: "us"}),
            costototalu: $.parseNumber($("#costototuP").html(), {format: "$#,##0.000", locale: "us"}),
            pesobruto: $.parseNumber($("#pesobruto").html(), {format: "#,##0.00", locale: "us"}),
            longitudpipa: $("#longitudpipam").val()
        },
        bitacora: $("#bitacoraConcEnv").val(),
    };
    console.log(param);
    $.get(file, param, function (proceso) {
        console.log(proceso);
        if (proceso.status === 1) {
            actualizaTodo();
            $("#closeConcluirEnvio").click();
        } else {
            alert(proceso.error);
            return;
        }
    }, "json");
}
function espejocosto(obj) {
    var totalLts = $.parseNumber($("#ltsembarqueconc").html(), {format: "#,##0.00", locale: "us"});
    var idespejo = "res" + obj.attr("id");
    var idespejou = "res" + obj.attr("id") + "u";
    var valor = (obj.val() > 0) ? obj.val() : 0;
    var valorunit = 0;
    if (totalLts > 0 && valor > 0) {
        valorunit = Math.round((eval(valor) / eval(totalLts)) * 1000) / 1000;
    }
    var format = "$#,##0.00";
    if ($("#" + idespejo).hasClass("numeric0")) {
        format = "#,##0";
    } else if ($("#" + idespejo).hasClass("numeric2")) {
        format = "#,##0.00";
    }
    $("#" + idespejo).html(valor).formatNumber({format: format, locale: "us"});
    $("#" + idespejou).html(valorunit).formatNumber({format: "$#,##0.000", locale: "us"});
}
function calculatotales(obj) {
//    parseNumbers();
    if (obj.parent().hasClass("espejeado")) {
        espejocosto(obj);
    }
    calculacostodiesel();
    calculakms();
    calculafijos();
    calculapeso();
    var totalF = 0;
    var totalV = 0;
    var total = 0;
    var totalFUnitario = 0;
    var totalVUnitario = 0;
    var totalUnitario = 0;
    //Totales
    //Fijos
    $(".cft").each(function () {
        var valorf = $.parseNumber($(this).html(), {format: "#,##0", locale: "us"});
        totalF += (eval(valorf) > 0) ? eval(valorf) : 0;
    });
    $(".cvt").each(function () {
        var valorv = $.parseNumber($(this).html(), {format: "#,##0", locale: "us"});
        totalV += (eval(valorv) > 0) ? eval(valorv) : 0;
    });
    total = eval(totalF) + eval(totalV);
    //Totales unitarios
    var totalLts = $.parseNumber($("#ltsembarqueconc").html(), {format: "#,##0", locale: "us"});
    if (totalLts > 0 && total > 0) {
        totalFUnitario = eval(totalF) / eval(totalLts);
        totalVUnitario = eval(totalV) / eval(totalLts);
        totalUnitario = eval(total) / eval(totalLts);
    }
    $("#totalF").html(totalF).formatNumber({format: "$#,##0.00", locale: "us"});
    $("#totalFUnitario").html(totalFUnitario).formatNumber({format: "$#,##0.000", locale: "us"});
    ;
    $("#totalV").html(totalV).formatNumber({format: "$#,##0.00", locale: "us"});
    ;
    $("#totalVUnitario").html(totalVUnitario).formatNumber({format: "$#,##0.000", locale: "us"});
    ;
    $("#costototP").html(total).formatNumber({format: "$#,##0.00", locale: "us"});
    ;
    $("#costototuP").html(totalUnitario).formatNumber({format: "$#,##0.000", locale: "us"});
    ;
}
function calculapeso() {
    var pesosincarga = ($("#pesosincarga").val() > 0) ? $("#pesosincarga").val() : 0;
    var pesodelacarga = ($("#pesocarga").val() > 0) ? $("#pesocarga").val() : 0;
    var pesototal = eval(pesosincarga) + eval(pesodelacarga);
    $("#pesobruto").html(pesototal).formatNumber({format: "#,##0.00", locale: "us"});
}
function calculacostodiesel() {
    var ltsdiesel = $("#ltsDiesel").val();
    var preciodiesel = $("#preciodieselsiniva").val();
    var costodiesel = 0;
    var dieselunit = 0;
    if (ltsdiesel > 0 && preciodiesel > 0) {
        costodiesel = Math.round(ltsdiesel * preciodiesel * 100) / 100;
    }
    var totalLts = $.parseNumber($("#ltsembarqueconc").html(), {format: "#,##0.00", locale: "us"});
    if (totalLts > 0 && costodiesel > 0) {
        dieselunit = Math.round((eval(costodiesel) / eval(totalLts)) * 1000) / 1000;
    }
    $("#resdiesel").html(costodiesel).formatNumber({format: "$#,##0.00", locale: "us"});
    ;
    $("#resdieselu").html(dieselunit).formatNumber({format: "$#,##0.000", locale: "us"});
    ;
}
function calculafijos() {
    var diasruta = $.parseNumber($("#restiemporuta").html(), {format: "#,##0.00", locale: "us"});
    var kmsruta = $.parseNumber($("#kmsRecorrP").html(), {format: "#,##0", locale: "us"});
    var totalLts = $.parseNumber($("#ltsembarqueconc").html(), {format: "#,##0.00", locale: "us"});
    if (eval(diasruta) > 0) {
        $(".cft").each(function () {
            var costo = 0;
            if ($(this).hasClass("prmes")) {
                costo = (eval($(this).attr("montomensual")) / 30.4) * eval(diasruta);
            } else {
                costo = (eval($(this).attr("monto100kms")) / 100) * eval(kmsruta);
            }
            $(this).html(costo).formatNumber({format: "$#,##0.00", locale: "us"});
            if (totalLts > 0 && costo > 0) {
                var costounit = eval(costo) / eval(totalLts);
                $(this).parent().find(".cfu").html(costounit).formatNumber({format: "$#,##0.000", locale: "us"});
            }
        });
    }

}
function calculakms() {
    var odomini = $("#odomInicioP").val();
    var odomfin = $("#odomFinP").val();
    var kmsrec = 0;
    if (odomini > 0 && odomfin > 0 && odomfin > odomini) {
        kmsrec = odomfin - odomini;
    }
    $("#kmsRecorrP").html(kmsrec).formatNumber({format: "#,##0", locale: "us"});
    ;
    calcularendkml();
}
function calcularendkml() {
    var kmsrec = $.parseNumber($("#kmsRecorrP").html(), {format: "#,##0", locale: "us"});
    var ltsdiesel = $("#ltsDiesel").val();
    var rendkml = 0;
    if (kmsrec > 0 && ltsdiesel > 0) {
        rendkml = Math.round((eval(kmsrec) / eval(ltsdiesel)) * 1000) / 1000;
    }
    $("#rendkmlP").html(rendkml).formatNumber({format: "#,##0.00", locale: "us"});
    ;
}
function obtenerFijosPipa(placas) {
    var param = {
        fase: "obtenerFijosPipa",
        placas: placas
    };
    $.get(file, param, function (proceso) {
        $("#llantas").attr({
            montomensual: proceso.llantas[0],
            monto100kms: proceso.llantas[1]
        });
        $("#chofer").attr({
            montomensual: proceso.chofer[0],
            monto100kms: proceso.chofer[1]
        });
        $("#depreciacion").attr({
            montomensual: proceso.depreciacion[0],
            monto100kms: proceso.depreciacion[1]
        });
        $("#mantenimiento").attr({
            montomensual: proceso.mantenimiento[0],
            monto100kms: proceso.mantenimiento[1]
        });
        $("#administracion").attr({
            montomensual: proceso.administracion[0],
            monto100kms: proceso.administracion[1]
        });
        $("#seguro").attr({
            montomensual: proceso.seguro[0],
            monto100kms: proceso.seguro[1]
        });
        $("#otrosfijos").attr({
            montomensual: proceso.otrosfijos[0],
            monto100kms: proceso.otrosfijos[1]
        });
        $("#pesosincarga").val(proceso.pesosincarga);
        $("#longitudpipam").val(proceso.longitudm);
        var ltsTotales = $.parseNumber($("#ltsembarqueconc").html(), {format: "#,##0.00", locale: "us"});
        $("#pesocarga").val(Math.round(proceso.pesounidadcarga * ltsTotales * 100) / 100);
    }, "json");
}
function formatNumbers() {
    $(".cvt").each(function () {
        $.formatNumber($(this).html(), {format: "$#,##0.00", locale: "us"});
    });
    $(".cft").each(function () {
        $.formatNumber($(this).html(), {format: "$#,##0.00", locale: "us"});
    });
    $(".cvu").each(function () {
        $.formatNumber($(this).html(), {format: "$#,##0.000", locale: "us"});
    });
    $(".cfu").each(function () {
        $.formatNumber($(this).html(), {format: "$#,##0.000", locale: "us"});
    });
    $(".currency2").each(function () {
        $.formatNumber($(this).html(), {format: "$#,##0.00", locale: "us"});
    });
    $(".currency3").each(function () {
        $.formatNumber($(this).html(), {format: "$#,##0.000", locale: "us"});
    });
    $(".numeric2").each(function () {
        $.formatNumber($(this).html(), {format: "#,##0.00", locale: "us"});
    });
    $(".numeric0").each(function () {
        $.formatNumber($(this).html(), {format: "#,##0", locale: "us"});
    });
}