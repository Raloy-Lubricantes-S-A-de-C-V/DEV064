var file = "php/fVentasOlapSmartRoad.php";
$(document).ready(function() {
    getData();
});

function getData() {
    var param = {
        fase: "ventasGranelSmartRoad",
        f1: $("#from").val(),
        f2: $("#to").val()
    };
    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#reportContainer table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
            return false;
        } else {
            showPivot(proceso.data);
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
                        "uniqueName": "planta_carga"
                    },
                    {
                        "uniqueName": "logistica_ruta"
                    },
                    {
                        "uniqueName": "week_of_month_carga"
                    },
                    {
                        "uniqueName": "ubic_edoCor"
                    }
                ],
                "columns": [{
                        "uniqueName": "fecha_carga.Month"
                    },
                    {
                        "uniqueName": "Measures"
                    }
                ],
                "measures": [{
                    "uniqueName": "litros",
                    "aggregation": "sum",
                    "format": "numerico"
                }],
                "expands": {
                    "expandAll": true,
                    "rows": [{
                            "tuple": [
                                "planta_carga"
                            ]
                        },
                        {
                            "tuple": [
                                "Country.Canada"
                            ]
                        }
                    ]
                }
            },
            "options": {
                "grid": {
                    "showHeaders": false
                }
            },
            "formats": [{
                    "name": "currency",
                    "thousandsSeparator": ",",
                    "decimalPlaces": 2,
                    "currencySymbol": "$",
                    "currencySymbolAlign": "left"
                },
                {
                    "name": "numerico",
                    "thousandsSeparator": ",",
                    "decimalPlaces": 2,
                    "currencySymbol": "",
                    "currencySymbolAlign": "left"
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
        to: $("#to").val()
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