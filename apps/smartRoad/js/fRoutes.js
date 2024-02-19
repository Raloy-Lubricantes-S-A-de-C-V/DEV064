var file = "php/fIndex.php";
var serverdata = "";
$(document).ready(function() {
    updateEvents()
    limpiaSolCarga()
    updateAll(function() {
        console.log("Updated")
    })
    updatePedidos()
});

function updateEvents() {
    limpiaSolCarga();
    //Papeleta (solicitud de carga)
    $("#closePapeleta")
        .on("click", function() {
            limpiaSolCarga();
        });
    $("#btnSaveSolCarga").click(function() {
        generaSolCarga();
    });
    $("#fechaCarga").change(function() {
        selAtqs();
    });

    $("#fechaCarga").datepicker({ dateFormat: 'yy-mm-dd', minDate: 0 });
    $("#fechaRegreso").datepicker({ dateFormat: 'yy-mm-dd', minDate: 0 });

    $("#btnUpdate").click(limpiaSolCarga);
    //Pedidos y ruteo
    $("#btnLlamaPedidos").click(function() {
        $(".modalContainer").hide();
        $(this).prop("disabled", true);
        $("#divPedidos").show(500);
    });
    $("#closePedidos")
        .on("click", function() {
            $(".modalContainer").hide(500);
            $("#btnLlamaPedidos").prop("disabled", false);
        });
    $('#btnSavePedidos')
        .on('click', function() {
            guarda_pre_ruteo();
        });
    $('#btnUpdPedidos')
        .on('click', function() {
            //    dimePedidosPendientes_fuentes();
            dimePedidosPendientes_fromLog();
        });

    //poner en camino
    $("#closePonerEnCamino").unbind("click").click(
        function() {
            ponerEnCamino_limpiarForm();
        });

    //Concluir envíos
    $("#closeConcluirEnvio")
        .on("click", function() {
            $(".modalContainer").hide(500);
            $("#concEnviIdEntrega").val("");
        });

    $("#odomFin,#ltsDiesel,#odomInicio").change(function() {
        var odomFinal = ($("#odomFin").val() > 0) ? $("#odomFin").val() : 0;
        var odomInicio = ($("#odomInicio").val() > 0) ? $("#odomInicio").val() : 0;
        var ltsDiesel = ($("#ltsDiesel").val() > 0) ? $("#ltsDiesel").val() : 0;
        var kmsRecorrido = eval(odomFinal - odomInicio);
        var kms_lt = eval(kmsRecorrido / ltsDiesel);
        $("#kmsRecorr").val(kmsRecorrido);
        $("#rendimiento").val(kms_lt);
    });
    $("#odomFinE,#ltsDieselE,#odomInicioE").change(function() {
        var odomFinal = ($("#odomFinE").val() > 0) ? $("#odomFinE").val() : 0;
        var odomInicio = ($("#odomInicioE").val() > 0) ? $("#odomInicioE").val() : 0;
        var ltsDiesel = ($("#ltsDieselE").val() > 0) ? $("#ltsDieselE").val() : 0;
        var kmsRecorrido = eval(odomFinal - odomInicio);
        var kms_lt = eval(kmsRecorrido / ltsDiesel);
        $("#kmsRecorrE").val(kmsRecorrido);
        $("#rendimientoE").val(kms_lt);
    });

    //Botón en formulario para guardar el envío de pipas propias
    $("#concluyeEnvio").click(function() {
        concluirEnvio_save();
    });
    $(".datosconcluir input").change(function() {
        calculatotales($(this));
    });
}

function updatePedidos() {
    if (typeof(EventSource) !== "undefined") {
        var source = new EventSource("php/findex_sse.php");
        source.onmessage = function(event) {
            var modalAbierto = 0;
            $(".modalContainer").each(function() {
                modalAbierto = ($(this).prop("display") === "block") ? 1 : modalAbierto;
            });
            if (serverdata !== event.data && serverdata !== "" && modalAbierto === 0) {
                var respuesta = JSON.parse(event.data);
                if (respuesta.status === 0) {
                    window.location.reload();
                    return ("false");
                } else {
                    $.each(respuesta.datos, function(i, v) {
                        $(".boxCar[folio='" + v.folio + "']").removeClass("green").removeClass("yellow").removeClass("red").addClass(v.colorClass).find("button[title='Poner En Camino']").removeClass("ponerEnCamino").addClass(v.clase);
                    });
                    $("button[title='Poner En Camino']").off("click");
                    $(".ponerEnCamino").off("click").on("click", function() {
                        // ponerEnCamino($(this));
                        ponerEnCamino_save_inline($(this));
                    }).focus();
                    alert("Nuevas órdenes cargadas");
                }
            }
            serverdata = event.data;
        };
    } else {
        document.getElementById("result").innerHTML = "Sorry, your browser does not support server-sent events...";
    }
    //    dimePedidosPendientes_fuentes();
    dimePedidosPendientes_fromLog();
    dimePrecioDiesel();
}

function changeEta(ipr, old_eta, new_eta, $obj) {
    var param = {
        fase: "changeEta",
        old_eta: old_eta,
        new_eta: new_eta,
        ipr: ipr,
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status == 1) {
            $obj.attr("curr_eta", param.new_eta)
            $obj.css("background", "#cdfbdd")
        } else {
            alert(proceso.error)
        }
    }, "json")
}

function setCartaPorte($obj) {
    var param = {
        fase: "setCartaPorte",
        carta_porte: $obj.val(),
        id_entrega: $obj.attr("id_entrega"),
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status == 1) {
            $obj.css("background", "#cdfbdd")
        } else {
            alert(proceso.error)
        }
    }, "json")
}

