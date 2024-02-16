$(document).ready(function () {
    $("#searchCte").click(function () {
        getCustomerData();
    });
    $('#frmconsulta').submit(function (e) {
		e.preventDefault();
		reporteMostrar();
	});
});
function getCustomerData() {
    var param =
            {
                f: "getCustomerData",
                cveCte: $("#inputCveCte").val(),
                cveDte: $("#inputCveDte").val()
            };
    $.get(
            "php/functions.php",
            param,
            function (proceso) {
                if (proceso.status === 1) {
                    if (proceso.numRows === 0) {
                        alert("No existen registros con los datos proporcionados");
                        $("#inputCveCte").focus();
                        return;
                    }
//                    else if (proceso.numRows > 1) {
//                        alert("Existen varios registros con los datos proporcionados, contacte al administrador");
//                        $("#inputCveCte").focus();
//                        return;
//                    }

                    $.each(proceso.data, function (index, value) {
                        $("#nombreCliente").html(value.nombreCliente);
                        $("#descDeterminante").html(value.descDeterminante);
                        $("#domicilioFiscal").html(value.domicilioFiscal);
                        $("#domicilioEnvio").html(value.domicilioEnvio);
                        $("#infoConsumoCliente tbody").html("<tr><td>" + value + "</td><td>" + value.Clasificiacion + "</td><td></td><td>" + value.Litros + "</td><td></td></tr>");
                    });

//                    $("#litrosCteAc").html(eval(proceso.data.ltsCteAc/12));
//                    $("#litrosCteSk").html(eval(proceso.data.ltsCteSk/12));
//                    $("#litrosCteAn").html(eval(proceso.data.ltsCteAn/12));
//                    $("#litrosDteAc").html(eval(proceso.data.ltsDteAc/12));
//                    $("#litrosDteSk").html(eval(proceso.data.ltsDteSk/12));
//                    $("#litrosDteAn").html(eval(proceso.data.ltsDteAn/12));
                } else {
                    alert("Error:" + proceso.error);
                    return;
                }
            },
            "json");
}
function model() {
    var param =
            {
                fase: "getCustomerData",
                cveCte: "",
                cveDte: ""
            }
    $.get(
            "php/functions.php",
            param,
            function (proceso) {

            },
            "json");
}


