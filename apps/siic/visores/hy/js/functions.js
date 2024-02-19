function r_ventasEvol() {
    var file = "php/functions.php";
    var param = {
        fase: "r_ventasEvol",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val()
    };
    $.get(file, param, function (proceso) {
        var data = [];
        var ykeys = [];
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $.each(proceso.ykeys, function (key, value) {
            ykeys.push(value);
        });
        $.each(proceso.data, function (key, value) {
            data.push(value);
        });
        Morris.Line({
            element: 'salesChart',
            data: data,
            xkey: 'm',
            ykeys: ykeys,
            labels: ykeys,
            hideHover: "auto",
            xLabelMargin: 10,
            ymax: 1600000,
            lineColors: proceso.strokeColors,
            xLabelFormat: function (x) { // <--- x.getMonth() returns valid index
                var month = months[x.getMonth()];
                return month;
            },
            dateFormat: function (x) {
                var month = months[new Date(x).getMonth()];
                return month;
            }
        });
        $("#salesLegend").html(proceso.table);
        $('.numeric').text(function () {
            var str = $(this).html() + '';
            x = str.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            $(this).html(x1 + x2);
        });
        $('.porc').text(function () {
            var str = $(this).html() + '';
            x = str.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            if (x1 === "0") {
                x1 = "";
            }
            $(this).html(x1 + Math.round(x2 * 10000) / 100 + "%");
        });
    }, "json");
}
function r_ventasxArea() {
    var file = "php/functions.php";
    var param = {
        fase: "r_ventasxArea",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val()
    };
    $.get(file, param, function (proceso) {
        var data = [];
        var ykeys = [];
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $.each(proceso.ykeys, function (key, value) {
            ykeys.push(value);
        });
        $.each(proceso.data, function (key, value) {
            data.push(value);
        });
        Morris.Line({
            element: 'salesChart',
            data: data,
            xkey: 'm',
            ykeys: ykeys,
            labels: ykeys,
            hideHover: "auto",
            xLabelMargin: 10,
            ymax: 1300000,
            lineColors: proceso.strokeColors,
            xLabelFormat: function (x) { // <--- x.getMonth() returns valid index
                var month = months[x.getMonth()];
                return month;
            },
            dateFormat: function (x) {
                var month = months[new Date(x).getMonth()];
                return month;
            }
        });
        $("#salesLegend").html(proceso.table);
        $('.numeric').text(function () {
            var str = $(this).html() + '';
            x = str.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            $(this).html(x1 + x2);
        });
        $('.porc').text(function () {
            var str = $(this).html() + '';
            x = str.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            if (x1 === "0") {
                x1 = "";
            }
            $(this).html(x1 + Math.round(x2 * 10000) / 100 + "%");
        });
    }, "json");
}

function zk_ventasxPlantaFam(i) {
    var file = "php/functions.php";
    var param = {
        fase: "zk_ventasxPlantaFam",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val(),
        id: i,
        uSe: $("#uSe").val()
    };
    $.get(file, param, function (proceso) {
        $("#fec1Sp").html(proceso.fecSp.fec1);
        $("#fec2Sp").html(proceso.fecSp.fec2);
        $("#tfootth").attr("colspan", eval(proceso.sumCol + 1));
        $("#mainTable").dataTable({
            dom: 'Bfrtip',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'pdfHtml5'
            ],
            data: proceso.data,
            columns: proceso.columns,
            "paging": false,
            "bSort": false,
            fixedHeader: {
                header: true,
                footer: false
            },
            caption: "VENTAS POR FECHA Y ARTÍCULO",
            "aoColumnDefs": [
                {"sClass": "numeric", "aTargets": proceso.numericCols} //añadir clase de numeric a las columnas definidas en functions.php
            ],
            "footerCallback": function (tfoot, data, start, end, display) {
                var api = this.api();

                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                };

                // Total over all pages
                total = api
                        .column(proceso.sumCol)
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                // Total over this page
                pageTotal = api
                        .column(proceso.sumCol, {page: 'current'})
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                var numFormat = $.fn.dataTable.render.number('\,', '.', 0, '').display;
                // Update footer
                $(api.column(proceso.sumCol).footer()).html(
                        'Total mostrado:' + numFormat(pageTotal) + 'Lts / Total General:(' + numFormat(total) + ' Lts)'
                        );
            }


        });
        $('.numeric').text(function () {
            if ($.isNumeric($(this).html())) {
                $(this).parseNumber({format: "#,###", locale: "us"});
                $(this).formatNumber({format: "#,###", locale: "us"});
            }
        });

    }, "json");
}
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

        $("#totalLts").html("TOTAL: <span class='numeric'>" + proceso.totalLts + "</span> LTS");

        var data = [];
        var dataBar = [];
        var tableBar = "";
        //Crear charts
        //
        //Por planta
        $.each(proceso.porPlanta, function (key, value) {
            data.push(value);
            dataBar.push({p: value.label, v: value.value});
            tableBar += "<tr><td>" + value.label + "</td><td>" + value.value + "</td></tr>";
        });
        Morris.Donut({
            element: 'plantaChart',
            data: data,
            resize: true
        });