function espejocosto(obj) {
    var totalLts = $.parseNumber($("#ltsembarqueconc").html(), { format: "#,##0.00", locale: "us" });
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
    $("#" + idespejo).html(valor).formatNumber({ format: format, locale: "us" });
    $("#" + idespejou).html(valorunit).formatNumber({ format: "$#,##0.000", locale: "us" });
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
    $(".cft").each(function() {
        var valorf = $.parseNumber($(this).html(), { format: "#,##0", locale: "us" });
        totalF += (eval(valorf) > 0) ? eval(valorf) : 0;
    });
    $(".cvt").each(function() {
        var valorv = $.parseNumber($(this).html(), { format: "#,##0", locale: "us" });
        totalV += (eval(valorv) > 0) ? eval(valorv) : 0;
    });
    total = eval(totalF) + eval(totalV);
    //Totales unitarios
    var totalLts = $.parseNumber($("#ltsembarqueconc").html(), { format: "#,##0", locale: "us" });
    if (totalLts > 0 && total > 0) {
        totalFUnitario = eval(totalF) / eval(totalLts);
        totalVUnitario = eval(totalV) / eval(totalLts);
        totalUnitario = eval(total) / eval(totalLts);
    }
    $("#totalF").html(totalF).formatNumber({ format: "$#,##0.00", locale: "us" });
    $("#totalFUnitario").html(totalFUnitario).formatNumber({ format: "$#,##0.000", locale: "us" });;
    $("#totalV").html(totalV).formatNumber({ format: "$#,##0.00", locale: "us" });;
    $("#totalVUnitario").html(totalVUnitario).formatNumber({ format: "$#,##0.000", locale: "us" });;
    $("#costototP").html(total).formatNumber({ format: "$#,##0.00", locale: "us" });;
    $("#costototuP").html(totalUnitario).formatNumber({ format: "$#,##0.000", locale: "us" });;
}

function calculapeso() {
    var pesosincarga = ($("#pesosincarga").val() > 0) ? $("#pesosincarga").val() : 0;
    var pesodelacarga = ($("#pesocarga").val() > 0) ? $("#pesocarga").val() : 0;
    var pesototal = eval(pesosincarga) + eval(pesodelacarga);
    $("#pesobruto").html(pesototal).formatNumber({ format: "#,##0.00", locale: "us" });
}

function calculacostodiesel() {
    var ltsdiesel = $("#ltsDiesel").val();
    var preciodiesel = $("#preciodieselsiniva").val();
    var costodiesel = 0;
    var dieselunit = 0;
    if (ltsdiesel > 0 && preciodiesel > 0) {
        costodiesel = Math.round(ltsdiesel * preciodiesel * 100) / 100;
    }
    var totalLts = $.parseNumber($("#ltsembarqueconc").html(), { format: "#,##0.00", locale: "us" });
    if (totalLts > 0 && costodiesel > 0) {
        dieselunit = Math.round((eval(costodiesel) / eval(totalLts)) * 1000) / 1000;
    }
    $("#resdiesel").html(costodiesel).formatNumber({ format: "$#,##0.00", locale: "us" });;
    $("#resdieselu").html(dieselunit).formatNumber({ format: "$#,##0.000", locale: "us" });;
}

