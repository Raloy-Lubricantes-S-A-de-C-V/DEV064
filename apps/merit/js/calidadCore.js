$(document).ready(function () {
    botonesInicio();
});
function botonesInicio() {

    //Cambia el color de los botones con la acción de click
    $(".grupoOpciones .btnOpcion").click(function () {
        $(".grupoOpciones .btnOpcion").removeClass("btnClicked");
        $(this).addClass("btnClicked");
    });

    //OM
    $("#btnRegOMRR").click(
            function () {
                $("#optRegOMRR").toggle(300, function () {
                    if ($(this).is(':visible')) {
                        $("#numLote").change(function () {
                            dimeOMLote();
                        }).focus();
                        //botón de información acerca de cómo se construye el número de lote
                        $("#displayInfoCL").click(function () {
                            $("#infoConstructLote").toggle().css("top", eval($(this).position().top + $(this).height()) + "px");
                        })
                                .css("cursor", "pointer");
                        //ddmmYYSTMM
                    } else {
                        $(".grupoOpciones .btnOpcion").removeClass("btnClicked");
                    }
                });
            });

    //PT
    $("#btnRegOE").click(
            function () {
                $("#optRegOE").toggle(300, function () {
                    if ($(this).is(':visible')) {
                        $("#numLoteOE").change(function () {
                            dimeOELote();
                        }).focus();

                        //ddmmYYSTMM
                    } else {
                        $(".grupoOpciones .btnOpcion").removeClass("btnClicked");
                    }
                });
            });



    //Tache para cerrar las cajas de opciones        
    $(".innerOpcionesClose").on("click", function () {
//                $(this).parent().parent().hide();
        $(this).parent().parent().parent().find(".btnOpcion").click();
    });
}
function dimeOMLote() {
    var numLoteUpper = $("#numLote").val().toUpperCase();
    $("#numLote").val(numLoteUpper);
    if ($("#numLote").val() === "") {
        alert("Por favor ingrese un lote válido");
        return;
    }
    limpiaForm();
    var numLote = $("#numLote").val();
    dimeOMsTraz(numLote);
    dimeOMsSCP(numLote);
}
function dimeOELote() {
    var numLoteOE = $("#numLoteOE").val().toUpperCase();
    $("#numLoteOE").val(numLoteOE);
    if ($("#numLoteOE").val() === "") {
        alert("Por favor ingrese un lote válido");
        return;
    }

    var file = "php/functions.php";
    var param = {
        fase: "dimeOELote",
        numLote: numLoteOE,
        cveProducto: $("#cveProductoOE").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 2) {
            $("#tbl_oMezc tbody").html("<tr id='aviso'><td colspan='4'>No hay órdenes confirmadas por agregar a este Lote. Puede agregar otras órdenes haciendo click en el botón <i class='fa fa-plus-circle'></i> ubicado en la esquina superior derecha de este formulario.</td></tr>");
        } else if (proceso.status === 1) {
            $("#tbl_oMezc tbody").html(proceso.tbody);
            $("#tbl_oMezc tfoot").html(proceso.tfoot);
            $("#cveProducto").removeAttr("unidad").attr("unidad", proceso.unidad);
        } else {
            console.log(proceso.error);
            alert("Por favor revise la conexión a internet y vuelva a intentarlo");
        }
    }, "json")
            .done(function () {
                manageLinesFunct();
                $("#btnGuardarLote").unbind("click").click(function () {
                    validaGuardarCambiosLote();
                });
            }
            );
}