//        Morris.Bar({
//            element: 'plantaBar',
//            data: dataBar,
//            xkey: 'p',
//            ykeys: ['v'],
//            labels: ['Litros']
//        });
//        $("#PlantaTable tbody").html(tableBar);
        var dataSC = new Array();
        $.each(proceso.porPlantaYPtn, function (key, value) {
            var dataPoints = new Array();
            //Quitar las llaves del objeto datapoints
            $.each(value.dataPoints, function (k, v) {
                dataPoints.push(v);
            });
            //Insertar propiedades en el objeto dataSC
            dataSC.push({
                type: "stackedColumn",
                name: value.name,
                showInLegend: true,
                dataPoints: dataPoints
            });
        });
        console.log(dataSC);
        
        $("#plantaBar").CanvasJSChart({
            title: {
                text: "Ventas por planta y presentación"
            },
            axisY: {
                title: "Litros"
            },
            toolTip: {
                content: "{label} <br/>{name} : {y} Litros / 45000"
            },
            data: dataSC
        });

        data = [];


        //Por imagen o línea de producto
        $.each(proceso.porImg, function (key, value) {
            data.push(value);
        });
        Morris.Donut({
            element: 'imgChart',
            data: data,
            resize: true
//            ,formatter: function (value, data) {  $("#percVal").html(value/proceso.totalLts*100+" %");return (value); }
        });
        data = [];

        //Por presentación
        $.each(proceso.porPtn, function (key, value) {
            data.push(value);
        });
        Morris.Donut({
            element: 'ptnChart',
            data: data,
            resize: true
        });
        data = [];

        //Gráfica por día
//        $.each(proceso.fechasData, function (key, value) {
//            data.push(value);
//        });
//        
//        Morris.Line({
//            element: 'salesChart',
//            data: data,
//            xkey: 'f',
//            ykeys: "v",
//            labels: "Ventas",
//            hideHover: "auto",
//            xLabelMargin: 3
//        });


        //tabla de datos
//        $("#mainTable").dataTable({
//            destroy:true,
//            dom: 'Bfrtip',
//            buttons: [
//                'copyHtml5',
//                'excelHtml5',
//                'pdfHtml5'
//            ],
//            caption: "VENTAS POR FECHA Y ARTÍCULO",
//            data: proceso.data,
//            columns: proceso.columns,
//            "paging": false,
//            "aoColumnDefs": [
//                {"sClass": "numeric", "aTargets": proceso.numericCols} //añadir clase de numeric a las columnas definidas en functions.php
//            ],
//            "bSort": false,
//            fixedHeader: {
//                header: true,
//                footer: false
//            },
//            "footerCallback": function (tfoot, data, start, end, display) {
//                var api = this.api();
//
//                // Remove the formatting to get integer data for summation
//                var intVal = function (i) {
//                    return typeof i === 'string' ?
//                            i.replace(/[\$,]/g, '') * 1 :
//                            typeof i === 'number' ?
//                            i : 0;
//                };
//
//                // Total over all pages
//                total = api
//                        .column(proceso.sumCol)
//                        .data()
//                        .reduce(function (a, b) {
//                            return intVal(a) + intVal(b);
//                        }, 0);
//
//                // Total over this page
//                pageTotal = api
//                        .column(proceso.sumCol, {page: 'current'})
//                        .data()
//                        .reduce(function (a, b) {
//                            return intVal(a) + intVal(b);
//                        }, 0);
//                var numFormat = $.fn.dataTable.render.number('\,', '.', 0, '').display;
//                // Update footer
//                $(api.column(proceso.sumCol).footer()).html(
//                        'Total mostrado:' + numFormat(pageTotal) + 'Lts / Total General:(' + numFormat(total) + ' Lts)'
//                        );
//            }
//
//
//        });
        $('.numeric').text(function () {
            if ($.isNumeric($(this).html())) {
                $(this).parseNumber({format: "#,###", locale: "us"});
                $(this).formatNumber({format: "#,###", locale: "us"});
            }
        });
