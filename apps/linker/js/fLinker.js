var permisoEdicion=0;
$(document).ready(function () {
    revisaPermisoEdicion();
    $("#menu div").click(function () {
        window.location.href = $(this).attr("page");
    });
    $("#tabLinker").addClass("tabselected");

    $("#mensajes").hide();

    $("#getInvoicesBtn").click(function () {
        $("#facturasBusquedaCont").hide();
        $("#invoiceInfoCont").hide();
        getInvoices();
    });

    $(".boxTab").click(function () {
        toggleTabs($(this));
    });
    $("#getMaterialBtn").click(function () {
        getPOInfo();
    });
    
    

    $("#linkInvoicesBtn").click(function () {
        saveInvoices();
    });
    $("#mainMaterial").focus(function () {
        $("#PONumber").html("");
    });
    $("#mainMaterial").change(function () {
        llenaOCs($(this).val());
    });
    $("#mainMaterial").focus();
    limpiarTodo();
});
function revisaPermisoEdicion() {
    var file = "php/functionsLinker.php";
    var param = {
        fase: "revisaPermisoEdicion"
    };
    $.get(file, param, function (proceso) {
        sessionStorage.setItem('edicion', proceso.edicion);
        permisoEdicion=1;
    });
}
function llenaOCs(cveProd) {
    var file = "php/functionsLinker.php";
    var param = {
        fase: "llenaOCs",
        cveProd: cveProd
    };
    $.get(file, param, function (proceso) {
        if (proceso.errors !== "") {
            console.log(proceso.errors);
            return false;
        } else {
            $("#PONumber").html(proceso.options);
        }
    }
    , "json");
}
function deshabilita_por_permisos() {
    console.log(permisoEdicion);
    if (permisoEdicion !== 1)
    {
        $("#buscaFacturas").hide();//Inhabilita las opciones para agregar facturas a la OC
        $("#fechaZarpe").unbind("click");//Inhabilita la edición de fecha de Zarpe
        $(".clickableSH").hide();//Clase clickableSH (Show/Hide)... muestra u oculta los td de desenlace, inlcuyendo el header(th)
        //Cambia el botón de edición
        $("#menuDisplayer").html("Modo Sólo Lectura");

    }
}
function edit_revisaStatus() {
    if ($("#tdStatusLinker").html() === "Terminada") {//Edición desactivada
        edit_disable();
    } else {
        edit_enable();
    }
    if (eval(permisoEdicion) < 1) {
        deshabilita_por_permisos();
    }
}
function edit_enable() {
    $("#buscaFacturas").show();//Habilita las opciones para agregar facturas
    $(".clickableSH").show();//Clase clickableSH (Show/Hide)... muestra u oculta los td de desenlace, inlcuyendo el header(th)
    //Habilita la edición de fecha de zarpe
    $("#fechaZarpe").click(function () {
        if (typeof $(this).children("input").val() === "undefined") {
            var fecha = $(this).attr("fZ");
            $("#fechaZarpe")
                    .html("<input id='fechaZarpeInput' value='" + fecha + "'/><button id='saveFechaZarpe'>Actualizar</button>");
            $("#fechaZarpeInput").datepicker({
                dateFormat: "yy-mm-dd",
                onSelect: function (dateText) {
                    $("#fechaZarpe").attr("fZ", this.value);
                }
            })
                    .datepicker("setDate", fecha)
                    .focus();

            $("#saveFechaZarpe").click(function () {
                if (confirm("Por favor confirme que desea MODIFICAR la fecha de zarpe") === true) {
                    updateFechaZarpe();
                } else {
                    $("#fechaZarpe").html(fecha);
                }
            });
        }
    });
    $("#difCant").click(function () {

        if (typeof $(this).children("input").val() === "undefined") {
            var difcanti = $(this).attr("fZ");
            $("#difCant")
                    .html("<input id='difCantInput'  value='" + difcanti + "'/><button id='savedifCant'>Actualizar</button>");

            $("#savedifCant").click(function () {
                if (confirm("Por favor confirme que desea MODIFICAR la diferencia de cantidades") === true) {
                    updateDifCant();
                } else {
                    $("#difCant").html(difcanti);
                }
            });
        }
    }
    );
    //Cambia el botón de edición
    $("#menuDisplayer").html("<span title='Inactivar Edición'><i id='toggleEdit' class='fa fa-toggle-on'></i></span> Edición Activa");
    $("#toggleEdit").unbind("click").click(function () {
        edit_disable();
    });
    $("#tdStatusLinker").html("Editando");
    edit_saveStatus("Editando");
}
function edit_disable() {
    $("#buscaFacturas").hide();//Inhabilita las opciones para agregar facturas a la OC
    $("#fechaZarpe").unbind("click");//Inhabilita la edición de fecha de Zarpe
    $("#difCant").unbind("click");//Inhabilita la edición de Diferencia en cantidades
    $(".clickableSH").hide();//Clase clickableSH (Show/Hide)... muestra u oculta los td de desenlace, inlcuyendo el header(th)
    //Cambia el botón de edición
    $("#menuDisplayer").html("<span id='menuDisplayer' title='Activar Edición'><i id='toggleEdit' class='fa fa-toggle-off'></i></span> Edición Inactiva");
    $("#toggleEdit").unbind("click").click(function () {
        console.log(permisoEdicion);
        if (permisoEdicion === 1) {
            edit_enable();
        } else {
            alert("Error: \n\nSin permisos de edición");
        }

    });
    if ($("#tdStatusLinker").html("Terminada") !== "Terminada")
        edit_saveStatus("Terminada");
    $("#tdStatusLinker").html("Terminada");
}
function edit_saveStatus(stat) {
    var file = "php/functionsLinker.php";
    var param = {
        fase: "edit_saveStatus",
        oc: $("#PONumber").val(),
        material: $("#mainMaterial").val(),
        stat: stat
    };
    $.get(file, param, function (proceso) {
        if (proceso.errors !== "") {
            console.log(proceso.errors);
        }
    }, "json"
            );
}
function getPOInfo() {

    limpiarTodo();

    var file = "php/functionsLinker.php";
    var param = {
        fase: "getPOInfo",
        PO: $("#PONumber").val(),
        material: $("#mainMaterial").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.mensaje !== "") {
            console.log(proceso.mensaje);
            alert(proceso.mensaje);
            return;
        } else {
            $("#tdOC").html(proceso.matInfo.infoNumOC);
            $("#tdFecElabo").html(proceso.matInfo.infofecOC);
            $("#tdCC").html(proceso.matInfo.infoCC);
            $("#tdCveProveedor").html(proceso.matInfo.tdCveProveedor);
            $("#tdProveedor").html(proceso.matInfo.tdProveedor);
            $("#tdMaterial").html(proceso.matInfo.tdMaterial);
            $("#tdMaterialDesc").html(proceso.matInfo.tdMaterialDesc);
            $("#tdCantidad").html(proceso.matInfo.tdCantidad);
            $("#tdUnidad").html(proceso.matInfo.tdUnidad);
            $("#tdUsuario").html(proceso.matInfo.tdUsuario);
            $("#tdPrecio").html(proceso.matInfo.tdPrecio);
            $("#tdTotal").html(proceso.matInfo.tdTotal);
            $("#tdMoneda").html(proceso.matInfo.tdMoneda);
            $("#tdObs").html(proceso.matInfo.tdObs);
            $("#statusOC").html(proceso.matInfo.statusOC);
            $("#idLink").html(proceso.matInfo.idLink);
            $("#tdStatusLinker").html(proceso.matInfo.tdStatusLinker);
            $("#fechaZarpe").html(proceso.matInfo.fechaZarpe);
        }
    }
    , "json"
            )
            .done(function () {
                getSummaryAndDetails();
                $("#menuGral").hide();
                $("#unidadRef").html($("#tdUnidad").html() + "(s)");
                $("#buscaFacturasSect").show();
                //Se agrega el atributo fZ con la fecha de zarpe para permitir el intercambio de td e input
                $("#fechaZarpe").attr("fZ", $("#fechaZarpe").html());
                $("#difCant").attr("fZ", $("#difCant").html());
                edit_revisaStatus();
            });
}
function updateFechaZarpe() {
    var file = "php/functionsLinker.php";
    var param = {
        fase: "updateFechaZarpe",
        oc: $("#PONumber").val(),
        material: $("#mainMaterial").val(),
        fechaZarpe: $("#fechaZarpe").attr("fZ")
    };
    $.get(file, param, function (proceso) {
        if (proceso.errors === "") {
            setTimeout(function () {
                $("#fechaZarpe").attr("fZ", $("#fechaZarpeInput").val());
                $("#fechaZarpe").html($("#fechaZarpeInput").val());//Oculta el campo de texto y botón de edición
            }, 500);
        } else {
            console.log(proceso.errors);
        }
    }, "json"
            );
}
function updateDifCant() {
    var file = "php/functionsLinker.php";
    var param = {
        fase: "updateDifCant",
        oc: $("#PONumber").val(),
        material: $("#mainMaterial").val(),
        difCantidad: $("#difCantInput").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.errors === "") {
            setTimeout(function () {
                $("#difCant").attr("fZ", $("#difCantInput").val());
                $("#difCant").html($("#difCantInput").val());//Oculta el campo de texto y botón de edición
                $porcentaje = (parseFloat($("#difCant").attr("fz")) * 100) / parseFloat($("#tdCantidad").html().replace(',', ''));
                $("#tdPorcentaje").html($porcentaje.toFixed(2) + "%");
            }, 500);
        } else {
            console.log(proceso.errors);
        }
    }, "json"
            );
}
function accionesStatus() {
    $("#menuDisplayer").click(function () {
        if ($("#menuGral").css("display") === "block") {
            $("#menuGral").hide();
            $(this).removeClass("selectorCClick");
        } else {
            $("#menuGral").show();
            $(this).addClass("selectorCClick");
        }
    });
    if ($("#tdStatusLinker").html() === "Terminada") { //Deshabilitar todas las funciones de edición cuando la OC ya fue marcada como terminada
        $("#accionStatus").html("<i class='fa fa-pencil'></i>  Activar Edición").click(function () {
            habilitarOC();
        });
        $("#buscaFacturasSect").hide();
        $(".unlink").removeClass("clickable").unbind("click").html("");
    } else {
        $("#accionStatus").html("<i class='fa fa-flag'></i>  Terminar OC").click(function () {
            terminarOC();
        });
    }

}
function terminarOC() {
    if ($("#linkedInvoicesTbl tbody tr").length === 0) {
        alert("No se han relacionado partidas a esta OC");
        return;
    }
    var file = "php/functionsLinker.php";
    var param = {
        fase: "terminarOC",
        oc: $("#PONumber").val(),
        material: $("#mainMaterial").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.msj === "Listo") {
            $("#mensajes").css("top", $(window).scrollTop()).html(proceso.msj).show();
            setTimeout(function () {
                $("#mensajes").hide();
            }, 1500);
        } else {
            console.log(proceso.errors);
        }
    }, "json"
            )
            .done(function () {
                updateFechaZarpe();
                getPOInfo();
            });
}