function calculafijos() {
    var diasruta = $.parseNumber($("#restiemporuta").html(), { format: "#,##0.00", locale: "us" });
    var kmsruta = $.parseNumber($("#kmsRecorrP").html(), { format: "#,##0", locale: "us" });
    var totalLts = $.parseNumber($("#ltsembarqueconc").html(), { format: "#,##0.00", locale: "us" });
    if (eval(diasruta) > 0) {
        $(".cft").each(function() {
            var costo = 0;
            if ($(this).hasClass("prmes")) {
                costo = (eval($(this).attr("montomensual")) / 30.4) * eval(diasruta);
            } else {
                costo = (eval($(this).attr("monto100kms")) / 100) * eval(kmsruta);
            }
            $(this).html(costo).formatNumber({ format: "$#,##0.00", locale: "us" });
            if (totalLts > 0 && costo > 0) {
                var costounit = eval(costo) / eval(totalLts);
                $(this).parent().find(".cfu").html(costounit).formatNumber({ format: "$#,##0.000", locale: "us" });
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
    $("#kmsRecorrP").html(kmsrec).formatNumber({ format: "#,##0", locale: "us" });;
    calcularendkml();
}

function calcularendkml() {
    var kmsrec = $.parseNumber($("#kmsRecorrP").html(), { format: "#,##0", locale: "us" });
    var ltsdiesel = $("#ltsDiesel").val();
    var rendkml = 0;
    if (kmsrec > 0 && ltsdiesel > 0) {
        rendkml = Math.round((eval(kmsrec) / eval(ltsdiesel)) * 1000) / 1000;
    }
    $("#rendkmlP").html(rendkml).formatNumber({ format: "#,##0.00", locale: "us" });;
}

function dimePedidosPendientes_fuentes() {
    console.log("Orders Retrieved From Source");
    $.get("../../crons/cronPedidosSinRuteo.php", function(data) {
        console.log("Carga de pedidos a SmartRoad:" + data);
    });
    $('#tblPedidos tbody').html("");
    $.get("php/pedidosFuentes.php", function(respuesta) {
        $('#tblPedidos tbody').html(respuesta);
        var table = $('#tblPedidos').DataTable({
            "order": [
                [19, 'asc'],
                [9, 'asc']
            ],
            "columnDefs": [{
                "targets": 1,
                "orderable": false
            }],
            destroy: true,
            "scrollX": true,
            scrollY: '50vh',
            scrollCollapse: true,
            paging: false,
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;
                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };
                // Total over all pages
                total = api
                    .column(18)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                pageTotal = api
                    .column(18, { page: 'current' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
            }
        });
        $(".inputAtq").change(function() {
            cambiaColorAtq($(this));
        });
        $(".inputEta").datepicker({ dateFormat: "yy-mm-dd" });
        $("#fechCarga").datepicker({ dateFormat: "yy-mm-dd" });
        cambiaColorAtq();
        $(".inputsPed").on("change", function() {
            $("#divPedidos").find(".avisos").html("Cambios sin guardar").show();
        });
    });
}

function dimePedidosPendientes_fromLog() {
    console.log("Actualizando...")
    $("#btnLlamaPedidos").html("Actualizando...").prop("disabled", true);
    $("#btnUpdPedidos").html("Actualizando...").prop("disabled", true);
    actualizaFuentes(dimePedidosPendientes_fromLog_sync)
}

function actualizaFuentes(_callback) {
    console.log("Sincronizando Fuentes...")
    $("#btnLlamaPedidos").html("Sincronizando...")
    $("#btnUpdPedidos").html("Sincronizando...")
        // $.get("../../crons/cronPedidosSinRuteo.php", (json) => {
    $.get("../../ws/consolidaFuentes.php", function(data) {
        console.log("Carga de pedidos a SmartRoad");
        console.log(data);
    }, "json").done(function() {
        console.log("Carga terminada");
        _callback();
    });
}



function dimePedidosPendientes_fromLog_sync() {
    console.log("Obteniendo datos...")
    $('#tblPedidos tbody').html("")
    $.get("php/pedidosFromLog.php", function(respuesta) {
        $('#tblPedidos tbody').html(respuesta);
        var table = $('#tblPedidos').DataTable({
            "order": [
                [9, 'des'],
                [19, 'des']
            ],
            "columnDefs": [{
                "targets": 1,
                "orderable": false
            }],
            destroy: true,
            "scrollX": true,
            scrollY: '72vh',
            scrollCollapse: true,
            paging: true,
            "pageLength": 250,
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;
                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };
                // Total over all pages
                total = api
                    .column(18)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                pageTotal = api
                    .column(18, { page: 'current' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
            }
        });
        $(".inputAtq").change(function() {
            cambiaColorAtq($(this));
        });
        $(".inputEta").datepicker({ dateFormat: "yy-mm-dd" });
        $("#fechCarga").datepicker({ dateFormat: "yy-mm-dd" });
        cambiaColorAtq();
        $(".inputsPed").on("change", function() {
            $("#divPedidos").find(".avisos").html("Cambios sin guardar").show();
        });
    }).done(function() {
        console.log("Datos Actualizados");
        $("#btnLlamaPedidos").html("<i class='fa fa-search'></i>  Pedidos").prop("disabled", false);
        $("#btnUpdPedidos").html("<i class='fa fa-search'></i>  Actualizar").prop("disabled", false);
    });
}


function get_data_from_source(sources_json, cb) {
    var data = {}

    $.each(sources_json.pedidos, (i, v) => {
        $.ajax({
            url: v.url,
            type: "GET",
            crossDomain: true,
            headers: { 'Access-Control-Allow-Origin': 'http://www.zar-kruse.com' },
            data: JSON.stringify(v.data),
            dataType: "json",
            success: function(response) {
                if (v.response_var_name) {
                    data[i] = response[v.response_var_name]
                } else {
                    data[i] = response
                }
            },
            error: function(xhr, status) {
                console.log("error");
            }
        });
    })
    cb(data)
}

function buscapedidocerrado() {
    //    $(".btnCloseModal").click();
    $(".modalContainer").hide();
    $("#divPedidos").show(500);
    var param = {
        fase: "buscapedidocerrado",
        pedidoRaloy: $("#pedidocerradoRaloy").val(),
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        $('#tblPedidos').html(proceso.table);



        var table = $('#tblPedidos').DataTable({
            "order": [
                [0, 'asc'],
                [4, 'asc'],
                [5, 'asc']
            ],
            "columnDefs": [{
                "targets": 1,
                "orderable": false
            }],
            destroy: true,
            "scrollX": true,
            scrollY: '50vh',
            scrollCollapse: true,
            paging: false,
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;
                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };
                // Total over all pages
                total = api
                    .column(18)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                pageTotal = api
                    .column(18, { page: 'current' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                //                        $(api.column(0).footer()).html(
                //                                'Subtotal:' + pageTotal + ' / Total:' + total + ' Lts Pendientes)' + proceso.subtots
                //                                );
            }
        });
        $(".inputAtq").change(function() {
            cambiaColorAtq($(this));
        });
        $(".inputEta").datepicker({ dateFormat: "yy-mm-dd" });
        $("#fechCarga").datepicker({ dateFormat: "yy-mm-dd" });


        cambiaColorAtq();
        $(".inputsPed").on("change", function() {
            $("#divPedidos").find(".avisos").html("Cambios sin guardar").show();
        });

    }, "json");
}

function boxesRuteados() {
    var param = {
        fase: "boxesRuteados",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            console.log(proceso);
            return;
        } else {
            //En ruteo
            construyeBoxesRuteo(proceso.data);
        }

    }, "json").done(function() {

        $(".inputInlineCartaPorte").change(function() {
            setCartaPorte($(this))
        })

        $(".changeEtaInput").off("change").on("change", function() {
            var ipr = $(this).attr("ipr")
            var old_eta = $(this).attr("curr_eta")
            var date = new Date($(this).val());
            var day = date.getUTCDate();
            var month = date.getUTCMonth() + 1;
            var year = date.getUTCFullYear()
            if (month <= 9)
                month = '0' + month

            if (day <= 9)
                day = '0' + day

            var new_eta = year + "-" + month + "-" + day
            changeEta(ipr, old_eta, new_eta, $(this))
            alert("listo")
            return false;
        })

    });
}

function boxesCarga() {
    $('#contenedorCargas').html("Obteniendo rutas en carga <i class='fa fa-spin fa-spinner'></i>");
    var param = {
        fase: "boxesCarga",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            console.log(proceso);
            return;
        } else {
            //En Carga
            $('#contenedorCargas').html(proceso.data);

        }

    }, "json").done(function() {

        //En Carga
        $(".consultar").click(function() {
            window.open("solicitudCarga.php?folio=" + $(this).parent().attr("identrega"));
        });
        $(".ponerEnCamino").off("click").on("click", function() {
            // ponerEnCamino($(this));
            ponerEnCamino_save_inline($(this));
        });
        $(".cancelarCarga").click(function() {
            cancelarCarga($(this).parent().attr("identrega"));
        });

        //others
        $(".inputInlineCartaPorte").change(function() {
            setCartaPorte($(this))
        })

        $(".changeEtaInput").off("change").on("change", function() {
            var ipr = $(this).attr("ipr")
            var old_eta = $(this).attr("curr_eta")
            var date = new Date($(this).val());
            var day = date.getUTCDate();
            var month = date.getUTCMonth() + 1;
            var year = date.getUTCFullYear()
            if (month <= 9)
                month = '0' + month

            if (day <= 9)
                day = '0' + day

            var new_eta = year + "-" + month + "-" + day
            changeEta(ipr, old_eta, new_eta, $(this))
            alert("listo")
            return false;
        })
    });

}

