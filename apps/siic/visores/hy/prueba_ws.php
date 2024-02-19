<html>



<head>/intranet/libs/

    <!--jQuery-->

    <script type="text/javascript" src="/intranet/libs/jquery-3.2.1.min.js"></script>



    <!--Tablas dinámicas-->

    <link href="../../../../libs/webdatarocks-1.0.2/webdatarocks.min.css" rel="stylesheet" />

    <link href="../../../../libs/webdatarocks-1.0.2/theme/skyblue/webdatarocks.css" rel="stylesheet" />

    <script src="../../../../libs/webdatarocks-1.0.2/webdatarocks.toolbar.min.js"></script>

    <script src="../../../../libs/webdatarocks-1.0.2/webdatarocks.js"></script>



    <script type="text/javascript">

        $(document).ready(function() {

            var param = {

                from: "2021-03-01",

                to: "2021-03-31"

            }

            $.get("php/prueba_ws.php", param, function(response) {

                $("#datos").webdatarocks({

                    toolbar: true,

                    global: {

                        "localization": "webdatarocks_es.json"

                    },

                    report: {

                        dataSource: {

                            data: response

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

            }, "json")

        })

    </script>



</head>



<body>

    <div id="datos"></div>

</body>



</html>