//        zk_ventasxPlantaFam(i, 1);
    }, "json");
}
function creaChartsVentas() {
    var file = "php/functions.php";
    var param = {
        fase: "r_ventasEvol",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val()
    };
    $.get(file, param, function (proceso) {
        Morris.Donut({
            element: 'plantChart',
            data: [{label: "STG", value: 557000}, {label: "MTY", value: 150000}, {label: "GDL", value: 230000}],
            resize: true
        });
        $('.numeric').text(function () {
            var str = $(this).html() + '';
            x = str.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            $(this).html(x1 + x2);
        });
        $('.porc').text(function () {
            var str = $(this).html() + '';
            x = str.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            if (x1 === "0") {
                x1 = "";
            }
            $(this).html(x1 + Math.round(x2 * 10000) / 100 + "%");
        });
    }, "json");
}
function zk_ventasVSobj(i) {
    var file = "php/functions.php";
    var param = {
        fase: "zk_ventasVSobj",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val(),
        id: i,
        uSe: $("#uSe").val()
    };
    $.get(file, param, function (proceso) {
        $("#fec1Sp").html(proceso.fecSp.fec1);
        $("#fec2Sp").html(proceso.fecSp.fec2);
        $("#tfootth").attr("colspan", eval(proceso.sumCol + 1));
        $("#mainTable").html(proceso.tabla);
        $("tablaDatos").dataTable();
    }, "json")
            .done(function () {
                $('.numeric').text(function () {
                    if ($.isNumeric($(this).html())) {
                        $(this).parseNumber({format: "#,###", locale: "us"});
                        $(this).formatNumber({format: "#,###", locale: "us"});
                    }
                });
                $('.porc').text(function () {
                    var str = $(this).html() + '';
                    x = str.split('.');
                    x1 = x[0];
                    x2 = x.length > 1 ? '.' + x[1] : '';
                    var rgx = /(\d+)(\d{3})/;
                    while (rgx.test(x1)) {
                        x1 = x1.replace(rgx, '$1' + ',' + '$2');
                    }
                    if (x1 === "0") {
                        x1 = "";
                    }
                    $(this).html(x1 + Math.round(x2 * 10000) / 100 + "%");
                });
            });
    zk_ventasVSobj_summary(i);
}
function zk_ventasVSobj_summary(i) {
    var file = "php/functions.php";
    var param = {
        fase: "zk_ventasVSobj_summary",
        fec1: $("#fec1").val(),
        fec2: $("#fec2").val(),
        id: i,
        uSe: $("#uSe").val()
    };
    $.get(file, param, function (proceso) {
        $("#fec1Sp").html(proceso.fecSp.fec1);
        $("#fec2Sp").html(proceso.fecSp.fec2);
        $("#tfootth").attr("colspan", eval(proceso.sumCol + 1));
        $("#mainTable").append(proceso.tabla);
        $("tablaDatos_summary").dataTable();
    }, "json")
            .done(function () {
                $('.numeric').text(function () {
                    if ($.isNumeric($(this).html())) {
                        $(this).parseNumber({format: "#,###", locale: "us"});
                        $(this).formatNumber({format: "#,###", locale: "us"});
                    }
                });
                $('.porc').text(function () {
                    var str = $(this).html() + '';
                    x = str.split('.');
                    x1 = x[0];
                    x2 = x.length > 1 ? '.' + x[1] : '';
                    var rgx = /(\d+)(\d{3})/;
                    while (rgx.test(x1)) {
                        x1 = x1.replace(rgx, '$1' + ',' + '$2');
                    }
                    if (x1 === "0") {
                        x1 = "";
                    }
                    $(this).html(x1 + Math.round(x2 * 10000) / 100 + "%");
                });
            });
}
function stackedCols(data) {
    var dataSC = [];
    $.each(data, function (key, value) {
        var dataPoints = [];
        //Quitar las llaves del objeto datapoints
        $.each(value.dataPoints, function (k, v) {
            dataPoints.push(v);
        });
        //Insertar propiedades en el objeto dataSC
        dataSC.push({
            type: "stackedColumn",
            name: value.name,
            showInLegend: true,
            dataPoints: dataPoints
        });
    });
    $("#plantaBar").CanvasJSChart({
        title: {
            text: "Ventas por planta y presentación"
        },
        axisY: {
            title: "Litros"
        },
        toolTip: {
            content: "{label} <br/>{name} : {y} Litros / 45000"
        },
        data: dataSC
    });
    console.log(dataSC);
    return;

//    $("#plantaBar").CanvasJSChart({
//        title: {
//            text: "Ventas por planta y presentación"
//        },
//        axisY: {
//            title: "Litros"
//        },
//        toolTip: {
//            content: "{label} <br/>{name} : {y} Litros / 45000"
//        },
//        data: [
//            {
//                type: "stackedColumn",
//                name: "Granel",
//                showInLegend: true,
//                dataPoints: [
//                    {label: "STG", y: 9369},
//                    {label: "GDL", y: 7642},
//                    {label: "MTY", y: 7102}
//                ]
//            },
//            {
//                type: "stackedColumn",
//                name: "Tote",
//                showInLegend: true,
//                dataPoints: [
//                    {label: "STG", y: 1318}
//                ]
//            },
//            {
//                type: "stackedColumn",
//                name: "Tambor",
//                showInLegend: true,
//                dataPoints: [
//                    {label: "STG", y: 1109}
//                ]
//            }
//        ]
//    });
}