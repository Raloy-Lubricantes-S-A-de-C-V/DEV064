$(document).ready(function () {
    getRate("USD", "MXN");
    getRate("EUR", "USD");
//    $("#buttonsTiposdeCambio").html("<a id='modificar'>Modificar</a><span style='width:20px;'>|</span><a id='restablecer'>Restablecer</a>");

//    $("#modificar").click(function () {
//        if ($(this).html() === "Modificar") {
//            enableUpdate($(this));
//        } else {
//            disableUpdate($(this));
//        }
//    });

//    $("#restablecer").click(function () {
//        window.location.reload();
//    });

    setTimeout(function () {
        zk_costosU100("zk_costosU100_Init");
    }, 600);
});

function getRate(from, to) {
    var script = document.createElement('script');
    script.setAttribute('src', "http://query.yahooapis.com/v1/public/yql?q=select%20rate%2Cname%20from%20csv%20where%20url%3D'http%3A%2F%2Fdownload.finance.yahoo.com%2Fd%2Fquotes%3Fs%3D" + from + to + "%253DX%26f%3Dl1n'%20and%20columns%3D'rate%2Cname'&format=json&callback=parseExchangeRate");
    document.body.appendChild(script);
}

function parseExchangeRate(data) {
    var name = data.query.results.row.name;
    var fecha = data.query.results.row.time;
    var rate = parseFloat(data.query.results.row.rate, 10);
    var nameSpan = name.replace("/", "_");
    $("#tiposdeCambio").append(
            "<span>"
            + "<a href='http://finance.yahoo.com' target='_blank'>"
            + name
            + "</a>: <input disabled='disabled' type='text' class='tiposdeCambioInput' id='sp" + nameSpan + "' value='" + rate + "'/>"
            + "</span>");
}
function enableUpdate(obj) {
    $("#tiposdeCambio input").removeAttr("disabled").addClass("updateable");
    obj.html("Aceptar").click(function () {
        disableUpdate(obj);
    });
}

function disableUpdate(obj) {
    $("#tiposdeCambio input").attr("disabled", "disabled").removeClass("updateable");
    obj.html("Modificar").click(function () {
        enableUpdate(obj);
    });
    $("#mainTable").dataTable().destroy();
    zk_costosU100("zk_costosU100_Init");
}

function zk_costosU100(fase) {
    $("#loadingDiv").show();
    var file = "php/functionsCostoU100.php";
    var param = {
        fase: "creaQueries",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val(),
        tcE: $("#spEUR_USD").val(),
        tcP: $("#spUSD_MXN").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            var param = {
                fase: "muestraDatos"
            };
            $.get(file, param, function (proceso) {
                var h,searching;
                $.each(proceso, function (index, value) {
                    if (value.status === 1) {
                        if(value.tabla==="Detalle"){
                            h="220px";
                            searching=true;
                        }else{
                            h=true;
                            searching=false;
                        }
                        $("#tf"+value.tabla).attr("colspan", eval(value.conteo));
                        $("#title"+value.tabla).html(value.title);
                        
                        //tabla de datos
                        $("#tbl" + value.tabla).dataTable({
                            dom: 'Bfrtip',
                            buttons: [
                                'copyHtml5',
                                'excelHtml5',
                                'pdfHtml5'
                            ],
                            scrollY: h,
                            "scrollX": true,
                            caption: value.title,
                            data: value.data,
                            columns: value.columns,
                            "paging": false,
                            searching:searching,
                            "aoColumnDefs": [
                                {"sClass": "numeric", "aTargets": value.numericCols}, //añadir clase de numeric a las columnas definidas en functions.php
                                {"sClass": "currency", "aTargets": value.currencyCols} //añadir clase de numeric a las columnas definidas en functions.php
                            ],
                            "bSort": false,
                            "footerCallback": function (tfoot, data, start, end, display) {
                                var api = this.api();

                                // Remove the formatting to get integer data for summation
                                var intVal = function (i) {
                                    return typeof i === 'string' ?
                                            i.replace(/[\$,]/g, '') * 1 :
                                            typeof i === 'number' ?
                                            i : 2;
                                };

                                // Total over all pages
                                total = api
                                        .column(value.sumCol)
                                        .data()
                                        .reduce(function (a, b) {
                                            return intVal(a) + intVal(b);
                                        }, 0);

                                // Total over this page
                                pageTotal = api
                                        .column(value.sumCol, {page: 'current'})
                                        .data()
                                        .reduce(function (a, b) {
                                            return intVal(a) + intVal(b);
                                        }, 0);
                                var numFormat = $.fn.dataTable.render.number('\,', '.', 3, '').display;
                                // Update footer
                                $(api.column(value.sumCol).footer()).html(
                                        'Total mostrado:$' + numFormat(pageTotal) + ' / Total General:$(' + numFormat(total) + ')'
                                        );
                            }

                        });
                        $('.numeric').text(function () {
                            if ($.isNumeric($(this).html())) {
                                $(this).parseNumber({format: "#,##0", locale: "us"});
                                $(this).formatNumber({format: "#,##0", locale: "us"});
                            }
                        }
                        );
                        $('.currency').text(function () {
                            if ($.isNumeric($(this).html())) {
                                $(this).parseNumber({format: "$#,##0.00#", locale: "us"});
                                $(this).formatNumber({format: "$#,##0.00#", locale: "us"});
                            }
                        }
                        );
                    }

                });

            }, "json")
                    .done(function () {
                        $("#loadingImg").hide();
                        $("#loadingMsg").html("Completado");
                        setTimeout(function () {
                            $("#loadingDiv").hide();
                        }, 1200);
                    })
                    .error(function () {
                        $("#loadingImg").hide();
                        $("#loadingMsg").html("Tiempo de espera excedido");
                    });
        } else {
            alert(proceso.error);
        }

    }, "json"
            )
            .done(function () {
                $("#loadingMsg").html("Mostrando reportes");
            })
            .error(function () {
                $("#loadingImg").hide();
                $("#loadingMsg").html("Tiempo de espera excedido");
            });
}
