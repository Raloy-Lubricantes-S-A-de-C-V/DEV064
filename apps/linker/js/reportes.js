$(document).ready(function () {
    
    $("#menu div").click(function () {
        window.location.href = $(this).attr("page");
    });
    $("#tabCostoOC").addClass("tabselected");
    
    
    var firstDay = new Date(new Date().getFullYear()-1, new Date().getMonth(), 1);
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
    $("#mostrarReporteEv").click(function () {
        $("#loadingStatus").show();
        getSabana();
    });
    $("#loadingStatus").hide();
});
function arraySum(arr) {
    var sum = 0;
    $.each(arr, function (i, v) {
        if (typeof v[1] === "number") {
            sum = eval(sum + v[1]);
        }
    });
    return sum;
}

function getSabana() {
    var file = "php/functionsReportes.php";
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
                    $("#sabana").empty().html(
                            "<h2>Sábana de datos</h2><div><table id='sabanaTable'>" + proceso.tabla + "</table></div>");
                } else {
                    alert(proceso.errors);
                    return;
                }
            },
            "json")
            .done(function () {
                $("#sabanaTable").dataTable({
                    destroy: true,
                    dom: 'Bfrtip',
                    buttons: [
                        'copyHtml5',
                        'excelHtml5',
                        'pdfHtml5'
                    ],
                    scrollY: "500px",
                    scrollX: "100%",
                    "order": [[ 0, "desc" ]]
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