function boxesCamino() {
    $('#contenedorEnvios').html("obteniendo rutas en camino <i class='fa fa-spin fa-spinner'></i>");
    var param = {
        fase: "boxesCamino",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            console.log(proceso);
            return;
        } else {

            //En ruteo
            // construyeBoxesRuteo(proceso.enRuteo);
            //En Carga
            // $('#contenedorCargas').html(proceso.enCarga);
            // $(".consultar").click(function() {
            //     window.open("solicitudCarga.php?folio=" + $(this).parent().attr("identrega"));
            // });
            // $(".ponerEnCamino").off("click").on("click", function() {
            //     // ponerEnCamino($(this));
            //     ponerEnCamino_save_inline($(this));
            // });
            // $(".cancelarCarga").click(function() {
            //     cancelarCarga($(this).parent().attr("identrega"));
            // });

            //en Camino
            $('#contenedorEnvios').html(proceso.data);
            $(".consultarCert").click(function() {
                window.open("certsCal.php?folio=" + $(this).parent().attr("identrega"));
            });
            $(".concluirEnvio_pro").off("click").on("click", function() {
                var obj = $(this).parent();
                $("#divConcluirEnvio").show(500, function() {
                    $("#tipoenvio").html("Envío con transporte propio");
                    concluirEnvio_form(obj);
                });
            });
            $(".concluirEnvio_ext").off("click").on("click", function() {
                var obj = $(this).parent();
                $("#divConcluirEnvio").show(500, function() {
                    $("#tipoenvio").html("Envío con transporte externo");
                    concluirEnvio_form(obj);
                });
            });
            $(".cancelarEnvio").off("click").on("click", function() {
                cancelarEnvio($(this).parent());
            });
        }

    }, "json").done(function() {

        $(".inputInlineCartaPorte").change(function() {
            setCartaPorte($(this))
        })

        $(".changeEtaInput").off("change").on("change", function() {
            var ipr = $(this).attr("ipr")
            var old_eta = $(this).attr("curr_eta")
            var date = new Date($(this).val());
            var day = date.getUTCDate();
            var month = date.getUTCMonth() + 1;
            var year = date.getUTCFullYear()
            if (month <= 9)
                month = '0' + month

            if (day <= 9)
                day = '0' + day

            var new_eta = year + "-" + month + "-" + day
            changeEta(ipr, old_eta, new_eta, $(this))
            alert("listo")
            return false;
        })
    });
}

function construyeBoxesRuteo(data) {
    var arrColores = ["#fff", "#daa520", "#00688b", "#8b0a50", "#2796ea", "#f9c2d1", "#dbe1e7", "#f37735", "#ffc425", "#800080", "#fef65b", "#000080", "#ccff00"];
    $("#contenedorBoxes").html("");
    $.each(data.keys, function(i, k) {
        var div = $("<div></div>").addClass("boxAll").attr("iprs", data.iprs[k]).appendTo("#contenedorBoxes");
        var spanTag = "<span atq='" + k + "' class='subtots'><span class='cardIcon' style='color:" + arrColores[k] + "'><i class='fa fa-tag'></i> " + k + "</span></span>"
        var spanLts = "<span class='ltsBox'>" + data.lts[k] + " L</span>";
        $("<div></div>").addClass("left").html("<div>" + spanTag + "</div><div>" + spanLts + "</div>").appendTo(div);
        var rightDiv = $("<div></div>").addClass("right").appendTo(div);
        $.each(data.boxes[k], function(i, arr) {
            var container = $("<div></div>").css({ "padding": "5px", "box-sizing": "border-box", "width": "100%", "float": "left", "border-top": "solid 1px #d9dadb" }).appendTo(rightDiv);
            var spanLtsTag = "<span class='tagLtsSurtir'>" + arr.lts + "</span>";
            $("<span></span>").addClass("tagProdCte").html(arr.fuente + " " + arr.cliente + " " + arr.pedido + " " + arr.cveProd).appendTo(container);
            $("<span></span>").addClass("tagEtayLts").html(arr.eta + " " + spanLtsTag + ":").appendTo(container);
            $("<span></span>").addClass("tagUbic").html(arr.mpio + ", " + arr.edoCor).appendTo(container);
        });

        $("<br/>").css("clear", "both").appendTo(div);
        var divBotones = $("<div></div>").addClass("ctrBoxRuteo").appendTo(div);
        var buttonEliminar = "<button title='Eliminar Ruta' class='cancelarRuteo'><i class='fa fa-trash'></i></button>";
        var buttonConfirmar = "<button title='Confirmar Ruta' class='ponerEnCarga'><i class='fa fa-check'></i></button>";
        $("<div></div>")
            .addClass("boxRuteoBtns")
            .attr("iprs", data.iprs[k])
            .html(buttonEliminar + " " + buttonConfirmar)
            .appendTo(divBotones);
    });
    $(".ponerEnCarga").unbind("click").click(function() {
        previsualizarPapeleta($(this).parent());
    });
    $(".cancelarRuteo").unbind("click").click(function() {
        delete_from_pre_ruteo_boxes($(this).parent());
    });
}

