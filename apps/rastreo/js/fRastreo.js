$(document).ready(function() {

    getOrders()

    $("#searchBtn").click(function() {
        getOrders()
    })

})

function getOrders() {
    var param = {
        fase: "getOrders",
        folio: $("#folioInput").val(),
        numPedido: $("#numPedido").val()
    }
    $.get("php/fRastreo.php", param, function(respuesta) {
        if (respuesta.status == 1) {
            $("#resultTable tbody").html(respuesta.tbody)
        } else if (respuesta.status == 2) {
            $("#resultTable tbody").html("<tr colspan='10'>No se han encontrado pedidos</tr>")
        } else {
            $("#resultTable tbody").html("<tr colspan='10'>Error!</tr>")
            console.log(respuesta.error)
        }

    }, "json")
}