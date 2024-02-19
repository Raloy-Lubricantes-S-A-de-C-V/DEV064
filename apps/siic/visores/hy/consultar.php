<!DOCTYPE html>
<html>

<head>
    <title><?php echo $title; ?></title>
    <!--jQuery-->
    <script type="text/javascript" src="../../../../libs/jquery-3.2.1.min.js"></script>
    <!--Tablas dinámicas-->
    <link href="../../../../libs/webdatarocks-1.0.2/webdatarocks.min.css" rel="stylesheet" />
    <link href="../../../../libs/webdatarocks-1.0.2/theme/skyblue/webdatarocks.css" rel="stylesheet" />
    <script src="../../../../libs/webdatarocks-1.0.2/webdatarocks.toolbar.min.js"></script>
    <script src="../../../../libs/webdatarocks-1.0.2/webdatarocks.js"></script>

</head>

<body>

    <input type="hidden" id="tkn" value="<?php $_GET["token"]; ?>" />
    <textarea style="width:80%;" id="sql"></textarea>
    <button id="exec_sql">Mostrar</button>
    <br />
    Resultado:<br />
    <div id="reportContainer"></div>
    <div id="result"></div>
    <script>
        $(document).ready(function() {
            $("#exec_sql").click(function() {
                $.get("php/fConsultar.php", {
                    "sql": $("#sql").val(),
                    "t": sessionStorage.getItem("token")
                }, function(res) {
                    console.log(res)
                    showPivot(res.jsondata)
                    $("#result").html(res)
                }, "json")
            })
        })


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
    </script>

</body>