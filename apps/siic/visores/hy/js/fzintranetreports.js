$(document).ready(function() {
    getData()
})

function getData() {
    var req = getUrlParameters()
    document.title = req["title"]
    $("#report-title").html(req["title"])
    req["t"] = sessionStorage.getItem("token")
    $.get("php/fzintranetbackend.php", req, function(res) {
            if (res.status == 1) {

                showPivot(res.data, getReportConfig(req["fx"]))
            } else {
                alert("error")
            }
        }, "json")
        .done(
            $("#loading").hide()
        )
}

function getUrlParameters() {
    var urlSearch = window.location.search.substring(1).replace("?fec1", "&fec1")
    var search = decodeURIComponent(urlSearch)
    var obj = {}
    var urlItems = search.split('&')
    for (var i = 0; i < urlItems.length; i++) {
        var urlItem = urlItems[i].split('=')
        obj[urlItem[0]] = urlItem[1]
    }
    return obj;
}

function getReportConfig(fx) {
    var config = {}
    if (fx == "fetchPTInventory") {
        config = {
            "slice": {
                "rows": [{
                        "uniqueName": "Marca",
                        "sort": "asc"
                    },
                    {
                        "uniqueName": "Envase",
                        "sort": "asc"
                    },
                    {
                        "uniqueName": "Clave",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "Producto",
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "Cantidad",
                        "filter": {
                            "members": [
                                "Cantidad.0"
                            ],
                            "negation": true
                        },
                        "sort": "unsorted"
                    },
                    {
                        "uniqueName": "Unidad",
                        "sort": "unsorted"
                    }
                ],
                "columns": [{
                    "uniqueName": "Measures"
                }],
                "measures": [{
                    "uniqueName": "Cantidad",
                    "aggregation": "sum",
                    "format": "53o0st6c"
                }],
                "flatOrder": [
                    "Marca",
                    "Envase",
                    "Clave",
                    "Producto",
                    "Cantidad",
                    "Unidad"
                ]
            },
            "options": {
                "grid": {
                    "type": "flat",
                    "showHeaders": false,
                    "showTotals": "off",
                    "showGrandTotals": "off"
                }
            },
            "formats": [{
                "name": "53o0st6c",
                "thousandsSeparator": ",",
                "decimalSeparator": ".",
                "currencySymbol": "",
                "currencySymbolAlign": "left",
                "nullValue": "",
                "textAlign": "right",
                "isPercent": false
            }],
            "tableSizes": {
                "columns": [{
                    "tuple": [],
                    "measure": "Envase",
                    "width": 118
                }]
            }
        }
    }
    return config
}

function showPivot(data, config) {
    $("#data-container").webdatarocks({
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
            "options": config["options"],
            "slice": config["slice"],
            "formats": config["formats"],
            "tableSizes": config["tableSizes"]
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