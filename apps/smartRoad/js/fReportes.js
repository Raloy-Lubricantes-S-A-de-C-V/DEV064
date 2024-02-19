var file = "php/fReportes.php";
$(document).ready(function () {
    mostrarReporte();
    $("#subHeader2").find("a").click(function () {
        var reporte = $(this).attr("valor");
        $("#reporteMostrar").val(reporte);
        mostrarReporte();
    });
    $("#reload").click(function(){mostrarReporte();});
});
function mostrarReporte() {
    var r = $("#reporteMostrar").val();
    $(".reporter").hide();
    switch (r) {
        case "dashboard":
            $("#dashboard").show(200);
            dashboard();
            break;
        case "cargas":
            $("#cargas").show(200);
            cargas();
            break;
        case "entregas":
            $("#entregas").show(200);
            entregas();
            break;
        case "costoxciudad":
            $("#costoxciudad").show(200);
            costoxciudad();
            break;
        default:
            $("#dashboard").show(200);
            ltsxtransporte();
    }
}
function dashboard() {
    ltsxtransporte();
    //funciones de botones
    $(".chartOptions button").click(function () {
        //div contenedor de la tarjeta
        var divCard = $(this).parent().parent();

        //div contenedor de la tabla
        var divCtrTable = $(this).parent().parent().find(".tableCtr");

        var widthCard = divCard.width();
        var offsetCard = divCard.offset();
        var topCtrTable = eval(offsetCard.top + 20);
        var leftCtrTable = eval(offsetCard.left + widthCard);

        divCtrTable.offset({left: leftCtrTable}).toggle(200);
    });
}
function ltsxtransporte() {
    var param = {
        fase: "ltsxtransporte"
    };
    $.get(file, param, function (proceso) {
        var barChartData = {
            labels: proceso.labels,
            datasets: proceso.datasets
        };
        var ctx = $("#chart1");
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: barChartData,
            options: {
                title: {
                    display: true,
                    text: 'Litros entregados por línea de transporte'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                responsive: true,
                scales: {
                    xAxes: [{
                            stacked: true,
                        }],
                    yAxes: [{
                            stacked: true
                        }]
                }
            }
        });

        $("#tableCtr_Chart1")
                .html("<table>" + proceso.tablaDatos + "</table>")
                .find("table")
                .dataTable();
    }, "json");

}
function llenaSelReportes() {
    var param = {
        fase: "llenaSelReportes",
        usuario: $("#userSession").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            $("#selReportes").html(proceso.options);
        } else {
            alert(proceso.error);
        }

    }, "json");

}
function crearReporte() {
    if ($("#fechaDel").val() !== "" && $("#fechaAl").val() !== "" && $("#selReportes").val() !== "") {
        alert($("#selReportes").val());
    } else {
        alert("Seleccione valores válidos");
    }
}
function cargas() {
    var param = {
        fase: "cargas"
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            $('#calCargas').fullCalendar(proceso.data);
        } else {
            alert(proceso.error);
        }

    }, "json");

}
function entregas() {

    var param = {
        fase: "entregas"
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            $('#calEntregas').fullCalendar(proceso.data);
        } else {
            alert(proceso.error);
        }

    }, "json");

}
function costoxciudad() {

    var param = {
        fase: "costoxciudad"
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            $('#costoxciudadTblCtr').html(proceso.table);
            $("#costosxcd").dataTable({
                destroy: true,
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'pdfHtml5'
                ],
                paging:false
            });
        } else {
            alert(proceso.error);
        }

    }, "json");

}