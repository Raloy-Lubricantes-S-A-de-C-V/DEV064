var file = "php/f_all_reports.php";
var pivot
var lastReportCreated = ""
var shownData = []

$(document).ready(function () {
    fetchReportsList()
    setInitialDates()
    createPivot()
    $("#btn-create-report").click(fetchReportData)
    // streamData()
    // $("#btn-create-report").click(fetchAndConcat)
});
function fetchAndConcat() {
    loadingShow()
    fetchAndConcatReportData(function (data) {
        loadingHide()
        console.log(data)
    })
}
function setInitialDates() {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = ("0" + (today.getMonth() + 1)).slice(-2) // Months start at 0!
    let dd = ("0" + today.getDate()).slice(-2)
    $("#input-date-1").val(yyyy + "-" + mm + "-01")
    $("#input-date-2").val(yyyy + "-" + mm + "-" + dd)
}
function loadingHide() {
    $("#loading").addClass("d-none")
}
function loadingShow() {
    $("#loading").removeClass("d-none")
}
function createPivot() {
    pivot = new WebDataRocks({
        container: "#div-tbl-container",
        toolbar: true,
        beforetoolbarcreated: customizeToolbar,
        height: "97%",
        global: {
            "localization": "webdatarocks_es.json"
        },
        report: {
            dataSource: {
                data: []
            }
        }

    })
}
function customizeToolbar(toolbar) {
    var tabs = toolbar.getTabs(); // get all tabs from the toolbar
    toolbar.getTabs = function () {
        // tabs.push({
        //     id: "fm-tab-newtab",
        //     title: "Update",
        //     handler: function () {
        //         fetchReportData();
        //     },
        //     icon: ''
        // });
        delete tabs[0]; // delete the first tab
        return tabs;
    };
}

function fetchReportsList() {
    var req = {
        fx: "fetchReportsList",
        t: sessionStorage.getItem("token")
    }
    $.get(file, req, function (res) {
        if (res.status == 1) {
            var data = res.data
            $("#select-report").html("<option value=''>Seleccionar</option>")
            $.each(data, function (i, r) {
                $("#select-report").append("<option value='" + r.fx + "'>" + r.label + "</option>")
            })
        }
    }, "json")
        .done(function () {
            loadingHide()
        })
}
function fetchReportData() {
    loadingShow()
    if ($("#select-report").val() == 0) {
        $("div-messages").html("Seleccione un Reporte")
        $("#select-report").focus()
    }

    var req = {
        fx: "fetchReportData",
        subfx: $("#select-report").val(),
        f1: $("#input-date-1").val(),
        f2: $("#input-date-2").val(),
        t: sessionStorage.getItem("token")
    }
    $.get(file, req, function (res) {   
        console.log(res);
        var updatedData = []
        if (res.status == 1) {
            var updatedData = []
            concatSources(res.data, function (data) {
                updatedData = data
                console.log(data)
            })

            if (typeof res["dates"] != "undefined") {
                $("#input-date-1").val(res["dates"]["f1"])
                $("#input-date-2").val(res["dates"]["f2"])
            }
            shownData = updatedData
            if (lastReportCreated == req.subfx) {
                pivot.updateData({ data: updatedData })
            } else {
                var report = {
                    dataSource: { data: updatedData },
                    slice: res["slice"],
                    formats: res["formats"],
                    options: res["options"]
                }

                pivot.setReport(report)
                pivot.refresh()
            }

            lastReportCreated = req.subfx
        }
    }, "json")
        .done(function () {
            loadingHide()
        })
}
function concatSources(data, _callback) {
    var updatedData
    if ($("#appendData").is(":checked")) {
        console.log("Appending new data to current data")
        updatedData = shownData
    } else {
        updatedData = []
    }

    $.each(data, function (i, data) {
        updatedData = updatedData.concat(data)
    })
    _callback(updatedData);
}
function fetchAndConcatReportData(_callback = "") {
    loadingShow()
    if ($("#select-report").val() == 0) {
        $("div-messages").html("Seleccione un Reporte")
        $("#select-report").focus()
        return
    }
    var thisReport = $("#select-report option:selected").text("")
    var reloadAll
    if (thisReport == lastReportCreated) {
        reloadAll = false
    } else {
        reloadAll = true
        lastReportCreated = thisReport
    }

    // var subfxs = $("#select-report").val()
    // var subfxsArr = subfxs.split(',')
    var subfxsArr = ["salesZKOdooJson", "salesZKSCPJson"]
    var data = []
    subfxsArr.forEach(function (subfx, i, arr) {
        var req = {
            fx: "fetchReportData",
            subfx: subfx,
            f1: $("#input-date-1").val(),
            f2: $("#input-date-2").val(),
            t: sessionStorage.getItem("token")
        }
        $.get(file, req, function (res) {
            console.log(res)
            if (res.status == 1) {
                data.concat(res.data)
                if (typeof res["dates"] != "undefined") {
                    $("#input-date-1").val(res["dates"]["f1"])
                    $("#input-date-2").val(res["dates"]["f2"])
                }

                if (!reloadAll) {
                    pivot.updateData({ data: data })
                } else {
                    var report = {
                        dataSource: { data: data },
                        slice: res["slice"],
                        formats: res["formats"],
                        options: res["options"]
                    }

                    pivot.setReport(report)
                    pivot.refresh()
                }
            }
        }, "json")
            .done(function () {
                $("#loading").find("i").html(subfx + " Loaded")
                console.log(subfx, "Loaded")
            })
    })
    if (typeof _callback == "function") {
        _callback(data)
    }
}

function streamData() {
    var lastResponseLength = false;
    var req = {
        fx: "streamData",
        f1: $("#input-date-1").val(),
        f2: $("#input-date-2").val(),
        t: sessionStorage.getItem("token")
    }
    var ajaxRequest = $.ajax({
        type: 'GET',
        url: file,
        data: req,
        dataType: 'json',
        processData: true,
        xhrFields: {
            // Getting on progress streaming response
            onprogress: function (e) {
                var progressResponse;
                var response = e.currentTarget.response;
                if (lastResponseLength === false) {
                    progressResponse = response;
                    lastResponseLength = response.length;
                }
                else {
                    progressResponse = response.substring(lastResponseLength);
                    lastResponseLength = response.length;
                }
                var parsedResponse = JSON.parse(progressResponse);
                $('#progressTest').text(progressResponse);
                $('#fullResponse').text(parsedResponse.message);
                console.log(parsedResponse.message);
                $('.progress-bar').css('width', parsedResponse.progress + '%');
            }
        }
    });

    // On completed
    ajaxRequest.done(function (data) {
        console.log('Complete response = ' + data);
    });

    // On failed
    ajaxRequest.fail(function (error) {
        console.log('Error: ', error);
    });

    console.log('Request Sent');
}