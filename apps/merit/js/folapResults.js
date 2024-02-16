var file = "php/folapResults.php";
$(document).ready(function () {
    getData();
    $("#changeDates").click(getData);
    $("#copyBtn").click(function () {
        selectElementContents(document.getElementById('tblReport'));
    });
    $("#exportBtn").click(function(){
        exportTableToExcel('tblReport',"SkyBlue_lab_"+$("#from").val()+"_"+$("#to").val());
    });
});
function getData() {
    $("#tblReport tbody").html("");
    $("#waitasec").show();
    var param = {
        fase: "getData",
        f1: $("#from").val(),
        f2: $("#to").val()
    };
    $.get(file, param, function (proceso) {
//        console.log(proceso);
        if (proceso.status !== 1) {
            alert("Sin datos");
            $("#tblReport tbody").html("<tr><td colspan='11'>Sin datos</td></tr>");
            return false;
        } else {
//            showPivot(proceso.jsondata);
            showtable(proceso.jsondata, function () {
//                $("#tblReport").dataTable({
//                    dom: 'Bfrtip',
//                    "buttons": [
//                        'copy', 'excel', 'pdf'
//                    ],
//                    "paging": false,
//                    "destroy": true
//                });
                $("#loading").hide();
                $("#spanfec1").html($("#from").val());
                $("#spanfec2").html($("#to").val());
                $("#searchInput").off("keyup").on("keyup", function () {
                    var value = $(this).val().toLowerCase();
                    $("#tblReport > tbody > tr").filter(function () {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });
            });
        }
    }, "json").done(function(){$("#waitasec").hide();});
}
function selectElementContents(el) {
    var body = document.body, range, sel;
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
        document.execCommand("copy");

    } else if (body.createTextRange) {
        range = body.createTextRange();
        range.moveToElementText(el);
        range.select();
        range.execCommand("Copy");
    }
    alert("Datos copiados");
}
function showtable(data, callback) {
    let tbody = "";

    $(data).each(function (i, v) {
        let minval = (v.valor_min === null) ? "" : v.valor_min;
        let color = (v.okErr === "OK") ? "green" : "red";
        tbody += "<tr>";
        tbody += "<td>" + v.lote + "</td>";
        tbody += "<td>" + v.fechaProd + "</td>";
        tbody += "<td>" + v.planta + "</td>";
        tbody += "<td>" + v.tq + "</td>";
        tbody += "<td>" + v.fechaIngreso + "</td>";
        tbody += "<td>" + v.fechaResultado + "</td>";
        tbody += "<td>" + v.lapso + "</td>";
        tbody += "<td>" + v.param + "</td>";
        tbody += "<td>" + minval + "</td>";
        tbody += "<td>" + v.valor_max + "</td>";
        tbody += "<td style='color:" + color + ";font-weight:bold;'>" + v.Resultado + "</td>";
        tbody += "<td style='color:" + color + ";'>" + v.okErr + "</td>";
        tbody += "</tr>";
    });
    $("#tblReport tbody").html(tbody);

    callback();
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
                rows: [
                    {
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
                "columns": [
                    {
                        "uniqueName": "fecha.Month"
                    },
                    {
                        "uniqueName": "Measures"
                    }
                ],
                "measures": [
                    {
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
                }]
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

function totalLts() {
    var sum = 0;
    $('.lts').each(function () {
        sum += parseFloat($(this).html());  // Or this.innerHTML, this.innerText
    });
    sum = Math.round(sum);
    $("#totalLtsVal").html(sum);
    $("#copytocl").off("click").on("click", copytocl);
    $("#loading").hide();
}
function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    // Specify file name
    filename = filename?filename+'.xls':'LabSkyBlue.xls';
    
    // Create download link element
    downloadLink = document.createElement("a");
    
    document.body.appendChild(downloadLink);
    
    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
    
        // Setting the file name
        downloadLink.download = filename;
        
        //triggering the function
        downloadLink.click();
    }
}