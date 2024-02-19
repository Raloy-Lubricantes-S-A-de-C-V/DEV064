var file = "php/fventaszk_olap.php";
var fileOdoo = "php/folap_odoozk.php";
$(document).ready(function () {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    fx=urlParams.get('fx')
    console.log(fx)
    if(fx==null){
        $("#btn-update-data").click(getDataOdoo("update"));
        getDataOdoo();
    }else{
        selectFX(fx)
    }
});
function selectFX(fx){
    fxs={
        "getPriceLists":getPriceLists
    }
    fxs[fx]()
}
function getPriceLists(){
    var param = {
        fx: "getPriceLists",
        f1: $("#from").val(),
        f2: $("#to").val(),
        t: sessionStorage.getItem("token")
    };
    $.get(fileOdoo, param, function (proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#reportContainer table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
            // return false;
            _callback(0);
        } else {
            showPivot(proceso.jsondata);
        }
    }, "json").done(function () {

        $("#loading").hide();
    });
}
function getDataOdoo(_callback) {
    var param = {
        fx: "getAllData",
        f1: $("#from").val(),
        f2: $("#to").val(),
        t: sessionStorage.getItem("token")
    };
    $.get(fileOdoo, param, function (proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#reportContainer table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
            // return false;
            _callback(0);
        } else {
            showPivot(proceso.jsondata);
        }
    }, "json").done(function () {

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
            slice: {
                rows: [{
                    "uniqueName": "planta",
                    caption: "Área"
                },
                {
                    "uniqueName": "Presentacion",
                    caption: "Empaque"
                },
                {
                    "uniqueName": "Imagen",
                    caption: "Marca"
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
                },
                {
                    "uniqueName": "Pzas",
                    "aggregation": "sum",
                    "caption": "Piezas",
                    "format": "numerico"
                }
                ],
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
            },
            {
                name: "numerico",
                currencySymbol: "",
                currencySymbolAlign: "left",
                thousandsSeparator: ",",
                decimalPlaces: 2
            }
            ]
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
        fase: "getAllData",
        from: $("#from").val(),
        to: $("#to").val(),
        t: sessionStorage.getItem("token")
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
        sum += parseFloat($(this).html()); // Or this.innerHTML, this.innerText
    });
    sum = Math.round(sum);
    $("#totalLtsVal").html(sum);
    $("#copytocl").off("click").on("click", copytocl);
    $("#loading").hide();
}