function cancelarEnvio(obj) {
    var idEntrega = $(obj).attr("identrega");

    var confirm = window.confirm("Por favor confirme que desea CANCELAR EL ENVÍO FOLIO: " + idEntrega + "\nEsta acción pondrá el envío en CARGA.");
    if (confirm === false) {
        return;
    } else {
        var param = {
            fase: "cancelarEnvio",
            idEntrega: idEntrega,
            t: sessionStorage.getItem("token")
        };
        console.log(param);
        $.get(file, param, function(proceso) {
            if (proceso.status !== 1) {
                alert(proceso.error);
                console.log(proceso);
                return;
            } else {
                actualizaTodo();
            }
        }, "json");
    }

}

function delete_from_pre_ruteo_boxes(obj) {
    var param = {
        fase: "delete_from_pre_ruteo",
        strToDel: $(obj).attr("iprs"),
        t: sessionStorage.getItem("token")
    };
    console.log(param);
    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            console.log(proceso);
            return;
        } else {
            window.location.reload();
        }

    }, "json");
}

function guarda_pre_ruteo() {
    var arr_strValues = new Array();
    var arr_idsToDel = new Array();
    var hayNoStd = false;

    $("#tblPedidos tbody").children("tr").each(function(i, val) {
        var iddetSmartRoad = $(this).attr("idDet");
        var iddetorigen = $(this).attr("id_det_origen");
        var fuentepedido = $(this).attr("fuentepedido");
        var tds = $(val).children("td");

        if ($(tds[0]).html() !== "NA" && $(tds[6]).html() === "") {
            hayNoStd = true;
        }
        if ($(tds[1]).find("input").val() === "NA") {
            arr_idsToDel.push($(val).attr("idpr"));
        }

        if ($(tds[0]).html() !== "NA" && $(tds[0]).html() !== "Z" && $(tds[6]).html() !== "" && isNaN($(tds[1]).find("input").val()) === false && $(tds[1]).find("input").val() > 0) {

            var numCamion = $(tds[1]).find("input").val();
            var ltsSurtir = $(tds[2]).find("input").val();
            var eta = "'" + $(tds[3]).find("input").val() + "'";
            var causa_cambio_fecha = "'" + $(tds[4]).find("input").val() + "'";
            var idEM = $(tds[6]).attr("idEM");
            var pedido = "'" + $(tds[7]).html() + "'";
            var fechaPedido = "'" + $(tds[8]).html() + "'";
            var fechaCompromiso = "'" + $(tds[9]).html() + "'";
            var cveCliente = "'" + $(tds[10]).html() + "'";
            var cliente = "'" + $(tds[11]).html() + "'";
            var destino = "'" + $(tds[12]).html() + "'";
            var cveProd = "'" + $(tds[13]).html() + "'";
            var nombreProd = "'" + $(tds[14]).html() + "'";
            var estado = "'" + $(tds[15]).html() + "'";
            var ciudad = "'" + $(tds[16]).html() + "'";
            var ltsPedido = $(tds[17]).html();
            var albaran = "'" + $(tds[19]).html() + "'";
            var ventas = "'" + $(tds[20]).html() + "'";
            var estadoRemision = "'" + $(tds[21]).html() + "'";
            var soid = "'" + $(tds[22]).html() + "'";
            var user = "'" + $("#userSession").html() + "'";

            if (isNaN(numCamion) === true) {
                alert("Sin número de camión");
                return false;
            } else if (isNaN(ltsSurtir) === true) {
                alert("sin cantidad a surtir");
                return false;
            } else if (eta === "''") {
                alert("Introduzca una fecha de entrega correcta");
                return false;
            } else if (hayNoStd === true) {
                alert("Hay valores sin estandarizar. Pedido:" + pedido);
                return false;
            } else {
                var str = "";
                str += numCamion + ",";
                str += ltsSurtir + ",";
                str += eta + ",";
                str += causa_cambio_fecha + ",";
                str += idEM + ",";
                str += pedido + ",";
                str += fechaPedido + ",";
                str += fechaCompromiso + ",";
                str += cveCliente + ",";
                str += cliente + ",";
                str += destino + ",";
                str += cveProd + ",";
                str += nombreProd + ",";
                str += estado + ",";
                str += ciudad + ",";
                str += ltsPedido + ",";
                str += albaran + ",";
                str += ventas + ",";
                str += estadoRemision + ",";
                str += soid + ",";
                str += user + ",";
                str += "'" + iddetorigen + "',";
                str += iddetSmartRoad + ",";
                str += "'" + fuentepedido + "'";

                arr_strValues.push("(" + str + ")");
                //                console.log(str);
            }
        }
    });
    var strValues = arr_strValues.join(",");

    //    console.log(strValues);
    //    return false;
    var param = {
        fase: "guarda_pre_ruteo",
        strValues: strValues,
        strToDel: arr_idsToDel.join(","),
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return false;
        }
        if (hayNoStd === true) {
            alert("Los valores no estandarizados no fueron guardados");
        }

    }, "json").done(function() {
        alert("Cambios Guardados");
        actualizaTodo();
        $("#avisos").html("Cambios Guardados").show().fadeOut(5000);
    });

}

function previsualizarPapeleta(obj) {
    $("#inpIprs").val($(obj).attr("iprs"));
    if ($("#inpIprs").val() !== "") {
        var param = {
            fase: "previsualizarPapeleta",
            iprs: $("#inpIprs").val(),
            t: sessionStorage.getItem("token")
        };
        $.get(file, param, function(proceso) {
            if (proceso.status !== 1) {
                alert(proceso.error);
                console.log(proceso);
                return;
            }

            selPlantas();
            $("#datosPapeleta tbody").html(proceso.tbody);
            $("#datosPapeleta tfoot").html(proceso.tfoot);
            $("#ltsSurtirCarga").val(proceso.ltsSurtir);
            $("#placasUnidad").focus();
            $(".modalContainer").hide();
            $("#divPapeleta").show(500);

        }, "json");
    } else {
        $("#closePapeleta").click();
    }
}

