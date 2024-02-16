function zk_ventasxFam(i) {
    var file = "php/functions.php";
    var param = {
        fase: "zk_ventasxFam",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val(),
        id: i,
        uSe: $("#uSe").val()
    };
    $.get(file, param, function (proceso) {
        $("#fec1Sp").html(proceso.fecSp.fec1);
        $("#fec2Sp").html(proceso.fecSp.fec2);
        $("#tfootth").attr("colspan", eval(proceso.sumCol + 1));
        $("#tabladevs").html(proceso.tabladevs);

//        $("#totalLts").html("TOTAL: <span class='numeric'>" + proceso.totalLts + "</span> LTS");
        var rows2 = proceso.filas;
        pivotT(rows2);
        pivotT_USD(proceso.filasusd);

        drawAreaChart(proceso.arrData, "chart_plantas");
        drawAreaChart(proceso.arrDataPres, "chart_presentaciones");
        drawAreaChart(proceso.arrDataImg, "chart_imagenes");

        creaAnalitico();
        $("#selAnalitico").change(function () {
            creaAnalitico();
        });

        resumenIncome();

//        zk_ventasxPlantaFam(i, 1);
    }, "json")
            .done(function () {

                formatNumbers();
            });
}
function resumenIncome(i) {
    var file = "php/functions.php";
    var param = {
        fase: "resumenIncome",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val(),
        id: i,
        uSe: $("#uSe").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            console.log(proceso.error);
            return;
        }

        $("#tblResumenIncome tbody").html(proceso.table.tbody);
        $("#tblResumenIncome tfoot").html(proceso.table.tfoot);
        $("#tblResumenIncome").dataTable({"paging": false});

    }, "json")
            .done(function () {

                formatNumbers();
            });
}

function formatNumbers() {
//    $(".resultcell,.resultcell0").addClass("numeric");
    $('.numeric,.resultcell,.total,.total0,.coltotal,.resultcell0').text(function () {
        if ($.isNumeric($(this).html())) {
            if ($(this).attr("title") === "USD") {
                $(this).parseNumber({format: "$#,##0.00", locale: "us"});
                $(this).formatNumber({format: "$#,##0.00", locale: "us"});
            } else {
                $(this).parseNumber({format: "#,###", locale: "us"});
                $(this).formatNumber({format: "#,###", locale: "us"});
            }
        }
    });
    $('.currency').text(function () {
        if ($.isNumeric($(this).html())) {
            $(this).parseNumber({format: "$#,##0.00", locale: "us"});
            $(this).formatNumber({format: "$#,##0.00", locale: "us"});

        }
    });
    $('.currency3').text(function () {
        if ($.isNumeric($(this).html())) {
            $(this).parseNumber({format: "$#,##0.000", locale: "us"});
            $(this).formatNumber({format: "$#,##0.000", locale: "us"});

        }
    });

}

function pivotT(rows) {
//        console.log(rows);
    $('#res').pivot({
        source: {
            columns: [
                {colvalue: "Year", coltext: "Year", header: "Year", sortbycol: "Year", groupbyrank: null, pivot: true, result: false},
                {colvalue: "Mes", coltext: "Mes", header: "Mes", sortbycol: "Mes", groupbyrank: null, pivot: true, result: false},
                {colvalue: "Present", coltext: "Present", header: "Present", sortbycol: "Present", groupbyrank: 2, pivot: false, result: false},
                {colvalue: "Planta", coltext: "Planta", header: "Planta", sortbycol: "Planta", dataid: "An optional id.", groupbyrank: 1, pivot: false, result: false},
                {colvalue: "Litros", coltext: "Litros", header: "Litros", sortbycol: "Litros", groupbyrank: null, pivot: false, result: true}],
            rows: rows

        },
        formatFunc: function (n) {
            formatNumbers();
            return n;
        },
//        parseNumFunc: function (n) {
//            return +((typeof n === "string") ? +n.replace('.', '').replace(',', '.') : n);
//        },
//        onResultCellClicked: function (data) {
//            alert(dumpObj(data, "data"));
//        },
        sortPivotColumnHeaders: true //we want months non sorted to get them in the right order.
    });
}
function pivotT_USD(rows) {
//        console.log(rows);
    $('#res_USD').pivot({
        source: {
            columns: [
                {colvalue: "Year", coltext: "Year", header: "Year", sortbycol: "Year", groupbyrank: null, pivot: true, result: false},
                {colvalue: "Mes", coltext: "Mes", header: "Mes", sortbycol: "Mes", groupbyrank: null, pivot: true, result: false},
                {colvalue: "Present", coltext: "Present", header: "Present", sortbycol: "Present", groupbyrank: 2, pivot: false, result: false},
                {colvalue: "Planta", coltext: "Planta", header: "Planta", sortbycol: "Planta", dataid: "An optional id.", groupbyrank: 1, pivot: false, result: false},
                {colvalue: "USD", coltext: "USD", header: "USD", sortbycol: "USD", groupbyrank: null, pivot: false, result: true}],
            rows: rows

        },
        formatFunc: function (n) {
            formatNumbers();
            return n;
        },
        sortPivotColumnHeaders: true //we want months non sorted to get them in the right order.
    });
}
function drawAreaChart(dataArray, containerId) {
    google.charts.load('current', {'packages': ['corechart']});

    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable(dataArray);

        var options = {
            title: "Litros",
            hAxis: {title: 'Periodo (AAAA-mm)', titleTextStyle: {color: '#333'}},
            vAxis: {minValue: 0},
            isStacked: true
        };

        var chart = new google.visualization.AreaChart(document.getElementById(containerId));
        chart.draw(data, options);
    }

    google.charts.setOnLoadCallback(drawChartPercent);
    function drawChartPercent() {
        var data = google.visualization.arrayToDataTable(dataArray);

        var options = {
            title: '%',
            hAxis: {title: 'Periodo (AAAA-mm)', titleTextStyle: {color: '#333'}},
            vAxis: {minValue: 0},
            isStacked: "percent"
        };

        var chart = new google.visualization.AreaChart(document.getElementById(containerId + '_perc'));
        chart.draw(data, options);
    }
}
function creaAnalitico() {
    var file = "php/functions.php";
    var param = {
        fase: "creaAnalitico",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val(),
        uSe: $("#uSe").val(),
        campo: $("#selAnalitico").val()
    };
    $.get(file, param, function (proceso) {
//        console.log(proceso);
        if (proceso.status === 1) {
            $("#tblAnalitico").dataTable().fnDestroy();
            $("#tblAnContainer").html("<table id='tblAnalitico'><thead><tr><th>Etiqueta</th><th>Promedio</th><th>&Uacute;ltimo mes</th><th>Delta Lts</th><th>Delta %</th></tr></thead><tbody></tbody></table>");
            $("#tblAnalitico tbody").html(proceso.tbody);
            $("#tblAnalitico").dataTable({
                destroy: true,
                "order": [[1, "desc"]],
                "fnDrawCallback": function (oSettings) {
                    formatNumbers();
                }
            });
        } else {
            console.log(proceso);
        }

    }, "json")
            .done(function () {
                formatNumbers();
            });
}