function getSummaryAndDetails() {
    var file = "php/functionsLinker.php";
    var param = {
        fase: "getSummary",
        PO: $("#tdOC").html(),
        material: $("#mainMaterial").val()
    };
    
    $.get(file, param, function (proceso) {

        $("#resumenCosto").html(proceso.table);
        if (proceso.errors) {
            console.log(proceso.errors);

        }
    }
    , "json"
            )
            .done(function () {
                getLinkedInvoices();
                getLinkedCN();
                $("#matInfoCont").show();
                $("#unitSummary").html($("#tdUnidad").html());//unidad de la materia prima de la orden de compra principal
                formatNumbers();
                //Botón ver detalles
                $("#showLinked").click(function () {
                    if ($("#partidasEnlazadas").css('display') === 'none') {
                        $("#partidasEnlazadas").show();
                        $(".boxTab").first().click();
                        $(this).html("<i class='fa fa-eye-slash'></i> Ocultar Detalles");
                        edit_revisaStatus();
                    } else {
                        $("#partidasEnlazadas").hide();
                        $(this).html("<i class='fa fa-eye'></i> Ver Detalles");
                    }
                });
                if ($("#partidasEnlazadas").css('display') === 'none') {
                    $("#showLinked").html("<i class='fa fa-eye'></i> Ver Detalles");
                } else {
                    $("#showLinked").html("<i class='fa fa-eye-slash'></i> Ocultar Detalles");
                }

            });
}