function cambiaColorAtq(inputAtq) {
    var arrColores = ["#fff", "#daa520", "#00688b", "#8b0a50", "#2796ea", "#f9c2d1", "#dbe1e7", "#f37735", "#ffc425", "#800080", "#fef65b", "#000080", "#ccff00"];
    if (inputAtq === undefined) { //si no es un cambio individual
        $(".atqNmbr").each(function(i, val) {
            if ($(this).html() !== "NA" && $(this).html() !== "Z" && $(this).html() !== "") {
                $(this).children("i").remove();
                var nmbr = eval($(this).html().trim());
                $(this)
                    .html(nmbr + " <i class='fa fa-circle'></i>")
                    .css("color", arrColores[nmbr]);
            }
        });

    } else { //Cuando se hace un cambio  individual
        var atqNmbrTD = inputAtq.parent().parent().children("td").first();
        atqNmbrTD.html(inputAtq.val() + " <i class='fa fa-circle'></i>").css("color", arrColores[inputAtq.val()]);
    }
    $(".subtots").each(function(i, v) {
        $(this).children("span").first().css("color", arrColores[$(this).attr("atq")]);
    });
}

function generaSolCarga() {
    var iprs = $("#inpIprs").val();
    if ($("#placasUnidad").val() === "" || $("#fechaCarga").val() === "" || isNaN($("#totalLtsCarga").attr("lts")) === true || $("#fechaRegreso").val() === "" || $("#plantaOrigen").val() === "" || $("#plantaRegreso").val() === "") {
        alert("Imposible completar la acción. Revise los datos y vuelva a intentar");
        return;
    } else {
        var param = {
            fase: "generaSolCarga",
            placas: $("#placasUnidad").val(),
            fecha_carga: $("#fechaCarga").val(),
            planta_carga: $("#plantaOrigen").val(),
            fecha_regreso: $("#fechaRegreso").val(),
            planta_regreso: $("#plantaRegreso").val(),
            obs: $("#observacionesCarga").val(),
            usuario: $("#userSession").html(),
            litros: $("#totalLtsCarga").attr("lts"),
            iprs: iprs,
            t: sessionStorage.getItem("token")
        };
        $.get(file, param, function(proceso) {
            //            console.log(proceso);
            if (proceso.status !== 1) {
                alert(proceso.error);
                return;
            }

            window.open("solicitudCarga.php?folio=" + proceso.idNuevo, "_blank");

        }, "json").done(function() {
            limpiaSolCarga();
        });
    }
}

function limpiaSolCarga() {
    $("#inpIprs").val("");
    $("#placasUnidad").val("");
    $("#fechaCarga").val("");
    $("#plantaOrigen").html("");
    $("#fechaRegreso").val("");
    $("#plantaRegreso").html("");
    $("#observacionesCarga").val("");
    $("#placasUnidad").html("");
    $("#datosPapeleta tbody").html("");
    $("#datosPapeleta tfoot").html("");
    $(".modalContainer").hide(500);
    $("#btnSaveSolCarga").prop("disabled", true);
    $("#ltsSurtirCarga").val("");
    actualizaTodo();
}

function selAtqs() {
    var param = {
        fase: "selAtqs",
        fechaCarga: $("#fechaCarga").val(),
        ltsSurtir: $("#ltsSurtirCarga").val(),
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        //        console.log(proceso);
        if (proceso.status === 1) {

            $("#placasUnidad")
                .html(proceso.options)
                .unbind("change")
                .change(function() {
                    if ($(this).val() !== "")
                        $("#btnSaveSolCarga").prop("disabled", false);
                });
        } else if (proceso.status === 2) {
            alert("No hay unidades disponibles con capacidad para entregar esta ruta");
        } else {
            alert(proceso.error);
            return;
        }
    }, "json");
}

function selPlantas() {
    var param = {
        fase: "selPlantas",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        //        console.log(proceso);
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        $(".selPlantas").each(function() {
            $(this).html(proceso.options);
        });
    }, "json");
}

function cancelarCarga(idEntrega) {
    var confirm = window.confirm("Por favor confirme que desea CANCELAR la solicitud de carga FOLIO: " + idEntrega + "\nLa cancelación eliminará también el ruteo.");
    if (confirm === false) {
        return;
    } else {
        var param = {
            fase: "cancelarCarga",
            idEntrega: idEntrega,
            t: sessionStorage.getItem("token")
        };
        $.get(file, param, function(proceso) {
            if (proceso.status !== 1) {
                alert(proceso.error);
                return;
            } else {
                actualizaTodo();
            }
        }, "json");
    }

}

function ponerEnCamino(obj) {
    var idEntrega = $(obj).parent().attr("identrega");
    $("#idEntregaPEC").html(idEntrega);
    $(".modalContainer").hide();
    $("#divPonerEnCamino").show(500, function() {
        ponerEnCamino_cargaDatos();
        $("#ponerEnCamino_save").unbind("click").click(function() {
            ponerEnCamino_save();
        });
    });

}

function ponerEnCamino_save_inline($obj) {
    console.log($obj)
    var id_entrega = $obj.parent().attr("identrega")

    var param = {
        fase: "ponerEnCamino_save_inline",
        id_entrega: id_entrega,
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status === 1) {
            window.open("certsCal.php?folio=" + id_entrega);
            actualizaTodo();
        } else if (proceso.status == 2) {
            alert("Error " + proceso.error);
        } else {
            alert("Error");
            console.log(proceso.error)
        }
    }, "json");
}

