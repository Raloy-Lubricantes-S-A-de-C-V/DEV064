var file = "php/fCrud.php";
$(document).ready(function () {
    tablaAdminDet();
    $("#btnSaveDet").click(function () {
        $("#cover").show();
        $("#avisosDet").append(" <i class='fa fa-spinner fa-spin'></i> Guardando");
        $(this).prop("disabled", true);
        guardatablaAdminDet();
    });
    $(".shRight").click(function () {
        if ($(".hasChanged").length > 0) {
            var r = confirm("Desea guardar los cambios antes de actualizar?");
            if (r === true) {
                $("#btnSaveDet").click();
            } else {
                window.location.reload();
            }
        } else {
            window.location.reload();
        }
    });
});

function tablaAdminDet() {
    $(".grupoEditable").hide();

    var param = {
        fase: "tablaAdminDet"
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        $("#divDet").show();

        $('#tblAdminDet tbody').html(proceso.tbody);
        $("#filterDet").off("keyup").on("keyup", function () {
            var value = $(this).val().toLowerCase();
            $("#tblAdminDet tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        $(".btnSelEdos").off("click").on("click", function () {
            selectEdos($(this));
        });
        $("#tblAdminDet tbody input").on("change", function () {
            $(this).parent().parent().addClass("hasChanged");
        });
    }, "json").done(function () {
        $("#cover").hide();
        $('#tblAdminDet').dataTable({"searching": true, scrollY: "100%", "scrollX": "100%", "order": [[5, "asc"], [6, "asc"]]})
                .find("tbody tr .moredetails").off("click").on("click", function () {
            window.open("Detsheet.php?iddet=" + $(this).attr("iddet"));
        });
    });
}
function guardatablaAdminDet() {//--LZC
    if ($(".hasChanged").length === 0) {
        return false;
    }
    var arrLines = new Array();
    $("#tblAdminDet tbody").find(".hasChanged").each(function (i, tr) {
        var idEM = $(tr).find(".idEM").val();
        var idunico = $(tr).attr("idunico");
        var capac = ($(tr).find(".txtcapacity").val() > 0) ? $(tr).find(".txtcapacity").val() : 0;
        var tanques = $(tr).find(".txttanques").val();
        var coa = ($(tr).find(".txtformatocoa").val() > 1) ? $(tr).find(".txtformatocoa").val() : 1;
        if (idEM === "" || idEM === undefined || idEM === null || idunico === null || idunico === "" || idunico === undefined) {
            alert("Error");
            return false;
        } else {
            arrLines.push([idunico, idEM, capac, tanques, coa]);
        }
    });
    var param = {
        fase: "guardatablaAdminDet",
        lines: arrLines
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        var table = $('#tblAdminDet').DataTable();
        table.destroy();
    }, "json").done(function () {
        $("#avisosDet").html("Cambios Guardados");
        setTimeout("$('#avisosDet').html('')", 5000);
        $("#btnSaveDet").prop("disabled", false);
        tablaAdminDet();
    });
}
function selectEdos(obj) {
    var valorActual = $(obj).attr("valor");
    var htmlActual = $(obj).html();
    console.log(valorActual);
    var param = {
        fase: "selectEdos",
        selected: valorActual
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }

        var select = "<select id='selEdo'>" + proceso.options + "</select><button id='cancelSelEdo'>Cancelar</button>";
        $(obj).parent().html(select);
        $("#cancelSelEdo").click(function () {
            $(this).parent().html("<button valor='" + valorActual + "' class='btnSelEdos'>" + htmlActual + "</button>");
            $(".btnSelEdos").unbind("click").click(function () {
                selectEdos($(this));
            });
        });
        $("#selEdo").change(function () {
            var trParent = $(this).parent().parent();
            var valorSeleccionado = $(this).val();
            $(this).parent().html("<button valor='" + valorSeleccionado + "' class='btnSelEdos'>" + valorSeleccionado + "</button>");
            $(".btnSelEdos").unbind("click").click(function () {
                selectEdos($(this));
            });
            selectMpios(valorSeleccionado, trParent);
        }).focus();
    }, "json");

}
function selectMpios(edo, trParent) {
    $(trParent).find(".idEM").val("");
    var selMpio = $(trParent).find(".selMpios");
    if (edo === "" || edo === null) {
        alert("Seleccione un Estado");
        return;
    }
    var param = {
        fase: "selectMpios",
        edo: edo
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        $(selMpio)
                .html(proceso.options)
                .unbind("change")
                .on("change", function () {
                    $("#avisosDet").html("Cambios sin guardar").show();
                    $(trParent).addClass("hasChanged");
                    $(trParent).find(".idEM").val($(this).val());
                })
                .focus();

    }, "json");

}