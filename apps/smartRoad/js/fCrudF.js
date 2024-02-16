var file = "php/fCrud.php";
$(document).ready(function () {
    $(".grupoEditable").hide();

    $(".selector button").click(function () {
        $("#cover").show();
        $(".selector button").removeClass("selected");
        $(this).addClass("selected");
        llamaFuncionAdmin($(this).attr("fase"));
    });
    $(".selector button").first().click();
    $("#btnSaveFlota").click(function () {
        $(this).prop("disabled", true);
        guardatablaAdminFlota();
    });
    llenanom012();
});
function llenanom012(){
    var param={
        "fase":"nom012"
    };
    $.get(file,param,function(respuesta){
        if(respuesta.status===1){
            $("#tiposnom012 tbody").html(respuesta.tbody);
        }else{
            $("#tiposnom012 tbody").html("<tr><td colspan='5'>"+respuesta.error+"</td></tr>");
        }
    },"json").done(function(){
        $(".radiotipos").off("click").on("click",function(){
            $(".radiotipos").prop("checked",false);
            $("#tiposnom012 tbody tr").removeClass("tiposeleccionado");
            $(this).prop("checked",true).parent().parent().addClass("tiposeleccionado");
        });
    });
}
function llamaFuncionAdmin(string) {
    switch (string) {
        case "flota":
            tablaAdminFlota();
            break;
        case "determinantes":
            tablaAdminDet();
            break;
        default:
            tablaAdminFlota();
    }
}
function tablaAdminFlota() {
    $(".grupoEditable").hide();

    var param = {
        fase: "tablaAdminFlota"
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        $("#divFlota").show();
        $('#tblAdminFlota tbody').html(proceso.tbody);

        var table = $('#tblAdminFlota').DataTable(
                {
                    "order": [[0, 'asc']],
                    destroy: true,
                    "scrollX": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    paging: false
                }
        );
        $(".inputFecha").datepicker({dateFormat: "yy-mm-dd"});
        $(".delAtq").click(function () {
            var r = confirm("Desea eliminar el registro?");
            if (r === true) {
                borraAtq($(this).attr("idAtq"));
            } else {
                return;
            }
        });
        $("#tblAdminFlota input").on("change", function () {
            $("#avisosFlota").html("Cambios sin guardar").show();
        });

    }, "json")
            .done(function () {
                $("#cover").hide();
            });
}
function guardatablaAdminFlota() {
    var arr_strValues = new Array();
    $("#tblAdminFlota tbody").children("tr").each(function (i, val) {
        var tds = $(val).children("td");
        var trValues = new Array();
        $(tds).each(function (i, v) {
            if (i !== 0) {
                var input = $(v).find("input");
                if (i === 1)
                    placas = $(input).val();
                if (placas !== "") {
                    var string = "";
                    if ($(input).hasClass("numeric") === true) {
                        var number = $(input).val().replace(/[^\d\.\-eE+]/g, "");
                        string = (eval(number) > 0) ? number : 0;
                    } else {
                        string = ($(input).val() !== undefined && $(input).val() !== "") ? "'" + $(input).val().trim() + "'" : "''";
                    }
                    trValues.push(string);
                }
            }

        });
        var inputs = $(val).find("input");
        var placas = $(inputs[0]).val();
        if (placas !== "") {
            arr_strValues.push("(" + trValues.join(",") + ")");
        }
    });
    var strValues = arr_strValues.join(",");
//    console.log(strValues);
    var param = {
        fase: "guardatablaAdminFlota",
        strValues: strValues
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        var table = $('#tblAdminFlota').DataTable();
        table.destroy();
    }, "json").done(function () {
        $("#avisosFlota").html("Cambios Guardados");
        setTimeout("$('#avisosFlota').html('')", 5000);
        $("#btnSaveFlota").prop("disabled", false);
        tablaAdminFlota();
    });
}
function borraAtq(idAtq) {

    var param = {
        fase: "borraAtq",
        idAtq: idAtq
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return;
        }
        var table = $('#tblAdminFlota').DataTable();
        table.destroy();
        tablaAdminFlota();
        $("#avisos").html("Cambios Guardados").show().fadeOut(5000);
    }, "json");

}