function ponerEnCamino_cargaDatos() {
    var param = {
        fase: "ponerEnCamino_cargaDatos",
        folio: $("#idEntregaPEC").html(),
        t: sessionStorage.getItem("token")
    };

    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }

        $("#fechaSolicitudPEC").html(proceso.data.fechaSolicitud);
        $("#solicitantePEC").html(decode_utf8(encode_utf8(proceso.data.usuario)));
        $("#placasUnidadPEC").html(proceso.data.placas);
        $("#capacidadUnidadPEC").html(proceso.data.capac);
        $("#fechaCargaPEC").html(proceso.data.fecha_carga);
        $("#fechaRegresoPEC").html(proceso.data.fecha_regreso);
        $("#plantaCargaPEC").html(proceso.data.planta_carga);
        $("#plantaRegresoPEC").html(proceso.data.planta_regreso);

        $("#datosPapeletaPEC tbody").html(proceso.data.tablaDatos);
        $("#totalLtsPEC").html(proceso.data.totalLts);
        $("#utilizUnidPEC").html(proceso.data.utilizUnid);
        $(".sameRZK").off("click").on("click", function() {
            var valor = $(this).parent().find(".remisionZKPEC").val();
            $(".remisionZKPEC").val(valor);
        });
        $(".sameLZK").off("click").on("click", function() {
            var valor = $(this).parent().find(".loteZKPEC").val();
            $(".loteZKPEC").val(valor);
        });
        $(".sameDens").off("click").on("click", function() {
            var valor = $(this).parent().find(".densidadPEC").val();
            $(".densidadPEC").val(valor);
        });
        $(".sameConc").off("click").on("click", function() {
            var valor = $(this).parent().find(".concentracionPEC").val();
            $(".concentracionPEC").val(valor);
        });
    }, "json").done(function() {
        activateCert();
    });
}

function ponerEnCamino_limpiarForm() {
    $("#idEntregaPEC").html("");
    $("#placasPEC").html("");
    $("#ltsPEC").html("");

    $("#PECsellosEscotilla").val("");
    $("#PECnumEmbarque").val("");
    $("#PECpesoNeto").val("");
    $("#PECresponsable").val("");
    $("#fechaSolicitudPEC").html("");
    $("#solicitantePEC").html("");
    $("#placasUnidadPEC").html("");
    $("#capacidadUnidadPEC").html("");
    $("#fechaCargaPEC").html("");
    $("#fechaRegresoPEC").html("");
    $("#plantaCargaPEC").html("");
    $("#plantaRegresoPEC").html("");

    $("#datosPapeletaPEC tbody").html("<tr><td colspan='11' style='text-align:center;font-size:20pt;'><i class='fa fa-spinner fa-spin'></i></td></tr>");
    $("#totalLtsPEC").html("");
    $("#utilizUnidPEC").html("");


    $("#divPonerEnCamino").hide(500);
}

function ponerEnCamino_save() {
    var queries = new Array();
    $("#datosPapeletaPEC tbody tr").each(function(i, v) {
        queries.push("UPDATE smartRoad_pre_ruteo SET status='camino' WHERE id_pre_ruteo IN(" + $(this).attr("iprs") + ")");
    });
    //    console.log(queries.join(";") + ";");
    //    return false;
    var param = {
        fase: "ponerEnCamino_save",
        idEntrega: $("#idEntregaPEC").html(),
        numEmbarqueR: $("#PECnumEmbarque").val(),
        pesoNeto: $("#PECpesoNeto").val(),
        usuarioEnvio: $("#userSession").val() + " " + $("#nomUsuario").val(),
        updQueries: queries.join(";") + ";",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status === 1) {
            window.open("certsCal.php?folio=" + $("#idEntregaPEC").html());
            actualizaTodo();
            $("#closePonerEnCamino").click();
        } else {
            alert("Error");
        }
    }, "json");
}

