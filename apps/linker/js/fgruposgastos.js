$(document).ready(function () {

    $("#menu div").click(function () {
        window.location.href = $(this).attr("page");
    });
    $("#tabSesiones").addClass("tabselected");
    dimeGrupos();
});

function dimeGrupos() {
    var file = "php/fgruposgastos.php";
//New plot 
    var param = {
        fase: "dimeGrupos"
    };
    $.get(
            file,
            param,
            function (proceso) {
                if (proceso.status === 1) {
                    $("#sabana").empty().html(
                            "<h2>Grupos de Gastos</h2><div>" + proceso.table + "</div>");
                } else {
                    alert(proceso.errors);
                    return;
                }
            },
            "json")
            .done(function () {
                $("#sabanaTable").dataTable({
                    destroy: true,
                    "order": [[0, "desc"]],
                    "columnDefs": [{ "width": "20px", "targets": 0 }]
                });
                $("#loadingStatus").hide();
                $(".edittr").off("click").on("click",function(){
                    savechanges($(this).attr("idgrupo"));
                });
                $(".deletetr").off("click").on("click",function(){
                    deletegrupo($(this).attr("idgrupo"));
                });
            });
}