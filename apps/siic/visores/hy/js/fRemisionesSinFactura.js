var file = "php/fRemisionesSinFactura.php";
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
                        "uniqueName": "Remision"
                    },
                    {
                        "uniqueName": "numRec"
                    },
                    {
                        "uniqueName": "folioSmartRoad"
                    },
                    {
                        "uniqueName": "Almacen"
                    },
                    {
                        "uniqueName": "Fecha.Year",
                        "sort": "desc"
                    },
                    {
                        "uniqueName": "Fecha.Month",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "Fecha.Day"
                    },
                    {
                        "uniqueName": "Cliente_Cve"
                    },
                    {
                        "uniqueName": "Cliente_Nombre"
                    },
                    {
                        "uniqueName": "Prod_Cve"
                    },
                    {
                        "uniqueName": "Prod_Acab"
                    },
                    {
                        "uniqueName": "Prod_Descr"
                    },
                    {
                        "uniqueName": "litros",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "Importe"
                    },
                    {
                        "uniqueName": "Moneda"
                    },
                    {
                        "uniqueName": "STATUS"
                    }
                ],
                "columns": [{
                    "uniqueName": "Measures"
                }],
                "measures": [{
                        "uniqueName": "Remision",
                        "aggregation": "sum",
                        "format": "4rffycb6"
                    },
                    {
                        "uniqueName": "folioSmartRoad",
                        "aggregation": "sum",
                        "format": "4rffycb6"
                    },
                    {
                        "uniqueName": "Litros",
                        "aggregation": "sum",
                        "format": "currency"
                    },
                    {
                        "uniqueName": "Importe",
                        "aggregation": "sum",
                        "active": false,
                        "format": "4rfg02hi"
                    }
                ],
                "flatOrder": [
                    "Remision",
                    "numRec",
                    "folioSmartRoad",
                    "Almacen",
                    "Fecha.Year",
                    "Fecha.Month",
                    "Fecha.Day",
                    "Cliente_Cve",
                    "Cliente_Nombre",
                    "Prod_Cve",
                    "Prod_Acab",
                    "Prod_Descr",
                    "Litros",
                    "Importe",
                    "Moneda",
                    "STATUS"
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
            formats: [{
                    "name": "currency",
                    "thousandsSeparator": ",",
                    "decimalPlaces": 2,
                    "currencySymbol": "",
                    "currencySymbolAlign": "left"
                },
                {
                    "name": "4rffycb6",
                    "thousandsSeparator": "",
                    "decimalSeparator": ".",
                    "currencySymbol": "",
                    "currencySymbolAlign": "left",
                    "nullValue": "",
                    "textAlign": "left",
                    "isPercent": false
                },
                {
                    "name": "4rfg02hi",
                    "thousandsSeparator": ",",
                    "decimalSeparator": ".",
                    "decimalPlaces": 2,
                    "currencySymbol": "$",
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