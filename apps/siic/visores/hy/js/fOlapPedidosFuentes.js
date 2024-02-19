var file = "php/fOlapPedidosFuentes.php";
$(document).ready(function() {
    getData();
});

function getData() {
    var param = {
        f: "getData"
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
            "slice": {
                "rows": [{
                    "uniqueName": "cliente_nombre"
                }],
                "columns": [{
                        "uniqueName": "commitment_date2.Month"
                    },
                    {
                        "uniqueName": "Measures"
                    }
                ],
                "measures": [{
                    "uniqueName": "litros",
                    "aggregation": "sum",
                    "format": "4tqa6rf1"
                }]
            },
            "formats": [{
                "name": "4tqa6rf1",
                "thousandsSeparator": ",",
                "decimalSeparator": ".",
                "decimalPlaces": 2,
                "currencySymbol": "",
                "currencySymbolAlign": "left",
                "nullValue": "",
                "textAlign": "right",
                "isPercent": false
            }]
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