function getLinkedInvoices() {
    var file = "php/functionsLinker.php";
    $("#linkedInvoicesTbl tbody").html("");
    var param = {
        fase: "getLinkedInvoices",
        oc: $("#tdOC").html(),
        material: $("#tdMaterial").html()
    };
    $.get(file, param, function (proceso) {
        if (proceso.msj !== "") {
            alert("error");
            console.log(proceso.msj);
        } else {
            $("#linkedInvoicesTbl tbody").html(proceso.trs);
        }
//        console.log(proceso.query);
    }, "json"
            )
            .done(function () {
                formatNumbers();
                $(".clickable").click(function () {
                    var r = confirm("Está seguro de desenlazar la partida de factura seleccionada de la orden de compra concentradora?")
                    if (r === true) {
                        unlinkInvoices($(this).attr("idLink"));
                    } else {
                        return;
                    }
                }).css("cursor", "pointer");
            });
}
function getLinkedCN() {
    var file = "php/functionsLinker.php";
    $("#linkedCNTbl tbody").html("");
    var usd = 0;
    var usdUnit = 0;
    var param = {
        fase: "getLinkedCN",
        oc: $("#tdOC").html(),
        material: $("#tdMaterial").html()
    };
    $.get(file, param, function (proceso) {
        if (proceso.msj !== "") {
            alert("error");
            console.log(proceso.msj);
        } else {
            $("#linkedCNTbl tbody").html(proceso.trs);
            $("#resumenCosto table tbody").append("<tr><td>N.C.</td><td class='currency'>-" + proceso.usd + "</td><td class='currency'>-" + proceso.usdUnit + "</td></tr>");
            usd = proceso.usd;
            usdUnit = proceso.usdUnit;
        }
    }, "json"
            )
            .done(function () {
                restarATotales(usd, usdUnit);
                formatNumbers();
            });
}
function restarATotales(usd, usdUnit) {
    var usdGral = eval($("#subtotGral").parseNumber({format: "$#,##0.00", locale: "us"}) * 1);
    var usdUnitGral = eval($("#subTotUnitGral").parseNumber({format: "$#,##0.00", locale: "us"}) * 1);
    var usdRestar = eval(usd * -1);
    var usdUnitRestar = eval(usdUnit * -1);


    var nuevoUsd = eval(usdGral + usdRestar);
    var nuevoUsdUnit = eval(usdUnitGral + usdUnitRestar);

    $("#subtotGral").html(nuevoUsd);
    $("#subTotUnitGral").html(nuevoUsdUnit);
    formatNumbers();
}

