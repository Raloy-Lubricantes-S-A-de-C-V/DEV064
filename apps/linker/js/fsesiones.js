$(document).ready(function () {

    $("#menu div").click(function () {
        window.location.href = $(this).attr("page");
    });
    $("#tabSesiones").addClass("tabselected");
    getSesiones();
});

function getSesiones() {
    var file = "php/fsesiones.php";
//New plot 
    var param = {
        fase: "getSesiones"
    };
    $.get(
            file,
            param,
            function (proceso) {
                if (proceso.status === 1) {
                    $("#sabana").empty().html(
                            "<h2>Sesiones</h2><div><table id='sabanaTable'>" + proceso.tabla + "</table></div>");
                } else {
                    alert(proceso.errors);
                    return;
                }
            },
            "json")
            .done(function () {
                $("#sabanaTable").dataTable({
                    destroy: true,
                    "order": [[0, "desc"]]
                });
                $("#loadingStatus").hide();
            });
}