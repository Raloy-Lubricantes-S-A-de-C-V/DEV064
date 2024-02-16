var php = 'php/functions.php';

$(document).ready(function () {
    $('.siic-navigation').find('a').click(function () {
        $(document).find('.mdl-layout__drawer').removeClass('is-visible');
    });

    //Obteniendo permisos
//    revisaPermisos();

    //Funciones iniciales
    llenaInventarios();

    var hoy = new Date();

    $('#fec1').val(hoy.getFullYear() + '-' + pad2((hoy.getMonth() + 1)) + '-' + pad2(1));
    $('#fec2').val(hoy.getFullYear() + '-' + pad2((hoy.getMonth() + 1)) + '-' + pad2(hoy.getDate()));

});
function revisaPermisos() {
    var request = {
        u: $("#uSe").val(),
        id: $("#idR").val(),
        f: "dimePermisos"
    };
    $.get(php, request, function (response) {
        if (response.status === 1) {
            $.each(response.permisos, function (index, value) {
                console.log(index);
                console.log(value);
                if (value !== "yes") {
                    $("." + index).remove();
                }

            });
            $('*:contains("STG")').each(function () {
                if ($(this).children().length < 1)
                    $(this).css("border-bottom", "solid 3px #93C900");
            });
            $('*:contains("GDL")').each(function () {
                if ($(this).children().length < 1)
                    $(this).css("border-bottom", "solid 3px #7d74e5");
            });
            $('*:contains("MTY")').each(function () {
                if ($(this).children().length < 1)
                    $(this).css("border-bottom", "solid 3px #FFB03D");
            });
        } else {
            alert(response.error);
        }
    }, 'json');
}

function muestraResumenPdn() {
    var request = {
        f: 'muestraResumenPdn',
        fec1: $('#fec1').val(),
        fec2: $('#fec2').val(),
        u: $("#uSe").val(),
        id: $("#idR").val()
    };
    $.get(php, request, function (response) {
        if (response.status === 1) {
            $('.pdnInput').empty();
            $.each(response.plantas, function (iPlanta, planta) {
                $("#" + planta.substring(0, 1) + "tyConsumption").html(response.ty[planta][0]);
                $("#" + planta.substring(0, 1) + "tyConsumptionL").html(response.ty[planta][2]);
                $("#" + planta.substring(0, 1) + "tyL").html(response.ty[planta][1]);
                $("#" + planta.substring(0, 1) + "tyLAVG").html(response.ty[planta][3]);
                $("#" + planta.substring(0, 1) + "tyAgua").html(response.agua[planta]);
                $("#" + planta.substring(0, 1) + "tyShare").html(response.ty[planta][4]);
            });
        } else {
            alert(response.error);
        }
    }, 'json');

}

function consumoL4W() {
    var request = {
        f: 'consumosLW',
        periodo: "L4W",
        u: $("#uSe").val(),
        id: $("#idR").val()
    };
    $.get(php, request, function (response) {
        if (response.status === 1) {
            $.each(response.plantas, function (iPlanta, planta) {
                $("#" + planta.substring(0, 1) + "L4Wsacos").html(response.sacos[planta]);
                $("#" + planta.substring(0, 1) + "L4WsacosSem").html(eval(response.sacos[planta]) / 4);
                $("#" + planta.substring(0, 1) + "L4Wkgs").html(response.data[planta][0]);
                $("#" + planta.substring(0, 1) + "L4Wkgslt").html(response.data[planta][2]);
                $("#" + planta.substring(0, 1) + "L4Wmix").html(response.mix[planta]);
            });
        } else {
            alert(response.error);
        }
    }, 'json');

}

function consumoLW() {
    var request = {
        f: 'consumosLW',
        periodo: "LW",
        u: $("#uSe").val(),
        id: $("#idR").val()
    };
    $.get(php, request, function (response) {
        if (response.status === 1) {

            $.each(response.plantas, function (iPlanta, planta) {
                $("#" + planta.substring(0, 1) + "LWsacos").html(response.sacos[planta]);
                $("#" + planta.substring(0, 1) + "LWsacosSem").html(eval(response.sacos[planta]) / 4);
                $("#" + planta.substring(0, 1) + "LWkgs").html(response.data[planta][0]);
                $("#" + planta.substring(0, 1) + "LWkgslt").html(response.data[planta][2]);
                $("#" + planta.substring(0, 1) + "LWmix").html(response.mix[planta]);
            });
        } else {
            alert(response.error);
        }
    }, 'json');

}

