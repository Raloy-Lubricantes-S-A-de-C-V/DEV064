var file = "php/fldm.php";
$(document).ready(function() {
    getData();
});

function getData() {
    var param = {
        fase: "get_data",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function(proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#reportContainer table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
            return false;
        } else {
            showPivot(proceso.data);
        }
    }, "json").done(
        totalLts
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
            "slice": {
                "rows": [{
                        "uniqueName": "cve"
                    },
                    {
                        "uniqueName": "acabado"
                    },
                    {
                        "uniqueName": "descr"
                    },
                    {
                        "uniqueName": "cant_req"
                    },
                    {
                        "uniqueName": "unidad_req"
                    },
                    {
                        "uniqueName": "cve_req",
                        "format": "4p6o1yqm"
                    },
                    {
                        "uniqueName": "descr_req"
                    },
                    {
                        "uniqueName": "tipo_req"
                    }
                ],
                "columns": [{
                    "uniqueName": "Measures"
                }],
                "measures": [{
                        "uniqueName": "cant_req",
                        "aggregation": "sum",
                        "format": "4p6o3d8a"
                    },
                    {
                        "uniqueName": "cve_req",
                        "aggregation": "sum",
                        "active": false,
                        "format": "text"
                    }
                ],
                "expands": {
                    "expandAll": true
                },
                "flatOrder": [
                    "cve",
                    "acabado",
                    "descr",
                    "cant_req",
                    "unidad_req",
                    "cve_req",
                    "descr_req",
                    "tipo_req"
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
                    "name": "text",
                    "thousandsSeparator": "",
                    "decimalSeparator": ".",
                    "currencySymbol": "",
                    "currencySymbolAlign": "left",
                    "nullValue": "",
                    "textAlign": "left",
                    "isPercent": false
                },
                {
                    "name": "4p6o3d8a",
                    "thousandsSeparator": ",",
                    "decimalSeparator": ".",
                    "decimalPlaces": 5,
                    "currencySymbol": "",
                    "currencySymbolAlign": "left",
                    "nullValue": "",
                    "textAlign": "right",
                    "isPercent": false
                }
            ]
        },
        //        width: 100,
        height: "97%"
            //        customizeCell: Function,
            //        global: ReportObject,
            //        reportcomplete: Function | String
    });
}

function customizeToolbar(toolbar) {
    var tabs = toolbar.getTabs(); // get all tabs from the toolbar
    toolbar.getTabs = function() {
        delete tabs[0]; // delete the first tab
        return tabs;
    };
}

function copytocl() {
    var el = document.getElementById('tblReport');
    var body = document.body,
        range, sel;
    if (document.createRange && window.getSelection) {
        range = document.createRange();
        sel = window.getSelection();
        sel.removeAllRanges();
        try {
            range.selectNodeContents(el);
            sel.addRange(range);
        } catch (e) {
            range.selectNode(el);
            sel.addRange(range);
        }
    } else if (body.createTextRange) {
        range = body.createTextRange();
        range.moveToElementText(el);
        range.select();
        range.execCommand("Copy");
    }
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