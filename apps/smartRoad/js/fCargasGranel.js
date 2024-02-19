var file = "php/fIndex.php";
$(document).ready(function () {
    limpiaSolCarga();
    dimePedidosPendientesOdoo();
    $("#btnUpdate").click(limpiaSolCarga);
    //Pedidos y ruteo
    $("#btnLlamaPedidos").click(function () {
        $(".modalContainer").hide();
        $(this).prop("disabled", true);
        $("#divPedidos").show(500);
    });
    $("#closePedidos")
            .on("click", function () {
                $(".modalContainer").hide(500);
                $("#btnLlamaPedidos").prop("disabled", false);
            });
    $('#btnSavePedidos')
            .on('click', function () {
                guarda_pre_ruteo();
            });
    $('#btnUpdPedidos')
            .on('click', function () {
//                dimePedidosPendientes();
            });


    //Papeleta (solicitud de carga)
    $("#closePapeleta")
            .on("click", function () {
                limpiaSolCarga();
            });
    $("#btnSaveSolCarga").click(function () {
        generaSolCarga();
    });
    $("#fechaCarga").change(function () {
        selAtqs();
    });

    $("#fechaCarga").datepicker({dateFormat: 'yy-mm-dd', minDate: 0});
    $("#fechaRegreso").datepicker({dateFormat: 'yy-mm-dd', minDate: 0});

    //poner en camino
    $("#closePonerEnCamino").unbind("click").click(
            function () {
                ponerEnCamino_limpiarForm();
            });

    //Concluir envíos
    $("#closeConcluirEnvio")
            .on("click", function () {
                $(".modalContainer").hide(500);
                $("#concEnviIdEntrega").val("");
            });
    $("#closeConcluirEnvioExt")
            .on("click", function () {
                $(".modalContainer").hide(500);
                $("#concEnviIdEntrega").val("");
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
    $("#odomFinE,#ltsDieselE,#odomInicioE").change(function () {
        var odomFinal = ($("#odomFinE").val() > 0) ? $("#odomFinE").val() : 0;
        var odomInicio = ($("#odomInicioE").val() > 0) ? $("#odomInicioE").val() : 0;
        var ltsDiesel = ($("#ltsDieselE").val() > 0) ? $("#ltsDieselE").val() : 0;
        var kmsRecorrido = eval(odomFinal - odomInicio);
        var kms_lt = eval(kmsRecorrido / ltsDiesel);
        $("#kmsRecorrE").val(kmsRecorrido);
        $("#rendimientoE").val(kms_lt);
    });

    $(".calcula").unbind("change").on("change", function () {
        if (isNaN($(this).val()) === true) {
            alert("Valor Inválido");
            $(this).select();
            return false;
        }
        var totVal = 0;
        var thisVal = 0;
        $(".calcula").each(function () {
            thisVal = ($(this).val() !== "" && eval($(this).val()) > 0) ? eval($(this).val()) : 0;
            totVal += thisVal;
            $(this).val((Math.round(thisVal * 100)) / 100);
        });
        var costoxltP = Math.round((eval(totVal / $("#ltsEntregados").val()) * 10000)) / 10000;
        $("#costoxltP").val(costoxltP);
        totVal = (Math.round(totVal * 100)) / 100;
        $("#costoTotP").val(totVal);
    });

    //Botón en formulario para guardar el envío de pipas propias
    $("#concluyePropias").unbind("click").on("click", function () {
        concluirEnvio_propias_save($(this).attr("identrega"));
    });
    //Botón en formulario para guardar el envío de pipas externas
    $("#concluyeExternas").unbind("click").on("click", function () {
        concluirEnvio_externas_save($(this).attr("identrega"));
    });

});
function dimePedidosPendientesOdoo() {
    $.get("../../crons/cronPedidosSinRuteo.php", function (data) {
        console.log("Carga de pedidos a SmartRoad:" + data);
    });
    var param = {
        fase: "dimePedidosPendientesOdoo"
    };
//    console.log(param);
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            console.log(proceso);
            return false;
        } else {
            if (proceso.status !== 1) {
                alert(proceso.error);
                return;
            }
//            console.log(proceso.table);
            $('#tblPedidos tbody').html(proceso.table);



            var table = $('#tblPedidos').DataTable(
                    {
                        "order": [[19, 'asc'], [9, 'asc']],
                        "columnDefs": [{
                                "targets": 1,
                                "orderable": false
                            }],
                        destroy: true,
                        "scrollX": true,
                        scrollY: '50vh',
                        scrollCollapse: true,
                        paging: false,
                        "footerCallback": function (row, data, start, end, display) {
                            var api = this.api(), data;
                            // Remove the formatting to get integer data for summation
                            var intVal = function (i) {
                                return typeof i === 'string' ?
                                        i.replace(/[\$,]/g, '') * 1 :
                                        typeof i === 'number' ?
                                        i : 0;
                            };
                            // Total over all pages
                            total = api
                                    .column(18)
                                    .data()
                                    .reduce(function (a, b) {
                                        return intVal(a) + intVal(b);
                                    }, 0);

                            // Total over this page
                            pageTotal = api
                                    .column(18, {page: 'current'})
                                    .data()
                                    .reduce(function (a, b) {
                                        return intVal(a) + intVal(b);
                                    }, 0);

                            // Update footer
//                        $(api.column(0).footer()).html(
//                                'Subtotal:' + pageTotal + ' / Total:' + total + ' Lts Pendientes)' + proceso.subtots
//                                );
                        }
                    }
            );
            $(".inputAtq").change(function () {
                cambiaColorAtq($(this));
            });
            $(".inputEta").datepicker({dateFormat: "yy-mm-dd"});
            $("#fechCarga").datepicker({dateFormat: "yy-mm-dd"});


            cambiaColorAtq();
            $(".inputsPed").on("change", function () {
                $("#divPedidos").find(".avisos").html("Cambios sin guardar").show();
            });
        }
    }, "json");
}
function buscapedidocerrado() {
//    $(".btnCloseModal").click();
    $(".modalContainer").hide();
    $("#divPedidos").show(500);
    var param = {
        fase: "buscapedidocerrado",
        pedidoRaloy: $("#pedidocerradoRaloy").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        $('#tblPedidos').html(proceso.table);



        var table = $('#tblPedidos').DataTable(
                {
                    "order": [[0, 'asc'], [4, 'asc'], [5, 'asc']],
                    "columnDefs": [{
                            "targets": 1,
                            "orderable": false
                        }],
                    destroy: true,
                    "scrollX": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    paging: false,
                    "footerCallback": function (row, data, start, end, display) {
                        var api = this.api(), data;
                        // Remove the formatting to get integer data for summation
                        var intVal = function (i) {
                            return typeof i === 'string' ?
                                    i.replace(/[\$,]/g, '') * 1 :
                                    typeof i === 'number' ?
                                    i : 0;
                        };
                        // Total over all pages
                        total = api
                                .column(18)
                                .data()
                                .reduce(function (a, b) {
                                    return intVal(a) + intVal(b);
                                }, 0);

                        // Total over this page
                        pageTotal = api
                                .column(18, {page: 'current'})
                                .data()
                                .reduce(function (a, b) {
                                    return intVal(a) + intVal(b);
                                }, 0);

                        // Update footer
//                        $(api.column(0).footer()).html(
//                                'Subtotal:' + pageTotal + ' / Total:' + total + ' Lts Pendientes)' + proceso.subtots
//                                );
                    }
                }
        );
        $(".inputAtq").change(function () {
            cambiaColorAtq($(this));
        });
        $(".inputEta").datepicker({dateFormat: "yy-mm-dd"});
        $("#fechCarga").datepicker({dateFormat: "yy-mm-dd"});


        cambiaColorAtq();
        $(".inputsPed").on("change", function () {
            $("#divPedidos").find(".avisos").html("Cambios sin guardar").show();
        });

    }, "json");
}
function allBoxes() {
    var param = {
        fase: "allBoxes"
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            console.log(proceso);
            return;
        } else {

            //En ruteo
//            construyeBoxesRuteo(proceso.enRuteo);
            //En Carga
            $('#contenedorCargas').html(proceso.enCarga);
            $(".consultar").click(function () {
                window.open("solicitudCarga.php?folio=" + $(this).parent().attr("identrega"));
            });
            $(".ponerEnCamino").click(function () {
                ponerCargado($(this));
            });
            $(".cancelarCarga").click(function () {
                cancelarCarga($(this).parent().attr("identrega"));
            });
        }

    }, "json");
}
function previsualizarPapeleta(obj) {
    $("#inpIprs").val($(obj).attr("iprs"));
    if ($("#inpIprs").val() !== "") {
        var param = {
            fase: "previsualizarPapeleta",
            iprs: $("#inpIprs").val()
        };
        $.get(file, param, function (proceso) {
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
        $(".atqNmbr").each(function (i, val) {
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
    $(".subtots").each(function (i, v) {
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
            iprs: iprs
        };
        $.get(file, param, function (proceso) {
//            console.log(proceso);
            if (proceso.status !== 1) {
                alert(proceso.error);
                return;
            }

            window.open("solicitudCarga.php?folio=" + proceso.idNuevo, "_blank");

        }, "json").done(function () {
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
        ltsSurtir: $("#ltsSurtirCarga").val()
    };
    $.get(file, param, function (proceso) {
//        console.log(proceso);
        if (proceso.status === 1) {

            $("#placasUnidad")
                    .html(proceso.options)
                    .unbind("change")
                    .change(function () {
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
        fase: "selPlantas"
    };
    $.get(file, param, function (proceso) {
//        console.log(proceso);
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        $(".selPlantas").each(function () {
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
            idEntrega: idEntrega
        };
        $.get(file, param, function (proceso) {
            if (proceso.status !== 1) {
                alert(proceso.error);
                return;
            } else {
                actualizaTodo();
            }
        }, "json");
    }

}

function ponerCargado(obj) {
    var idEntrega = $(obj).parent().attr("identrega");
    $("#idEntregaPEC").html(idEntrega);
    $(".modalContainer").hide();
    $("#divPonerEnCamino").show(500, function () {
        ponerEnCamino_cargaDatos();
        $("#ponerEnCamino_save").unbind("click").click(function () {
            ponerEnCamino_save();
        });
    });

}
function ponerEnCamino_cargaDatos() {
    var file = "php/fIndex.php";
    var param = {
        fase: "ponerEnCamino_cargaDatos",
        folio: $("#idEntregaPEC").html()
    };

    $.get(file, param, function (proceso) {
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
        $(".sameRZK").off("click").on("click", function () {
            var valor = $(this).parent().find(".remisionZKPEC").val();
            $(".remisionZKPEC").val(valor);
        });
        $(".sameLZK").off("click").on("click", function () {
            var valor = $(this).parent().find(".loteZKPEC").val();
            $(".loteZKPEC").val(valor);
        });
        $(".sameDens").off("click").on("click", function () {
            var valor = $(this).parent().find(".densidadPEC").val();
            $(".densidadPEC").val(valor);
        });
        $(".sameConc").off("click").on("click", function () {
            var valor = $(this).parent().find(".concentracionPEC").val();
            $(".concentracionPEC").val(valor);
        });
    }, "json");
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
    $("#datosPapeletaPEC tbody tr").each(function (i, v) {
        queries.push("UPDATE smartRoad_pre_ruteo SET status='camino', remisionZK='" + $(this).find(".remisionZKPEC").val() + "', loteZK='" + $(this).find(".loteZKPEC").val() + "', densidad='" + $(this).find(".densidadPEC").val() + "', concentracion='" + $(this).find(".concentracionPEC").val() + "', sellosDescarga='" + $(this).find(".sellosDescPEC").val() + "' WHERE id_pre_ruteo=" + $(this).attr("id_pre_ruteo"));
    });
//    console.log(queries.join(";") + ";");
//    return false;
    var param = {
        fase: "ponerEnCamino_save",
        idEntrega: $("#idEntregaPEC").html(),
        sellosEscotilla: $("#PECsellosEscotilla").val(),
        numEmbarqueR: $("#PECnumEmbarque").val(),
        pesoNeto: $("#PECpesoNeto").val(),
        responsableCarga: $("#PECresponsable").val(),
        usuarioEnvio: $("#userSession").val() + " " + $("#nomUsuario").val(),
        updQueries: queries.join(";") + ";"
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            window.open("certsCal.php?folio=" + $("#idEntregaPEC").html());
            actualizaTodo();
            $("#closePonerEnCamino").click();
        } else {
            alert("Error");
        }
    }, "json");
}

function concluirEnvio_propias_form(obj) {
    var identrega = $(obj).attr("identrega");
    var ltsTot = $(obj).attr("ltsTot");
    var numEnvioRaloy = $(obj).attr("numEnvioRaloy");
    $("#concEnviIdEntrega").val(identrega);
    $("#datosConcEnvio").find("input").val(0).click(function () {
        $(this).select();
    });
    $("#datosConcEnvio").find("textarea").val("");
    $("#numEnvioRaloyCE").html(numEnvioRaloy);
    $("#ltsEntregados").val(ltsTot);
    $("#datosConcEnvio").find(".folio").html(identrega);
    $("#concluyePropias").attr("identrega", identrega);
}
function concluirEnvio_externas_form(obj) {
    var identrega = $(obj).attr("identrega");
    var ltsTot = $(obj).attr("ltsTot");
    var numEnvioRaloy = $(obj).attr("numEnvioRaloy");
    $("#concEnviIdEntregaExt").val(identrega);
    //seleccionar el texto de los inputs cuando se dé click sobre ellos
    $("#datosConcEnvioExt").find("input").val(0).click(function () {
        $(this).select();
    });
    $("#datosConcEnvioExt").find("textarea").val("");
    $("#numEnvioRaloyCEE").html(numEnvioRaloy);
    $("#ltsEntregadosE").val(ltsTot);
    $("#datosConcEnvioExt").find(".folio").html(identrega);
    //costo cargado en SCP
    var costoSCP = dimeCostoSCPExterna(numEnvioRaloy);
    (costoSCP > 0 && costoSCP !== "error") ? $("#costoTotPE").val(costoSCP) : $("#costoTotPE").val(0);
    (costoSCP > 0 && costoSCP !== "error") ? $("#costoxltPE").val(eval(costoSCP / ltsTot)) : $("#costoxltPE").val(0);
    //colocar el folio como un atributo en el botón de guardado
    $("#concluyeExternas").attr("identrega", identrega);
}
function dimeCostoSCPExterna(numEnvioRaloy) {//Requiere SCP RALOY
    var param = {
        fase: "dimeCostoSCPExterna",
        numEnvioRaloy: numEnvioRaloy
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            return proceso.costo;
        } else {
            return "error";
        }
    }, "json");
}
function concluirEnvio_propias_save(identrega) {

    var param = {
        fase: "concluirEnvio_propias_save",
        idEntrega: identrega,
        diesel: $("#diesel").val(),
        peajes: $("#peajes").val(),
        alimentos: $("#alimentos").val(),
        hospedaje: $("#hospedaje").val(),
        otros: $("#otros").val(),
        expotros: $("#expotros").val(),
        ltsDiesel: $("#ltsDiesel").val(),
        odomInicio: $("#odomInicio").val(),
        odomFin: $("#odomFin").val(),
        kmsRecorr: $("#kmsRecorr").val(),
        rendimiento: $("#rendimiento").val(),
        ltsEntregados: $("#ltsEntregados").val(),
        costoTotP: $("#costoTotP").val(),
        costoxltP: $("#costoxltP").val(),
        bitacoraConcEnv: $("#bitacoraConcEnv").val(),
        userCierre: $("#userSession").html()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            actualizaTodo();
            $("#closeConcluirEnvio").click();
        } else {
            alert(proceso.error);
            return;
        }
    }, "json");
}
function concluirEnvio_externas_save(identrega) {
    if ($("#costoTotPE").val() <= 0 || $("#costoTotPE").val() === "" || $("#costoTotPE").val() === undefined) {
        alert("No es posible guardar con costo 0");
        return false;
    }
    var param = {
        fase: "concluirEnvio_externas_save",
        idEntrega: identrega,
        ltsDiesel: $("#ltsDieselE").val(),
        odomInicio: $("#odomInicioE").val(),
        odomFin: $("#odomFinE").val(),
        kmsRecorr: $("#kmsRecorrE").val(),
        rendimiento: $("#rendimientoE").val(),
        ltsEntregados: $("#ltsEntregadosE").val(),
        costoTotP: $("#costoTotPE").val(),
        costoxltP: $("#costoxltPE").val(),
        bitacoraConcEnv: $("#bitacoraConcEnvE").val(),
        userCierre: $("#userSession").html()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            actualizaTodo();
            $("#closeConcluirEnvioExt").click();
        } else {
            alert(proceso.error);
            return;
        }
    }, "json");
}

function actualizaTodo() {
//    dimePedidosPendientes();
    allBoxes();
}

function decode_utf8(s) {
    return decodeURIComponent(escape(s));
}
function encode_utf8(s) {
    return unescape(encodeURIComponent(s));
}