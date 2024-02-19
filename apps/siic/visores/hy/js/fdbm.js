var file = "php/fventaszk_olap.php";
var fileOdoo = "php/folap_odoozk.php";
$(document).ready(function () {
    // const queryString = window.location.search;
    // const urlParams = new URLSearchParams(queryString);
    // fx=urlParams.get('fx')
    // console.log(fx)
    // if(fx==null){
    //     getDataOdoo();
    // }else{
    //     selectFX(fx)
    // }
    $("#sqlSubmit").click(execSQL)
});
function execSQL() {
    var param = {
        fx: "execSQL",
        sql: $("#sql").val(),
        t: sessionStorage.getItem("token")
    };

    $.get(fileOdoo, param, function (proceso) {
        if (proceso.status !== 1) {
            $("#resultMessage").html(proceso.error)
        } else {
            $("#resultMessage").html(proceso.numRows)
            showPivot(proceso.jsonData);
        }
    }, "json").done(function () {
        $("#loading").hide();
    });
}
function selectFX(fx) {
    fxs = {
        "getPriceLists": getPriceLists
    }
    fxs[fx]()
}
function getPriceLists() {
    var param = {
        fx: "getPriceLists",
        f1: $("#from").val(),
        f2: $("#to").val(),
        t: sessionStorage.getItem("token")
    };
    $.get(fileOdoo, param, function (proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#resultTable table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
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
            $("#resultTable table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
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
    $("#resultTable").webdatarocks({
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
                    type: "flat",
                    //                    "type": "flat", //classic o quitar para default
                    showHeaders: false
                }
            },
            slice: {

            },
            formats: [
                
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
        fase: "getData",
        from: $("#from").val(),
        to: $("#to").val(),
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#resultTable table tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
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