function getInvoices() {
    if ($("#supplierInvoiceSearch").val() === "" || $("#supplierInvoiceSearch").val() === "undefined") {
        return;
    }
    var file = "php/functionsLinker.php";
    var param = {
        fase: "getInvoices",
        fact: $("#supplierInvoiceSearch").val()
    };
    var numRows = 0;
    $.get(file, param, function (proceso) {
        $("#facturasBusqueda tbody").html(proceso.table);
        if (proceso.errors !== "") {
            console.log(proceso.errors);
        }
        numRows = proceso.numRows;
    }
    , "json"
            ).done(function () {
        $("#facturasBusquedaCont").show();
        $(".getInvoiceInfoBtn").click(function () {
            $("#facturasBusqueda tbody").html($(this).parent().parent().html());
            $(".getInvoiceInfoBtn").hide();
            getInvoiceInfo($(this));
        });
        if (numRows === 1) {
            $(".getInvoiceInfoBtn:first-child").click();
        }
        formatNumbers();
    });
}
function getInvoiceInfo(obj) {
    var file = "php/functionsLinker.php";
    var param = {
        fase: "getOptionsClasif"
    };
    var options = "";
    $.get(file, param, function (proceso) {
        options = proceso.opts;
    }
    , "json").done(function () {
        var param = {
            fase: "getInvoiceInfo",
            cve: obj.attr("cveProv"),
            fact: obj.attr("fact")
        };

        $.get(file, param, function (proceso) {
            $("#supplierInvoiceInfo").html(proceso);
            $(".clasifGasto")
                    .each(function (i, val) {
                        $(this).html(options);
                    });
        })
                .done(function () {
                    $("#invoiceInfoCont").show();
                    $("#supplierInvoiceInfo input[type='checkbox']:first-child").focus();
                    $("#supplierInvoiceInfo td").each(function () {
                        if ($(this).html().length <= 8) {
                            $(this).css("text-align", "center");
                        }
                    });
                    getPedimentos();
                    $(".input2Char,.input4Char")
                            .keyup(function (event) {
                                $(this).attr("justFocused", 0);
                                if ((eval($(this).val().length) === eval($(this).attr("maxlength"))) && $(this).attr("justFocused") === 0) {
                                    $(this).next().focus();
                                } else {
                                    return;
                                }
                            })
                            .focus(function (event) {
                                $(this).attr("justFocused", 1);
                                $(this).select();
                            });
                    formatNumbers();
                });
    });

}
function getPedimentos() {
    var file = "php/functionsLinker.php";
    var param = {
        fase: "getPedimentos",
        oc: $("#PONumber").val(),
        material: $("#mainMaterial").val()
    };

    $.get(file, param, function (proceso) {
        $("#clasePedimento").html(proceso.options);
        $(".inputPedimento").each(function (i, val) {
            $(this).val(proceso.ped[i]);
        });
    }, "json");
}
function saveInvoices() {
    var file = "php/functionsLinker.php";
    var numOC = $("#tdOC").html();
    var cveArt = $("#tdMaterial").html();
    var fechaZarpe = "";
    if ($("#fechaZarpeInput").val()) {
        fechaZarpe = $("#fechaZarpeInput").val();
    } else {
        fechaZarpe = $("#fechaZarpe").html();
    }
    var param = {
        fase: "checkCabecera",
        numOC: numOC,
        cveArt: cveArt,
        fechaZarpe: fechaZarpe
    };
//    console.log(param);
    $.get(file, param, function (proceso) {
        if (proceso.msj === "") {
            $("#idLink").html(proceso.id);
            var arrRows=new Array();
            var arrValores = new Array();
            
            if ($("#cantProrrateo").val() === "" || $("#tdOC").html() === "" || $("#tdMaterial").html() === "" || $("input[type='checkbox']:checked").length === 0) {
                alert("Algún valor no es válido, por favor revise los datos");
                return;
            }
            $("input[type='checkbox']:checked").each(function (key, val) {
                var arrValores={
                    "idLink":$("#idLink").html(),
                    "numFact":$(this).parent().parent().attr("fact"),
                    "CveProvFact":$(this).parent().parent().attr("prov"),
                    "Linea":$(this).parent().parent().attr("line"),
                    "cveMP":$(this).parent().parent().attr("cveMP"),
                    "numRecibo":$(this).parent().parent().attr("numRecibo"),
                    "tcToUSD":($("#tcToUSD").val()>0)?$("#tcToUSD").val():1,
                    "idTipoGasto":$(this).parent().parent().find(".clasifGasto").val(),
                    "apportion":eval($(this).parent().parent().find(".apportion").val() / 100),
                    "usuarioLigue":$("#nomUsuario").val()
                };
                arrRows.push(arrValores);
            });
            console.log(arrRows);
            var param = {
                fase: "saveInvoices",
                rows: arrRows
            };
            $.get(file, param, function (proceso) {
                if (proceso.msj === "Updated") {
                    $("#mensajes").css("top", $(window).scrollTop()).html(proceso.msj).show();
                    setTimeout(function () {
                        $("#mensajes").hide();
                    }, 1500);
                } else {
                    alert("Error");
                    console.log(proceso.msj);
                }
            }, "json"
                    )
                    .done(function () {
                        $("#buscaFacturasSect").hide();
                        $("#invoiceInfoCont").hide();
                        getPOInfo();
                        getInvoices();
                        formatNumbers();
                    });

        } else {
            alert("Error");
            console.log(proceso.errors);
        }
    }, "json"
            );
}
function unlinkInvoices(idLink) {
    var file = "php/functionsLinker.php";
    var param = {
        fase: "unlinkInvoices",
        idLink: idLink
    };
    $.get(file, param, function (proceso) {
        if (proceso.msj === "Updated") {
            $("#mensajes").css("top", $(window).scrollTop()).html(proceso.msj).show();
            setTimeout(function () {
                $("#mensajes").hide();
            }, 1500);
        } else {
            console.log(proceso.errors);
        }
    }, "json"
            )
            .done(function () {
                $("#buscaFacturasSect").hide();
                $("#invoiceInfoCont").hide();
                getPOInfo();
                getInvoices();
                formatNumbers();
            });
}
function toggleTabs(obj) {
    $(".boxTab").removeClass("current");
    $(".boxContent").hide();
    var divtoshow = obj.attr("boxContent");
    $("#" + divtoshow).show();
    obj.addClass("current");
}
function limpiarTodo() {
    $(".materialInfoTd").each(function (i, val) {
        $(this).html("");
    });
    $("#linkedInvoicesTbl tbody").html("");
    $("#linkedCNTbl tbody").html("");
    $("#facturasBusqueda tbody").html("");
    $("#supplierInvoiceSearch").val("");
    $("#partidasEnlazadas, #matInfoCont").hide();
    $("#buscaFacturasSect").hide();
    $("#facturasBusquedaCont").hide();
    $("#invoiceInfoCont").hide();
    $("#menuGral").hide();
}
function formatNumbers() {
    $('.currency').text(function () {
        if ($.isNumeric($(this).html())) {
            $(this).parseNumber({format: "$#,##0.00", locale: "us"});
            $(this).formatNumber({format: "$#,##0.00", locale: "us"});
        }
    });
    $('.numeric').text(function () {
        if ($.isNumeric($(this).html())) {
            $(this).parseNumber({format: "#,##0.00", locale: "us"});
            $(this).formatNumber({format: "#,##0.00", locale: "us"});
        }
    });
    $('.perc').text(function () {
        if ($.isNumeric($(this).html())) {
            $(this).parseNumber({format: "##0.00%", locale: "us"});
            $(this).formatNumber({format: "##0.00%", locale: "us"});
        }
    });
}