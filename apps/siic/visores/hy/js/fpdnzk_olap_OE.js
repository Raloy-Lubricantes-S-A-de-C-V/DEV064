var file = "../hy2/php/functions.php";
$(document).ready(function () {
    getData();
});
function getData() {
    var param = {
        f: "trazabilidad_consolida",
        fx:"pt",
        fec1: $("#from").val(),
        fec2: $("#to").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#reportContainer table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
            return false;
        } else {
            showPivot(proceso.data);
        }
    }, "json").done(function () {

        $("#loading").hide();
    }
    );
}
function showPivot(data) {
    $("#reportContainer").webdatarocks({
//        container: String,
        beforetoolbarcreated: customizeToolbar,
        toolbar: true,
        global: {
            "localization": "webdatarocks_es.json"
        },
        report: {
            dataSource: {
                data: data
            },
            "options": {
                "grid": {
//                    "type": "flat", //classic o quitar para default
                    showHeaders: false
                }
            },
            slice: {
                rows: [
                    {
                        "uniqueName": "Planta",
                        caption: "Planta"
                    },
                    {
                        "uniqueName": "Producto_empaque",
                        caption: "PRESENTACIÓN"
                    },
                    {
                        "uniqueName": "LotePTP",
                        caption: "LotePTP"
                    }
                ],
                "columns": [
                    {
                        "uniqueName": "FechaHr.Month"
                    },
                    {
                        "uniqueName": "Measures"
                    }
                ],
                "measures": [
                    {
                        "uniqueName": "lts_thisOE",
                        "aggregation": "sum",
                        "caption": "Litros OE",
                        "format": "currency"
                    }
                ],
                "sorting": {
                    "column": {
                        "type": "desc",
                        "tuple": [],
                        "measure": "lts_thisOE"
                    }
                }
            },
            formats: [{
                    name: "currency",
                    currencySymbol: "",
                    currencySymbolAlign: "left",
                    thousandsSeparator: ",",
                    decimalPlaces: 2
                },
                {
                    name: "numerico",
                    currencySymbol: "",
                    currencySymbolAlign: "left",
                    thousandsSeparator: ",",
                    decimalPlaces: 2
                }]
        },
        height: "97%"
    });
}
function customizeToolbar(toolbar) {
    var tabs = toolbar.getTabs(); // get all tabs from the toolbar
    toolbar.getTabs = function () {
        tabs.push({
            id: "fm-tab-newtab",
            title: "Update",
            handler: function () {
                updateDataJSON();
            },
            icon: ''
        });
        delete tabs[0]; // delete the first tab
        return tabs;
    };
}
function updateDataJSON() {
    var param = {
        fase: "getData",
        from: $("#from").val(),
        to: $("#to").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#reportContainer table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
            return false;
        } else {
            webdatarocks.updateData({
                data: proceso.jsondata
            });
            alert("Datos actualizados");
        }
    }, "json")
            .done(function () {
                $("#loading").hide();
            });
}

function totalLts() {
    var sum = 0;
    $('.lts').each(function () {
        sum += parseFloat($(this).html());  // Or this.innerHTML, this.innerText
    });
    sum = Math.round(sum);
    $("#totalLtsVal").html(sum);
    $("#copytocl").off("click").on("click", copytocl);
    $("#loading").hide();
}