var php = 'php/functions.php';

$(document).ready(function() {
    $('.siic-navigation').find('a').click(function() {
        $(document).find('.mdl-layout__drawer').removeClass('is-visible');
    });

    creaTablaResumenPnd(() => {
        muestraResumenPdn()
    })

    muestraDetallePdn();

});


function revisaPermisos(arrayPlantas) {
    $.each(arrayPlantas, function(index, value) {
        if (value !== "yes") {
            $("." + index).remove();
        }
        console.log(index);
    });

    $('*:contains("STG")').each(function() {
        if ($(this).children().length < 1)
            $(this).css("border-bottom", "solid 3px #93C900");
    });
    $('*:contains("GDL")').each(function() {
        if ($(this).children().length < 1)
            $(this).css("border-bottom", "solid 3px #7d74e5");
    });
    $('*:contains("MTY")').each(function() {
        if ($(this).children().length < 1)
            $(this).css("border-bottom", "solid 3px #FFB03D");
    });
}

function creaTablaResumenPnd(_callback) {
    var param = {
        f: "dimePlantas"
    }
    var ths = "<th>Resumen</th>",
        pdnTot = "<td>PRODUCCIÓN TOTAL EN EL PERIODO (LTS)</td>",
        pdnAvg = "<td>PRODUCCIÓN MENSUAL PROMEDIO (LTS)</td>",
        watTot = "<td>AGUA PERMEADA CONSUMIDA (LTS)</td>",
        u100Tot = "<td>CONSUMO DE U-100 EN EL PERIODO (KGS)</td>",
        u100pLt = "<td>CONSUMO DE U-100 POR LITRO (KGS/LT)</td>",
        u100Mix = "<td>MIX DE U-100 EN EL PERIODO</td>"

    $.get(php, param, function(response) {
        console.log(response)
        $.each(response, function(i, strPlanta) {
            planta = strPlanta.replaceAll("'", "")
            ths += "<th class='" + planta + "'>" + planta + "</th>"
            pdnTot += "<td class='pdnInput " + planta + "' tag='Liters'></td>"
            pdnAvg += "<td class='pdnInput " + planta + "' tag='LitersAvg'></td>"
            watTot += "<td class='pdnInput " + planta + "' tag='agua'></td>"
            u100Tot += "<td class='pdnInput " + planta + "' tag='urea'></td>"
            u100pLt += "<td class='pdnInput " + planta + "' tag='ureaxl'></td>"
            u100Mix += "<td class='pdnInput " + planta + "' tag='ureamix'></td>"
        })
        var table = "<thead><tr>" + ths + "</tr></thead>"
        table += "<tbody>"
        table += "<tr>" + pdnTot + "</tr>"
        table += "<tr>" + pdnAvg + "</tr>"
        table += "<tr>" + watTot + "</tr>"
        table += "<tr>" + u100Tot + "</tr>"
        table += "<tr>" + u100pLt + "</tr>"
        table += "<tr>" + u100Mix + "</tr>"
        table += "</tbody>"
        $("#resumenTbl").html(table)
    }, "json").done(function() {
        _callback()
    });
}

function muestraResumenPdn() {
    console.log("muestra resumen")
    var request = {
        f: 'muestraResumenPdn',
        fec1: $('#fec1').val(),
        fec2: $('#fec2').val(),
        u: $("#uSe").val(),
        id: $("#idR").val()
    };
    $.get(php, request, function(response) {
        if (response) {
            $('.pdnInput').empty();
            console.log("muestra resumen starting each...")
            $.each(response.plantas, function(iPlanta, planta) {
                if (typeof(response[planta]) != "undefined") {
                    $("." + planta + "[tag='Liters']").html(response[planta]["Liters"]);
                    $("." + planta + "[tag='LitersAvg']").html(response[planta]["LitersAvg"]);
                    $("." + planta + "[tag='agua']").html(response[planta]["agua"]);
                    $("." + planta + "[tag='urea']").html(response[planta]["urea"]);
                    $("." + planta + "[tag='ureaxl']").html(response[planta]["ureaxl"]);
                    $("." + planta + "[tag='ureamix']").html(response[planta]["ureamix"]);
                }

            });
            console.log("muestra resumen each finished")
            $(".pdnInput").css("text-align", "right")
        };
    }, 'json').done(function() {
        mix_pdn();
    });

}

function mix_pdn() {
    console.log("Calculando mezcla de urea en el periodo")
    $("#messages").html("Obteniendo mezcla de urea por proveedor en el periodo ...")
    var request = {
        f: 'mix_pdn',
        fec1: $('#fec1').val(),
        fec2: $('#fec2').val(),
        u: $("#uSe").val(),
        id: $("#idR").val()
    };
    $.get(php, request, function(response) {
        if (response) {
            $('.mixInput').empty();
            $.each(response.plantas, function(iPlanta, planta) {
                if (typeof(response[planta]) != "undefined") {
                    $("." + planta + "[tag='ureamix']").html(response[planta]["ureamix"]);
                }
            });
        };
    }, 'json').done(function() {
        $("#messages").html("")
    });
}

function muestraDetallePdn() {

    if (typeof php != 'undefined') {

        var request = {
            f: 'muestraDetallePdn',
            fec1: $('#fec1').val(),
            fec2: $('#fec2').val(),
            u: $("#uSe").val(),
            id: $("#idR").val()
        };

        $.get(
            php,
            request,
            function(proceso) {
                if (proceso.status === 1) {
                    if (proceso.numRows === 0) {
                        alert("No existen registros con los datos proporcionados");
                        $("#fec1").focus();
                        return;
                    }
                    $("#tblDetallePdn tbody").html(proceso.data["tblDetalePdn"]);
                    $("#tblDetallePdn").dataTable({
                        dom: 'Bfrtip',
                        buttons: [
                            'copyHtml5',
                            'excelHtml5',
                            'pdfHtml5'
                        ],
                        "paging": false,
                        "bSort": false,
                        fixedHeader: {
                            header: true,
                            footer: false
                        }
                    });

                } else {
                    alert("Error:" + proceso.error);
                    return;
                }
            },
            "json");
    }
}

function pad2(number) {
    return (number < 10 ? '0' : '') + number;
}

function pandl() {
    var request = {
        f: 'muestraResumenPdn',
        fec1: $('#fec1').val(),
        fec2: $('#fec2').val(),
        u: $("#uSe").val(),
        id: $("#idR").val()
    };
    $.get(php, request, function(response) {
        if (response) {
            $('.pdnInput').empty();
            $.each(response.plantas, function(iPlanta, planta) {
                $("#" + planta.substring(0, 1) + "tyConsumption").html(response.ty[planta][0]);
                $("#" + planta.substring(0, 1) + "tyConsumptionL").html(response.ty[planta][2]);
                $("#" + planta.substring(0, 1) + "tyL").html(response.ty[planta][1]);
                $("#" + planta.substring(0, 1) + "tyLAVG").html(response.ty[planta][3]);
                $("#" + planta.substring(0, 1) + "tyAgua").html(response.agua[planta]);
                $("#" + planta.substring(0, 1) + "tyShare").html(response.ty[planta][4]);
            });

        };
    }, 'json');
}