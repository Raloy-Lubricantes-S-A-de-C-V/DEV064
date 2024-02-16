$(document).ready(function () {
    $("contenedorInfoOC").toggle();
    infoOC();
    $("#historialRec").dataTable();
    $("#barCode").focus();
});
function infoOC() {
    var file = "php/functionsIndex.php";
    
    if ($("#cveProv").val() === "" || $("#oc").val() === "" || $("#material").val() === "") {
        window.location.href="index.php";
        return;
    }

    var param = {
        fase: "infoOC",
        cveProv: $("#cveProv").val(),
        oc: $("#oc").val(),
        material: $("#material").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 2) {
            alert("No existen registros con los datos proporcionados");
        } else if (proceso.status === 1) {
            $("#contenedorInfoOC").html(proceso.datos);
        } else {
            console.log(proceso.error);
            alert("Por favor revise la conexión a internet y vuelva a intentarlo");
        }
    }, "json")
            .done(function () {
                $("#recibirOC")
                        .remove();
            }
            );
}