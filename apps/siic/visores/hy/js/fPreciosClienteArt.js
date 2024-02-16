var file = "php/fPreciosClienteArt.php";
$(document).ready(function() {
    getData();
});

function getData() {
    var param = {
        fase: "getData",
        from: $("#from").val(),
        to: $("#to").val(),
        t: sessionStorage.getItem("token")
    };
    console.log(param);
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
            slice: {
                rows: [{
                        "uniqueName": "fuente",
                        caption: "Fuente"
                    },
                    {
                        "uniqueName": "marca",
                        "caption": "Marca"
                    },
                    {
                        "uniqueName": "empaque",
                        "caption": "Empaque"
                    }
                ],
                "columns": [{
                        "uniqueName": "fecha.Month"
                    },
                    {
                        "uniqueName": "Measures"
                    }
                ],
                "measures": [{
                    "uniqueName": "litros",
                    "aggregation": "sum",
                    "caption": "Litros",
                    "format": "currency"
                }],
                "expands": {
                    "expandAll": true
                },
                "sorting": {
                    "column": {
                        "type": "desc",
                        "tuple": [],
                        "measure": "litros"
                    }
                }
            },
            formats: [{
                name: "currency",
                currencySymbol: "",
                currencySymbolAlign: "left",
                thousandsSeparator: ",",
                decimalPlaces: 2
            }]
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