function limpiaForm() {
    $("#tbl_oMezc tbody tr").remove();
    $("#tbl_oMezc tbody").html("");
    $("#tbl_oMezc tfoot").html("");
    $("#OMsWMS").html("");
    $("#btnGuardarLote").unbind("click");
}
function dimeOMsSCP(numLote) {
    var file = "php/functions.php";
    var param = {
        fase: "dimeOMsSCP",
        numLote: numLote,
        cveProducto: $("#cveProducto").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 2) {
            $("#tbl_oMezc tbody").html("<tr id='aviso'><td colspan='4'>No hay órdenes confirmadas por agregar a este Lote. Puede agregar otras órdenes haciendo click en el botón <i class='fa fa-plus-circle'></i> ubicado en la esquina superior derecha de este formulario.</td></tr>");
        } else if (proceso.status === 1) {
            $("#tbl_oMezc tbody").html(proceso.tbody);
            $("#tbl_oMezc tfoot").html(proceso.tfoot);
            $("#cveProducto").removeAttr("unidad").attr("unidad", proceso.unidad);
        } else {
            console.log(proceso.error);
            alert("Por favor revise la conexión a internet y vuelva a intentarlo");
        }
    }, "json")
            .done(function () {
                manageLinesFunct();
                $("#btnGuardarLote").unbind("click").click(function () {
                    validaGuardarCambiosLote();
                });
            }
            );

}
function dimeOMsTraz(numLote) {
    var file = "php/functions.php";
    var param = {
        fase: "dimeOMsTraz",
        numLote: numLote,
        cveProducto: $("#cveProducto").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 2) {
            $("#OMsWMS").html("No existen datos del lote, por favor verifique que se trata de un NUEVO REGISTRO");
        } else if (proceso.status === 1) {
            $("#OMsWMS").html(proceso.datos);

        } else {
            console.log(proceso.error);
            alert("Por favor revise la conexión a internet y vuelva a intentarlo");
        }
    }, "json")
            .done(function () {
                activaAdminOM();
            }
            );
}
function activaAdminOM() {
    //removeOMezc
    $(".btnRemoveOMezc").unbind("click").click(function () {
        var resp = confirm("¡Cuidado! Va a eliminar la OC " + $(this).attr("oMezc") + " del lote " + $(this).attr("numLote") + " ¿Está Seguro?");
        if (resp === true) {
            removeOMezc($(this));
        } else {
            return;
        }

    });

    $("#omezc").change(function () {

    });
}
function manageLinesFunct() {
    $("#addLine").unbind("click");
    $("#btnsManageLines").remove(); //por protección, aunque el objeto no debe existir por ser parte de tbl_oMezc
    var buttons = "<tr id='btnsManageLines'><td colspan='4' id='manageLines'><span class='espaciado'><i id='addLine' class='fa fa-plus-circle' style='cursor:pointer; font-size:11pt;color: #306BA3'></i></span></td></tr>";
    $("#tbl_oMezc thead").append(buttons);

    $("#addLine").unbind("click").click(function () {
        addLine();
    });
}
function addLine() {
    var select = "<select  class='selOMRR newSel'><option value='OM' selected='selected'>OM</option><option value='RR'>RR</option></select>";
    var newLine = $("<tr class='newLine'><td>" + select + "</td><td><input type='text'class='tdOMezc' value=''/><td><input type='text' class='tdNumLote' value='" + $("#numLote").val() + "'/></td></td><td><input type='text' class='tdQty' value=''/></td></tr>");
    $("#tbl_oMezc tbody").append(newLine);

    //Acción al ingresar orden de mezcla nueva
    newLine.find(".tdOMezc")
            .unbind("change")
            .change(function () {
                dimeDatosOM($(this));
            })
            .focus();

    //Se elimina el aviso
    $("#aviso").remove();
    //Acciones dependiendo el tipo de orden
    $(".newSel").change(function () {
        if ($(this).val() === "RR") {
            var obj = $(this).parent().parent().find(".tdOMezc");
            obj.val($("#numLote").val());
            obj.prop("disabled", "disabled");
            var objNL = $(this).parent().parent().find(".tdNumLote");
            alert("¡ADVERTENCIA!\nLAS MODIFICACIONES REALIZADAS CON REMANENTES REUTILIZADOS (RR) NO PODRÁN SER REVERTIDAS UNA VEZ QUE GUARDE LOS CAMBIOS");
            objNL.val("").focus();
            obj.unbind("change");
        } else {
            var objNL = $(this).parent().parent().find(".tdNumLote");
            objNL.val($("#numLote").val());
            var obj = $(this).parent().parent().find(".tdOMezc");
            obj.val("").prop("disabled", false).focus();
            obj.unbind("change").change(function () {
                dimeDatosOM($(this))
            });
        }
    });


    //Botón para eliminar líneas añadidas
    if ($(".newLine").length === 1) {
        $("#manageLines").append("<span id='removeLine' class='espaciado'><i class='fa fa-minus-circle' style='cursor:pointer; font-size:11pt;color: #f4684d'></span>");
        $("#removeLine").unbind("click").click(function () {
            rmvLastLine();
        });
    }
}
function dimeDatosOM(obj) {
    var selVal = obj.parent().parent().find(".newSel").val();
    if (selVal !== "OM") {
        console.log(selVal);
        return;
    }

    var param = {
        fase: "dimeDatosOM",
        oMezc: obj.val()
    };
    var file = "php/functions.php";

    $.get(file, param, function (proceso) {
        if (proceso.status === 2) {
            alert("No se encontraron datos de la orden, por favor verifique");
            obj.select();
        } else if (proceso.status === 1) {
            obj.parent().parent().find("tdQty").val(proceso.qty).attr("qtyEnLaOrden", proceso.qty);

        } else {
            console.log(proceso.error);
            alert("Error al tratar de obtener la información de la orden ingresada");
            obj.select();
        }
    }, "json");
    return;
}
function rmvLastLine() {
    if ($(".newLine").length > 1) {
        $(".newLine").last().remove();
    } else if ($(".newLine").length === 1) {
        $(".newLine").last().remove();
        $("#removeLine").remove();
        //Se muestra el aviso
        if ($(".oldRow").length === 0) {
            $("#tbl_oMezc tbody").html("<tr id='aviso'><td colspan='4'>No hay órdenes confirmadas por agregar a este Lote. Puede agregar otras órdenes haciendo click en el botón <i class='fa fa-plus-circle'></i> ubicado en la esquina superior derecha de este formulario.</td></tr>");
        }
    }

}
function removeOMezc(obj) {
    var param = {
        fase: "removeOMezc",
        numLote: obj.attr("numLote"),
        oMezc: obj.attr("oMezc"),
        idreg: obj.attr("idreg")
    };
    var file = "php/functions.php";
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            dimeOMLote();
        } else {
            console.log(proceso.error);
            alert("Ha ocurrido un error");
        }
    }, "json");
}
function validaGuardarCambiosLote() {
    var trs = $("#tbl_oMezc tbody tr");
    var old_trs = $(".oldRow");
    var sumas = new Array();
    var lotes = new Array();
    var continuar = 1;
    //suma las órdenes de mezcla independientemente del lote para verificar que las cantidades coincidan
    trs.each(function () {
        var om = $(this).find(".tdOMezc").val();
        var qty = $(this).find('.tdQty').val();

        //Valida que no existan dos o más órdenes de mezcla con el mismo número de lote
        var lote = $(this).find('.tdNumLote').val();
        if (lotes["OM" + om + "L" + lote] === 1) {
            alert("Falló la operación por existir más de una orden de mezcla con el mismo lote");
            continuar = 0;
            return true;
        } else {
            lotes["OM" + om + "L" + lote] = 1;
        }
        if (qty !== "") {
            if (sumas[om] > 0) {
                sumas[om] = eval(sumas[om]) + eval(qty);
            } else {
                sumas[om] = eval(qty);
            }
        }
    });
    if (continuar === 0) {
        return;
    }
    //valida que las cantidades no sean menores o mayores a las de la orden de mezcla
    old_trs.each(function () {
        var qtyOriginal = $(this).find('.tdQty').attr("qtyEnLaOrden");
        var qtyActual = $(this).find('.tdQty').val();
        var OMezcActual = $(this).find(".tdOMezc").val();
        var claseOMezc = ".OM" + OMezcActual;
        if (qtyActual !== "") {
            //Valida si las cantidades no exceden las de la orden de mezcla
            if (eval(sumas[OMezcActual]) > eval(qtyOriginal)) {
                alert("La cantidad ingresada excede la cantidad de la Orden " + $(this).find(".tdOMezc").val() + "\nNo se han realizado los cambios");
                $(this).find('.tdQty').val(qtyOriginal).select();
                continuar = 0;
                return true;
            }
            //Valida si las cantidades no son menores a las de la OM, si lo son, valida que el restante de la OM esté en otro lote
            if (eval(sumas[OMezcActual]) < eval(qtyOriginal)) {
                alert("La cantidad ingresada es menor a la de la Orden " + $(this).find(".tdOMezc").val() + " y no se encuentra asignación del restante a otro número de lote\nNo se han realizado los cambios");
                $(this).find('.tdQty').val(qtyOriginal).select();
                continuar = 0;
                return true;
            }
        }
    });

    if (continuar === 0) {
        return;
    } else {
        guardarCambiosLote();
    }

}
function guardarCambiosLote() {
    var arrValues = new Array();
    var trs = $("#tbl_oMezc tbody tr");
    //Busca los datos y crea los inserts para enviar a PHP
    trs.each(function () {
        if ($(this).find('.tdQty').val() !== "") {
            if ($(this).find('.selOMRR').val() === "RR") {
                //Si es una reutilización de remanente, el remanente se resta al inventario del lote base y se agrega a otro número de lote.
                //Se realizan dos movimientos: el de resta al lote base y el de adición al nuevo lote
                var values = "";
                //Resta
                values += "(";
                values += "'" + $(this).find('.selOMRR').val() + "',"; //om_oe_rr Tipo de origen (orden de mezcla, orden de ensamble, reutilización de remanente
                values += "'" + $(this).find('.tdOMezc').val() + "',"; //Número de lote
                values += "'" + $(this).find('.tdNumLote').val() + "',";//Número de Orden
                values += "-" + $(this).find('.tdQty').val() + ","; //Cantidad
                values += "'" + $("#cveProducto").val() + "',"; //clave del producto
                values += "'" + $("#cveProducto").attr("unidad") + "',"; //unidad del producto
                values += "'" + $("#userSession").html() + "'"; //Usuario que realizó los cambios
                values += "),";

                //Adición
                values += "(";
                values += "'" + $(this).find('.selOMRR').val() + "',"; //om_oe_rr Tipo de origen (orden de mezcla, orden de ensamble, reutilización de remanente
                values += "'" + $(this).find('.tdNumLote').val() + "',"; //Número de lote (Lote nuevo)
                values += "'" + $(this).find('.tdOMezc').val() + "',";//Número de Orden o documento (Lote origen)
                values += $(this).find('.tdQty').val() + ","; //Cantidad
                values += "'" + $("#cveProducto").val() + "',"; //clave del producto
                values += "'" + $("#cveProducto").attr("unidad") + "',"; //unidad del producto
                values += "'" + $("#userSession").html() + "'"; //Usuario que realizó los cambios
                values += ")";

            } else {
                var values = "(";
                values += "'" + $(this).find('.selOMRR').val() + "',"; //om_oe_rr Tipo de origen (orden de mezcla, orden de ensamble, reutilización de remanente
                values += "'" + $(this).find('.tdNumLote').val() + "',"; //Número de lote
                values += $(this).find('.tdOMezc').val() + ",";//Número de Orden o documento
                values += $(this).find('.tdQty').val() + ","; //Cantidad
                values += "'" + $("#cveProducto").val() + "',"; //clave del producto
                values += "'" + $("#cveProducto").attr("unidad") + "',"; //unidad del producto
                values += "'" + $("#userSession").html() + "'"; //Usuario que realizó los cambios
                values += ")";
            }
        }
        arrValues.push(values);
    });

    var strValues = arrValues.join(",");
    console.log(strValues);
    var param = {
        fase: "guardarCambiosLote",
        strValues: strValues
    };
    var file = "php/functions.php";
    $.get(file, param, function (proceso) {
//        console.log(proceso);
        if (proceso.status === 1) {
            dimeOMLote();
        } else {
            console.log(proceso.error);
            alert("Ha ocurrido un error");
        }
    }, "json");
}