function concluirEnvio_form(obj) {
    var identrega = $(obj).attr("identrega");
    var ltsTot = $(obj).attr("ltsTot");
    var numEnvioRaloy = $(obj).attr("numEnvioRaloy");
    $("#concEnviIdEntrega").val(identrega);
    //Limpiar formulario
    $("#datosConcEnvio").find("input")
        .val(0)
        .off("click")
        .on("click", function() {
            $(this).select();
        });
    $(".cvt").each(function() {
        $(this).html("");
    });
    $(".cvu").each(function() {
        $(this).html("");
    });
    $(".currency2").each(function() {
        $(this).html("");
    });
    $(".currency3").each(function() {
        $(this).html("");
    });
    $(".numeric0").each(function() {
        $(this).html("");
    });
    $(".numeric2").each(function() {
        $(this).html("");
    });
    $("#datosConcEnvio").find("textarea").val("");
    $("#numEnvioRaloyCE").html(numEnvioRaloy);
    $("#ltsembarqueconc").html(ltsTot).formatNumber({ format: "#,##0.00", locale: "us" });
    $("#datosConcEnvio").find(".folio").html(identrega);
    $("#concluyePropias").attr("identrega", identrega);
    obtenerFijosPipa(obj.attr("placas"));
    var preciodieseltoday = $("#preciodieseltoday").val();
    $("#preciodieselsiniva").val(preciodieseltoday);
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
            ltsentrega: $.parseNumber($("#ltsembarqueconc").html(), { format: "#,##0.00", locale: "us" }),
            tiemporuta: $.parseNumber($("#restiemporuta").html(), { format: "#,##0.00", locale: "us" }),
            kmsrecorr: $.parseNumber($("#kmsRecorrP").html(), { format: "#,##0", locale: "us" }),
            rendcomb: $.parseNumber($("#rendkmlP").html(), { format: "$#,##0.00", locale: "us" }),
            llantas: $.parseNumber($("#llantas").html(), { format: "$#,##0.00", locale: "us" }),
            llantasu: $.parseNumber($("#llantasu").html(), { format: "$#,##0.000", locale: "us" }),
            chofer: $.parseNumber($("#chofer").html(), { format: "$#,##0.00", locale: "us" }),
            choferu: $.parseNumber($("#choferu").html(), { format: "$#,##0.000", locale: "us" }),
            depreciacion: $.parseNumber($("#depreciacion").html(), { format: "$#,##0.00", locale: "us" }),
            depreciacionu: $.parseNumber($("#depreciacionu").html(), { format: "$#,##0.000", locale: "us" }),
            mantenimiento: $.parseNumber($("#mantenimiento").html(), { format: "$#,##0.00", locale: "us" }),
            mantenimientou: $.parseNumber($("#mantenimientou").html(), { format: "$#,##0.000", locale: "us" }),
            administracion: $.parseNumber($("#administracion").html(), { format: "$#,##0.00", locale: "us" }),
            administracionu: $.parseNumber($("#administracionu").html(), { format: "$#,##0.000", locale: "us" }),
            seguro: $.parseNumber($("#seguro").html(), { format: "$#,##0.00", locale: "us" }),
            segurou: $.parseNumber($("#segurou").html(), { format: "$#,##0.000", locale: "us" }),
            otrosfijos: $.parseNumber($("#otrosfijos").html(), { format: "$#,##0.00", locale: "us" }),
            otrosfijosu: $.parseNumber($("#otrosfijosu").html(), { format: "$#,##0.000", locale: "us" }),
            totalfijos: $.parseNumber($("#totalF").html(), { format: "$#,##0.00", locale: "us" }),
            totalfijosu: $.parseNumber($("#totalFUnitario").html(), { format: "$#,##0.000", locale: "us" }),
            diesel: $.parseNumber($("#resdiesel").html(), { format: "$#,##0.00", locale: "us" }),
            dieselu: $.parseNumber($("#resdieselu").html(), { format: "$#,##0.000", locale: "us" }),
            peajes: $.parseNumber($("#respeajes").html(), { format: "$#,##0.00", locale: "us" }),
            peajesu: $.parseNumber($("#respeajesu").html(), { format: "$#,##0.000", locale: "us" }),
            alimentos: $.parseNumber($("#resalimentos").html(), { format: "$#,##0.00", locale: "us" }),
            alimentosu: $.parseNumber($("#resalimentosu").html(), { format: "$#,##0.000", locale: "us" }),
            hospedaje: $.parseNumber($("#reshospedaje").html(), { format: "$#,##0.00", locale: "us" }),
            hospedajeu: $.parseNumber($("#reshospedajeu").html(), { format: "$#,##0.000", locale: "us" }),
            otrosvar: $.parseNumber($("#resotros").html(), { format: "$#,##0.00", locale: "us" }),
            otrosvaru: $.parseNumber($("#resotrosu").html(), { format: "$#,##0.000", locale: "us" }),
            costoext: $.parseNumber($("#rescostoext").html(), { format: "$#,##0.00", locale: "us" }),
            costoextu: $.parseNumber($("#rescostoextu").html(), { format: "$#,##0.000", locale: "us" }),
            repartosext: $.parseNumber($("#resrepartosext").html(), { format: "$#,##0.00", locale: "us" }),
            repartosextu: $.parseNumber($("#resrepartosextu").html(), { format: "$#,##0.000", locale: "us" }),
            desviosext: $.parseNumber($("#resdesviosext").html(), { format: "$#,##0.00", locale: "us" }),
            desviosextu: $.parseNumber($("#resdesviosextu").html(), { format: "$#,##0.000", locale: "us" }),
            totalvariables: $.parseNumber($("#totalV").html(), { format: "$#,##0.000", locale: "us" }),
            totalvariablesu: $.parseNumber($("#totalVUnitario").html(), { format: "$#,##0.000", locale: "us" }),
            costototal: $.parseNumber($("#costototP").html(), { format: "$#,##0.00", locale: "us" }),
            costototalu: $.parseNumber($("#costototuP").html(), { format: "$#,##0.000", locale: "us" }),
            pesobruto: $.parseNumber($("#pesobruto").html(), { format: "#,##0.00", locale: "us" }),
            longitudpipa: $("#longitudpipam").val(),
            t: sessionStorage.getItem("token")
        },
        bitacora: $("#bitacoraConcEnv").val(),
    };
    console.log(param);
    $.get(file, param, function(proceso) {
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

function dimePrecioDiesel() {
    var param = {
        fase: "dimepreciodiesel",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        $("#preciodieseltoday").val(proceso);
        console.log("precio diésel ok " + proceso);
    });
}

function obtenerFijosPipa(placas) {
    var param = {
        fase: "obtenerFijosPipa",
        placas: placas,
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
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
        var ltsTotales = $.parseNumber($("#ltsembarqueconc").html(), { format: "#,##0.00", locale: "us" });
        $("#pesocarga").val(Math.round(proceso.pesounidadcarga * ltsTotales * 100) / 100);
    }, "json");
}

function updateAll(_callback) {
    boxesRuteados();
    boxesCarga();
    boxesCamino();
    _callback();
}

function actualizaTodo() {
    boxesRuteados();
    boxesCarga();
    boxesCamino();
}

function decode_utf8(s) {
    return decodeURIComponent(escape(s));
}

function encode_utf8(s) {
    return unescape(encodeURIComponent(s));
}

function activateCert() {
    $(".cert").off("click").on("click", function() {
        var folio = $(this).attr("folio");
        var iprs = $(this).attr("iprs");
        window.open("../smartRoad/certificadoCalidad.php?folio=" + folio + "&iprs=" + iprs + "");
    });
}

function formatNumbers() {
    $(".cvt").each(function() {
        $.formatNumber($(this).html(), { format: "$#,##0.00", locale: "us" });
    });
    $(".cft").each(function() {
        $.formatNumber($(this).html(), { format: "$#,##0.00", locale: "us" });
    });
    $(".cvu").each(function() {
        $.formatNumber($(this).html(), { format: "$#,##0.000", locale: "us" });
    });
    $(".cfu").each(function() {
        $.formatNumber($(this).html(), { format: "$#,##0.000", locale: "us" });
    });
    $(".currency2").each(function() {
        $.formatNumber($(this).html(), { format: "$#,##0.00", locale: "us" });
    });
    $(".currency3").each(function() {
        $.formatNumber($(this).html(), { format: "$#,##0.000", locale: "us" });
    });
    $(".numeric2").each(function() {
        $.formatNumber($(this).html(), { format: "#,##0.00", locale: "us" });
    });
    $(".numeric0").each(function() {
        $.formatNumber($(this).html(), { format: "#,##0", locale: "us" });
    });
}