function muestraDetallePdn() {

//    var url = $('.siic-ul-reportes').find('.siic-selected').attr('url');

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
                function (proceso) {
                    if (proceso.status === 1) {
                        if (proceso.numRows === 0) {
                            alert("No existen registros con los datos proporcionados");
                            $("#fec1").focus();
                            return;
                        }
                        var tblHtml = "";
                        $.each(proceso.plantas, function (iPlanta, planta) {
                            tblHtml += "<table style='width:100%'>"
                                    + "<thead>"
                                    + "<tr><th colspan='7'>" + planta + "</th></tr>"
                                    + "<tr><th>OP</th><th>FECHA Y HORA</th><th>KGS U-100</th><th>LTS AGUA</th><th>LTS EPT</th><th>UTILIZACIÓN</th><th>MIX</th><th>IF PLANTAS</th></tr>"
                                    + "</thead>"
                                    + "<tbody>"
                                    + proceso.data[planta]
                                    + "</tbody>"
                                    + "</table>";
                        });
                        $("#detalleConsumoPdn").html(tblHtml);
                    } else {
                        alert("Error:" + proceso.error);
                        return;
                    }
                },
                "json");
    }
}

function llenaInventarios() {
    $('.pdnInputInv').empty();
    consumoLW();
    consumoL4W();

    var request = {
        f: 'llenaInventarios',
        u: $("#uSe").val(),
        id: $("#idR").val()
    };

    setTimeout(function () {
        $.get(php, request, function (response) {
            if (response.status === 1) {
                $('.invInput').empty();

                var tabla = "";
                var header = "";
                var header2 = "";
                var lineII = "";
                var lineP = "";
                var lineC = "";
                var lineI = "";
                var lineCons = "";
                var lineIF = "";
                var ns = "";
                var yr = "";
                var cantP = 0;
                var cantC = "";
                var cantI = "";
                var cantCons = 0;
                var totCant = 0;
                var lineSem = "";
                var linePedidos = "";

                var tipoAnt = "";
                var plantaAnt = "";
                $.each(response.plantas, function (iPlanta, planta) {
                    $("#inventarioActual thead tr").append("<th>" + planta + "</th>");
                    $("#inventarioActual tfoot tr").append("<th>" + response.totalesPlanta[planta] + "</th>");
                });
                $.each(response.tipos, function (iTipo, tipo) {
                    var tr = "<tr id='tr" + tipo + "'><td>" + tipo + "</td>";
                    $.each(response.plantas, function (iPlanta, planta) {
                        if (response.invActual[planta][tipo] !== undefined) {
                            tr += "<td>" + response.invActual[planta][tipo] + "</td>";
                        } else {
                            tr += "<td>-</td>";
                        }

                    });
                    $("#inventarioActual tbody").append(tr);
                });
                $.each(response.plantas, function (iPlanta, planta) {
//                    $("#inventarioActual thead tr").append("<th>" + planta + "</th>");
//                    $.each(response.tipos, function (iTipoU, tipoU) {
//                        $("#tr" + tipoU).append("<td>" + response.invActual[planta][tipoU] + "</td>");
//                    });
                    tabla = "<div class='mdl-color--white mdl-cell mdl-cell--12-col'><table id='tblInvTrans" + planta + "' class='" + planta + " mdl-data-table mdl-js-data-table mdl-cell--12-col mdl-shadow--2dp'><thead></thead><tbody></tbody></table>";
                    header = "<tr><th>" + planta + "</th>";
                    header2 = "<tr><th>Num Sem</th>";
                    lineII = "<tr><td class='mdl-data-table__cell--non-numeric'>Inv. Inicial</td>";
                    lineP = "<tr><td class='mdl-data-table__cell--non-numeric'>Entradas P</td>";
                    lineC = "<tr><td class='mdl-data-table__cell--non-numeric'>Entradas C</td>";
                    lineI = "<tr><td class='mdl-data-table__cell--non-numeric'>Entradas I</td>";
                    lineCons = "<tr><td class='mdl-data-table__cell--non-numeric'>CONSUMO PROM</td>";
                    lineIF = "<tr class='highlight'><td class='mdl-data-table__cell--non-numeric'>Inv. Final</td>";
                    lineSem = "<tr class='highlight'><td class='mdl-data-table__cell--non-numeric'>Semanas Inv.</td>";
                    linePedidos = "<tr class=' small'><td class='mdl-data-table__cell--non-numeric'># OC</td>";

                    ns = "";
                    yr = "";
                    cantP = 0;
                    cantC = "";
                    cantI = "";
                    cantCons = $("#" + planta.substring(0, 1) + "L4WsacosSem").html();

                    //Inventario inicial
                    totCant = 0;
                    if (typeof eval(response.invActual[planta]["P"]) != "undefined") {
                        totCant += eval(response.invActual[planta]["P"]);
                    }
                    if (typeof eval(response.invActual[planta]["C"]) != "undefined") {
                        totCant += eval(response.invActual[planta]["C"]);
                    }
                    if (typeof eval(response.invActual[planta]["I"]) != "undefined") {
                        totCant += eval(response.invActual[planta]["I"]);
                    }
                    //Calculando por semana
                    $.each(response.wdate, function (idx, item) {
                        header += "<th>" + item + "</th>";

                        lineII += "<td>" + totCant + "</td>";
                        ns = response.wn[idx];
                        yr = response.wy[idx];
                        header2 += "<th>" + ns + "</th>";
                        if (typeof response.cantidades[planta][yr][ns] !== "undefined") {
                            if (typeof response.cantidades[planta][yr][ns]["P"] !== "undefined") {
                                cantP = response.cantidades[planta][yr][ns]["P"];
                                lineP += "<td>" + cantP + "</td>";
                                totCant += eval(cantP);
                            } else {
                                lineP += "<td></td>";
                            }

                            if (typeof response.cantidades[planta][yr][ns]["C"] !== "undefined") {
                                cantC = response.cantidades[planta][yr][ns]["C"];
                                lineC += "<td>" + cantC + "</td>";
                                totCant += eval(cantC);
                            } else {
                                lineC += "<td></td>";
                            }

                            if (typeof response.cantidades[planta][yr][ns]["I"] !== "undefined") {
                                cantI = response.cantidades[planta][yr][ns]["I"];
                                lineI += "<td>" + cantI + "</td>";
                                totCant += eval(cantI);
                            } else {
                                lineI += "<td> </td >";
                            }
                        } else {
                            lineP += "<td></td>";
                            lineI += "<td></td>";
                            lineC += "<td></td>";
                        }
                        lineCons += (typeof cantCons !== "undefined") ? "<td>" + cantCons + "</td>" : "<td></td>";
                        totCant = eval(totCant - cantCons);
                        lineIF += (typeof cantCons !== "undefined") ? "<td>" + totCant + "</td>" : "<td></td>";
                        lineSem += (typeof totCant !== "undefined") ? "<td>" + eval(Math.round(totCant / cantCons * 100)) / 100 + "</td>" : "<td></td>";
                        linePedidos += (typeof response.pedidos[planta][yr][ns] !== "undefined") ? "<td>" + response.pedidos[planta][yr][ns] + "</td>" : "<td></td>";

                    });
                    header += "</tr>"
                    header2 += "</tr>"
                    lineII += "</tr>"
                    lineP += "</tr>";
                    lineC += "</tr>";
                    lineI += "</tr>";
                    lineCons += "</tr>";
                    lineIF += "</tr>";
                    lineSem += "</tr>";
                    linePedidos += "</tr>";
                    tabla += "</table></div>";
                    $("#tablasTransito").append(tabla);
                    $("#tblInvTrans" + planta + " thead").html(header + header2);
                    $("#tblInvTrans" + planta + " tbody").html(lineII + lineP + lineC + lineI + lineCons + lineIF + lineSem + linePedidos);
                    $("#detalleOC").html(response.tablaOC);
                    $("#tblOcs").dataTable({
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
                });
            } else {
                alert(response.error);
            }
        }, 'json');
    }, 1000);
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
    $.get(php, request, function (response) {
        if (response.status === 1) {
            $('.pdnInput').empty();
            $.each(response.plantas, function (iPlanta, planta) {
                $("#" + planta.substring(0, 1) + "tyConsumption").html(response.ty[planta][0]);
                $("#" + planta.substring(0, 1) + "tyConsumptionL").html(response.ty[planta][2]);
                $("#" + planta.substring(0, 1) + "tyL").html(response.ty[planta][1]);
                $("#" + planta.substring(0, 1) + "tyLAVG").html(response.ty[planta][3]);
                $("#" + planta.substring(0, 1) + "tyAgua").html(response.agua[planta]);
                $("#" + planta.substring(0, 1) + "tyShare").html(response.ty[planta][4]);
            });
        } else {
            alert(response.error);
        }
    }, 'json');
}