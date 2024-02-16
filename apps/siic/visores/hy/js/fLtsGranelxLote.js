var file = "php/fLtsGranelxLote.php";
$(document).ready(function() {
    getData();
});

function getData() {
    var param = {
        fase: "getData",
        f1: $("#from").val(),
        f2: $("#to").val(),
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#reportContainer table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
            return false;
        } else {
            showPivot(proceso.jsondata);
        }
    }, "json").done(function() {

        $("#loading").hide();
    });
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
            "slice": {
                "rows": [{
                        "uniqueName": "loteETP",
                        "sort": "desc"
                    },
                    {
                        "uniqueName": "producto",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "planta",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "fechaLote.Year",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "fechaLote.Month",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "fechaLote.Day",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "tanque",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "nombreTanque",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "capacidad_l",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "lts",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "porcentaje",
                        "sort": "unsorted"
                    }
                ],
                "columns": [{
                    "uniqueName": "Measures"
                }],
                "measures": [{
                        "uniqueName": "porcentaje",
                        "aggregation": "sum",
                        "format": "4rrbba2j"
                    },
                    {
                        "uniqueName": "lts",
                        "aggregation": "sum",
                        "format": "4rrbc7fn"
                    },
                    {
                        "uniqueName": "tanque",
                        "aggregation": "sum"
                    },
                    {
                        "uniqueName": "capacidad_l",
                        "aggregation": "sum"
                    }
                ],
                "flatOrder": [
                    "loteETP",
                    "producto",
                    "planta",
                    "fechaLote.Year",
                    "fechaLote.Month",
                    "fechaLote.Day",
                    "tanque",
                    "nombreTanque",
                    "capacidad_l",
                    "lts",
                    "porcentaje"
                ]
            },
            "options": {
                "grid": {
                    "type": "flat",
                    "showHeaders": false,
                    "showTotals": "off",
                    "showGrandTotals": "columns"
                }
            },
            "conditions": [{
                "formula": "#value > 1",
                "measure": "porcentaje",
                "format": {
                    "backgroundColor": "#FFFFFF",
                    "color": "#F44336",
                    "fontFamily": "Arial",
                    "fontSize": "12px"
                }
            }],
            "formats": [{
                    "name": "4rrbba2j",
                    "thousandsSeparator": ",",
                    "decimalSeparator": ".",
                    "decimalPlaces": 2,
                    "currencySymbol": "",
                    "currencySymbolAlign": "left",
                    "nullValue": "",
                    "textAlign": "right",
                    "isPercent": true
                },
                {
                    "name": "4rrbc7fn",
                    "thousandsSeparator": ",",
                    "decimalSeparator": ".",
                    "decimalPlaces": 2,
                    "currencySymbol": "",
                    "currencySymbolAlign": "left",
                    "nullValue": "",
                    "textAlign": "right",
                    "isPercent": false
                }
            ]
        },
        height: "97%"
    });
}

function customizeToolbar(toolbar) {
    var tabs = toolbar.getTabs(); // get all tabs from the toolbar
    toolbar.getTabs = function() {
        tabs.push({
            id: "fm-tab-newtab",
            title: "Update",
            handler: function() {
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
        to: $("#to").val(),
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
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
        .done(function() {
            $("#loading").hide();
        });
}

function totalLts() {
    var sum = 0;
    $('.lts').each(function() {
        sum += parseFloat($(this).html()); // Or this.innerHTML, this.innerText
    });
    sum = Math.round(sum);
    $("#totalLtsVal").html(sum);
    $("#copytocl").off("click").on("click", copytocl);
    $("#loading").hide();
}