$(document).ready(function () {
    $("#menu div").click(function () {
        window.location.href = $(this).attr("page");
    });
    $("#tabOCSinLink").addClass("tabselected");
    
    var firstDay = new Date(new Date().getFullYear(), 0, 2);
    var today = new Date();
    $("#fec1").datepicker({
        dateFormat: "yy-mm-dd"
    })
            .datepicker("setDate", firstDay);
    $("#fec2").datepicker({
        dateFormat: "yy-mm-dd"
    })
            .datepicker("setDate", today);
    ;
    $("#mostrarOCs").click(function () {
        getSabana();
    });
    $("#loadingStatus").hide();
});

function getSabana() {
    var file = "php/funcOcSinLink.php";

//New plot 
    var param = {
        fase: "getSabana",
        material: $("#material").val(),
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val()
    };
    $.get(
            file,
            param,
            function (proceso) {
                if (proceso.status === 1) {
                    $("#sabanaTable").empty();
                    $("#sabanaTable").html(proceso.tabla);

                } else {
                    alert(proceso.errors);
                    return;
                }
            },
            "json")
            .done(function () {
                $("#sabana").prepend("<h2>Sábana de datos</h2>");
                $("#sabanaTable").dataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copyHtml5',
                        'excelHtml5',
                        'pdfHtml5'
                    ],
                    scrollY: "500px",
                    scrollX: "100%"
                });
                formatNumbers();
                $("#loadingStatus").hide();
            });
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