var php = 'php/functions.php';
var colores = [];
colores['DIST'] = '#93C900';
colores['EXPORT'] = '#FF8949';
colores['INTER'] = '#FFB03D';
colores['MAQ'] = '#00BBD6';
colores['PLANTA'] = '#5882FF';
colores['RALY'] = '#BA65C9';
colores['VTADIR'] = '#EF3C79';
colores['mes-DIST'] = '#93C900';
colores['mes-EXPORT'] = '#FF8949';
colores['mes-INTER'] = '#FFB03D';
colores['mes-MAQ'] = '#00BBD6';
colores['mes-PLANTA'] = '#5882FF';
colores['mes-RALY'] = '#BA65C9';
colores['mes-VTADIR'] = '#EF3C79';


$(document).ready(function () {
    $('.siic-navigation').find('a').click(function () {
        $(document).find('.mdl-layout__drawer').removeClass('is-visible');
    });

    llenaInventarios();

    muestraResumenPdn();
    muestraDetallePdn();

    $('#frmVentas').submit(function (e) {
        e.preventDefault();
        dimeVentas();
    });

    var hoy = new Date();

    $('#fec1').val(hoy.getFullYear() + '-' + pad2((hoy.getMonth() + 1)) + '-' + pad2(1));
    $('#fec2').val(hoy.getFullYear() + '-' + pad2((hoy.getMonth() + 1)) + '-' + pad2(hoy.getDate()));
    $('#fec1Vtas').val(hoy.getFullYear() + '-' + pad2((hoy.getMonth() + 1)) + '-' + pad2(1));
    $('#fec2Vtas').val(hoy.getFullYear() + '-' + pad2((hoy.getMonth() + 1)) + '-' + pad2(hoy.getDate()));

});
function revisaPermisos(arrayPlantas) {
    $.each(arrayPlantas, function (index, value) {
        if (value !== "yes") {
            $("." + index).remove();
        }
        console.log(index);
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
}
function muestraResumenPdn() {
    var request = {
        f: 'muestraResumenPdn',
        fec1: $('#fec1').val(),
        fec2: $('#fec2').val()
    };
    $.get(php, request, function (response) {
        if (response) {
            $('.pdnInput').empty();
            $.each(response.plantas, function (iPlanta, planta) {
                $("#" + planta.substring(0, 1) + "tyConsumption").html(response.ty[planta][0]);
                $("#" + planta.substring(0, 1) + "tyConsumptionL").html(response.ty[planta][2]);
                $("#" + planta.substring(0, 1) + "tyL").html(response.ty[planta][1]);
                $("#" + planta.substring(0, 1) + "tyLAVG").html(response.ty[planta][3]);
                $("#" + planta.substring(0, 1) + "tyAgua").html(response.agua[planta]);
                $("#" + planta.substring(0, 1) + "tyShare").html(response.ty[planta][4]);
            });
            revisaPermisos();
        }
        ;
    }, 'json');

}

function consumoL4W() {
    var request = {
        f: 'consumosLW',
        periodo: "L4W"
    };
    $.get(php, request, function (response) {
        if (response) {
            $.each(response.plantas, function (iPlanta, planta) {
                $("#" + planta.substring(0, 1) + "L4Wsacos").html(response.sacos[planta]);
                $("#" + planta.substring(0, 1) + "L4WsacosSem").html(eval(response.sacos[planta]) / 4);
                $("#" + planta.substring(0, 1) + "L4Wkgs").html(response.data[planta][0]);
                $("#" + planta.substring(0, 1) + "L4Wkgslt").html(response.data[planta][2]);
                $("#" + planta.substring(0, 1) + "L4Wmix").html(response.mix[planta]);
            });
        }
        ;
    }, 'json');

}

function consumoLW() {
    var request = {
        f: 'consumosLW',
        periodo: "LW"
    };
    $.get(php, request, function (response) {
        if (response) {

            $.each(response.plantas, function (iPlanta, planta) {
                $("#" + planta.substring(0, 1) + "LWsacos").html(response.sacos[planta]);
                $("#" + planta.substring(0, 1) + "LWsacosSem").html(eval(response.sacos[planta]) / 4);
                $("#" + planta.substring(0, 1) + "LWkgs").html(response.data[planta][0]);
                $("#" + planta.substring(0, 1) + "LWkgslt").html(response.data[planta][2]);
                $("#" + planta.substring(0, 1) + "LWmix").html(response.mix[planta]);
            });
        }
        ;
    }, 'json');

}

function muestraDetallePdn() {

//    var url = $('.siic-ul-reportes').find('.siic-selected').attr('url');

    if (typeof php != 'undefined') {

        var request = {
            f: 'muestraDetallePdn',
            fec1: $('#fec1').val(),
            fec2: $('#fec2').val()
//            ,sesion: sessionStorage.getItem('nomSesion'),
//            usuario: sessionStorage.getItem('userSesion')
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
                        revisaPermisos();
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
        f: 'llenaInventarios'
    };

    setTimeout(function () {
        $.get(php, request, function (response) {
            if (response) {
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


                $.each(response.plantas, function (iPlanta, planta) {
                    $("#" + planta.substring(0, 1) + "PU100").html(response.invActual[planta]["P"]);
                    $("#" + planta.substring(0, 1) + "CU100").html(response.invActual[planta]["C"]);
                    $("#" + planta.substring(0, 1) + "IU100").html(response.invActual[planta]["I"]);
                    $("#TOTPU100").html(response.invActual["P"]["total"]);
                    $("#TOTCU100").html(response.invActual["C"]["total"]);
                    $("#TOTIU100").html(response.invActual["I"]["total"]);
                    $("#" + planta.substring(0, 1) + "TOTU100").html(response.invActual[planta]["total"]);
                    $("#TOTTOTU100").html(response.invActual["total"]);

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

                    revisaPermisos();
                });
            }
            ;
        }, 'json');
    }, 1000);
}
function buscarCliente() {
    var php = "php/functions.php";
    var request = {
        f: 'buscarCliente',
        q: $('#buscarnombre').val()};

    if (request.q != '') {
        $.get(php, request, function (response) {
            if (response) {
                $('#clientes').empty();
                var html = '';
                $.each(response.data, function (idx, item) {
                    html =
                            '<div class="mdl-shadow--2dp mdl-cell mdl-cell--top siic-buscar--cliente">' +
                            '<i class="mdl-color-text--blue-900 material-icons">person</i>' +
                            '<div class="siic-buscar--cliente-item">' +
                            '<div class="siic-buscar--cliente-nom">' + item.NomCliente + '</div>' +
                            '<div class="siic-buscar--cliente-clave">' + item.CveCliente + '</div>' +
                            '<div class="siic-buscar--cliente-det">' + item.Determinante + '</div>' +
                            '<div class="siic-buscar--cliente-nomdet">' + item.NomDeterminante + '</div>' +
                            '<div class="siic-buscar--cliente-nomdet">' + item.Ciudad + '</div>' +
                            '</div>' +
                            '</div>';
                    $('#clientes').append(html);
                });
                $('#clientes').find('.siic-buscar--cliente').click(function (e) {
                    var clave = $(this).find('.siic-buscar--cliente-clave').text();
                    $('#cliente')
                            .val(clave)
                            .parent().addClass('is-dirty');
                });
            }
            ;

        }, 'json');

    }
    ;

}
function creaGrafico(cual, porciento) {
    var mychart = new AwesomeChart(cual);
    var dif = (100 - parseFloat(porciento));
    mychart.data = [parseFloat(porciento), dif];
    mychart.chartType = 'doughnut';
    mychart.colors = [colores[cual], '#eeeeee'];
    mychart.draw();
}

function graficoscanal(response) {

    $('#graficos').empty();
    if (response) {
        $.each(response, function (i, item) {
            var html;
            html =
                    '<div class="chart-container">' +
                    '<h5>' + item.Zona + '</h5>' +
                    '<h5>' + item.LitrosPercent + '%</h5>' +
                    '<canvas id="' + item.Zona + '" height="100" width="100"></canvas>' +
                    '<h5>' + parseFloat(item.Litros).toLocaleString("es-MX") + '</h5>' +
                    '</div>';
            $('#graficos').append(html);
            creaGrafico(item.Zona, item.LitrosPercent);
        });
    }
}

function graficosmes(response) {

    $('#graficosmes').empty();

    if (response) {
        $.each(response, function (i, item) {
            var html;
            html =
                    '<div class="chart-container">' +
                    '<h5>' + item.Zona + '</h5>' +
                    '<h5>' + item.LitrosPercent + '%</h5>' +
                    '<canvas id="mes-' + item.Zona + '" height="100" width="100"></canvas>' +
                    '<h5>' + parseFloat(item.Litros).toLocaleString("es-MX") + '</h5>' +
                    '</div>';

            $('#graficosmes').append(html);
            creaGrafico('mes-' + item.Zona, item.LitrosPercent);
        });
    }
}

function pad2(number) {
    return (number < 10 ? '0' : '') + number;
}

function topclientes(response) {

    $('#topclientes').empty();

    if (response) {
        $.each(response, function (i, item) {
            var html;
            html =
                    '<div class="siic-ul-topclientes__item mdl-cell mdl-cell--top">' +
                    '<div class="siic-ul-topclientes__item--cliente" style="border-left-color: ' + colores[item.Z] + ';">' +
                    '<div class="siic-ul-topclientes__item--cliente-nombre">' + item.NomCliente + '</div>' +
                    '<div class="siic-ul-topclientes__item--cliente-canal">' + item.Z + '</div>' +
                    '<div class="siic-ul-topclientes__item--cliente-litros">' + parseFloat(item.Litros).toLocaleString('es-MX') + ' Lts</div>' +
                    '</div>' +
                    '</div>';
            $('#topclientes').append(html);
        });
    }

}
function pandl() {
    var request = {
        f: 'muestraResumenPdn',
        fec1: $('#fec1').val(),
        fec2: $('#fec2').val()
    };
    $.get(php, request, function (response) {
        if (response) {
            $('.pdnInput').empty();
            $.each(response.plantas, function (iPlanta, planta) {
                $("#" + planta.substring(0, 1) + "tyConsumption").html(response.ty[planta][0]);
                $("#" + planta.substring(0, 1) + "tyConsumptionL").html(response.ty[planta][2]);
                $("#" + planta.substring(0, 1) + "tyL").html(response.ty[planta][1]);
                $("#" + planta.substring(0, 1) + "tyLAVG").html(response.ty[planta][3]);
                $("#" + planta.substring(0, 1) + "tyAgua").html(response.agua[planta]);
                $("#" + planta.substring(0, 1) + "tyShare").html(response.ty[planta][4]);
            });
            revisaPermisos();
        }
        ;
    }, 'json');
}
function dimeVentas() {
    var tipo = "barVertOverlapped";
    var request = {
        f: 'dimeVentas',
        fec1: $('#fec1Vtas').val(),
        fec2: $('#fec2Vtas').val()
    };

    $.get(php, request, function (response) {
        if (response) {
            var series = [];
            $.each(response.series, function (iPlanta, planta) {
                series
            });
            $('#chartVtas').gchart({type: 'barVertOverlapped',
                dataLabels: response.labels, legend: 'right',
                series: [
                    response.series
//                    $.gchart.series('TAMBOR', [44.1, 38.7, 31.8], 'blue'),
//                    $.gchart.series('TAMBOR 208', [55, 38.7, 28], '#c6db8a'),
//                    $.gchart.series('BIDÓN 20L', [60.1, 20, 18], '#669ac3')
                ]
            });
//            var ctx = document.getElementById("myChart").getContext("2d");
//            var myRadarChart = new Chart(ctx).Bar(data, {
//                pointDot: false
//            });
//            myRadarChart.update();
            revisaPermisos();
        }
        ;
    }, 'json');


}