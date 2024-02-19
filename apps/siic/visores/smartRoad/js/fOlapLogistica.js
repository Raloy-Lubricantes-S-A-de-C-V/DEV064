var file = "php/fOlapLogistica.php";
$(document).ready(function () {
    getData();
    $("#buttonupdate").click(updateDataJSON);
});
function getData() {
    var param = {
        fase: "getData",
        f1: $("#from").val(),
        f2: $("#to").val()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#reportContainer table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
            return false;
        } else {
            showPivot(proceso.jsondata);
        }
    }, "json")
            .done(function () {
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
            "slice": {
                "rows": [
                    {
                        "uniqueName": "id_entrega",
                        "caption": "Folio",
                    },
                    {
                        "uniqueName": "Estado"
                    },
                    {
                        "uniqueName": "Municipio"
                    },
                    {
                        "uniqueName": "cliente",
                        "caption": "Cliente"
                    }

                ],
                "columns": [
                    {
                        "uniqueName": "Measures"
                    }
                ],
                "measures": [
                    {
                        "uniqueName": "ltsSurtir",
                        "aggregation": "sum",
                        "format": "3hc1hq2k"
                    },
                    {
                        "uniqueName": "CTVar",
                        "formula": "( sum(\"totalvariables\") /sum(\"litros\"))* sum(\"ltsSurtir\") ",
                        "caption": "Costo Variable",
                        "format": "3hc1jgf9"
                    },
                    {
                        "uniqueName": "CTFijos",
                        "formula": "( sum(\"totalfijos\") /sum(\"litros\"))* sum(\"ltsSurtir\") ",
                        "caption": "Costo Fijo",
                        "format": "3hc1jgf9"
                    },
                    {
                        "uniqueName": "CTDestino",
                        "formula": "( sum(\"costototal\") /sum(\"litros\"))* sum(\"ltsSurtir\") ",
                        "caption": "Costo Total",
                        "format": "3hc1jgf9"
                    },
                    {
                        "uniqueName": "CUDestino",
                        "formula": "sum(\"CTDestino\") / sum(\"ltsSurtir\") ",
                        "caption": "MXN/LT",
                        "format": "3hc1itfi"
                    },
                    {
                        "uniqueName": "id_entrega",
                        "aggregation": "distinctcount",
                        "active": false,
                        "format": "3hf6ke1j"
                    },
                    {
                        "uniqueName": "municipio",
                        "aggregation": "distinctcount",
                        "active": false,
                        "availableAggregations": [
                            "count",
                            "distinctcount"
                        ]
                    },
                    {
                        "uniqueName": "FÃ³rmula #1",
                        "formula": "sum(\"costototal\") / sum(\"ltsSurtir\") ",
                        "caption": "Suma de FÃ³rmula #1",
                        "active": false
                    },
                    {
                        "uniqueName": "Costo Unit. Envio",
                        "formula": "(sum(\"costototal\")/ sum(\"litros\") )*sum(\"ltsSurtir\") ",
                        "caption": "Suma de Costo Unit. Envio",
                        "active": false
                    }
                ],
                "sorting": {
                    "column": {
                        "type": "desc",
                        "tuple": [],
                        "measure": "CUDestino"
                    }
                }
            },
            "options": {
                "grid": {
                    "type": "classic", //flat,classic o quitar para default
                    showHeaders: false
                }
            },
            "conditions": [
                {
                    "formula": "#value > 3",
                    "measure": "municipio",
                    "format": {
                        "backgroundColor": "#FFFFFF",
                        "color": "#F44336",
                        "fontFamily": "Arial",
                        "fontSize": "12px"
                    }
                }
            ],
            "formats": [
                {
                    "name": "3hc1hq2k",
                    "thousandsSeparator": ",",
                    "decimalSeparator": ".",
                    "decimalPlaces": 2,
                    "currencySymbol": "",
                    "currencySymbolAlign": "left",
                    "nullValue": "",
                    "textAlign": "right",
                    "isPercent": false
                },
                {
                    "name": "3hc1itfi",
                    "thousandsSeparator": ",",
                    "decimalSeparator": ".",
                    "decimalPlaces": 2,
                    "currencySymbol": "$",
                    "currencySymbolAlign": "left",
                    "nullValue": "",
                    "textAlign": "right",
                    "isPercent": false
                },
                {
                    "name": "3hc1jgf9",
                    "thousandsSeparator": ",",
                    "decimalSeparator": ".",
                    "decimalPlaces": 2,
                    "currencySymbol": "$",
                    "currencySymbolAlign": "left",
                    "nullValue": "",
                    "textAlign": "right",
                    "isPercent": false
                },
                {
                    "name": "3hf6ke1j",
                    "thousandsSeparator": "",
                    "decimalSeparator": ".",
                    "decimalPlaces": 0,
                    "currencySymbol": "",
                    "currencySymbolAlign": "left",
                    "nullValue": "",
                    "textAlign": "left",
                    "isPercent": false
                }
            ]
        },
        height: "97%"
    });

//    webdatarocks.on('reportchange', function () {
//        updateDataJSON();
//    });
//    webdatarocks.on("reportcomplete", function () {
//        $(".wdr-panel-content")
//                .append("<div><select><option>Costo por Cliente</option></select></div>")
//                .find(".wdr-ui-element").hide();
//        console.log("hola");
//    });
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