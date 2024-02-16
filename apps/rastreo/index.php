<!DOCTYPE html>

<html>

<head>
    <script type="text/javascript" src="/today_zk/libs/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="js/fRastreo.js"></script>
    <!-- bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

</head>

<body>
    <!-- Modal (Filters) -->
    <div class="modal fade" id="filtersModal" tabindex="-1" role="dialog" aria-labelledby="filtersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filtersModalLabel">Filtros</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row pb-2 px-0 m-0 w-100 d-flex">
                            <div class="col-2">
                                <label class="px-2" for="numPedido">Cliente</label>
                            </div>
                            <div class="col-3">
                                <input id="cveCliente" type="text" class="form-control" placeholder="Clave" />
                            </div>
                            <div class="col-7">
                                <input id="nomCliente" type="text" class="form-control" placeholder="Nombre" />
                            </div>
                        </div>
                        <div class="row py-2 px-0 m-0 w-100 d-flex">
                            <div class="col-2">
                                <label class="px-2" for="numPedido">Determinante</label>
                            </div>
                            <div class="col-3">
                                <input id="cveCliente" type="text" class="form-control" placeholder="Clave" />
                            </div>
                            <div class="col-7">
                                <input id="nomCliente" type="text" class="form-control" placeholder="Nombre" />
                            </div>
                        </div>
                        <div class="row py-2 px-0 m-0 w-100 d-flex">
                            <div class="col-2">
                                <label class="px-2" for="numPedido">Dirección</label>
                            </div>
                            <div class="col-4">
                                <input id="cveCliente" type="text" class="form-control" placeholder="Calle, Número , CP" />
                            </div>
                            <div class="col-3">
                                <input id="nomCliente" type="text" class="form-control" placeholder="Ciudad/Municipio" />
                            </div>
                            <div class="col-3">
                                <input id="nomCliente" type="text" class="form-control" placeholder="Estado" />
                            </div>
                        </div>
                        <hr />
                        <div class="row py-2 px-0 m-0 w-100 d-flex">
                            <div class="col-2">
                                <label class="px-2" for="numPedido">Producto</label>
                            </div>
                            <div class="col-2">
                                <input id="cveCliente" type="text" class="form-control" placeholder="Clave" />
                            </div>
                            <div class="col-3">
                                <input id="nomCliente" type="text" class="form-control" placeholder="Nombre" />
                            </div>
                            <div class="col-2">
                                <label class="px-2" for="numPedido">Fuente</label>
                            </div>
                            <div class="col-3">
                                <select class="form-control">
                                    <option value=""></option>
                                    <option value="zar">Zar Kruse</option>
                                    <option value="ral">Raloy</option>
                                </select>
                            </div>
                        </div>
                        <hr/>
                        <div class="row py-2 px-0 m-0 w-100 d-flex">
                            <div class="col-2">
                                <label class="px-2" for="numPedido">ETA</label>
                            </div>
                            <div class="col-5">
                                <input id="eta1" type="date" class="form-control" />
                            </div>
                            <div class="col-5">
                                <input id="eta2" type="date" class="form-control" />
                            </div>
                        </div>
                        <div class="row py-2 px-0 m-0 w-100 d-flex">
                            <div class="col-2">
                                <label class="px-2" for="numPedido">Pedido</label>
                            </div>
                            <div class="col-5">
                                <input id="fecPedido1" type="date" class="form-control" />
                            </div>
                            <div class="col-5">
                                <input id="fecPedido2" type="date" class="form-control" />
                            </div>
                        </div>
                        <div class="row py-2 px-0 m-0 w-100 d-flex">
                            <div class="col-2">
                                <label class="px-2" for="numPedido">Remisión</label>
                            </div>
                            <div class="col-5">
                                <input id=" fecRemi1" type="date" class="form-control" />
                            </div>
                            <div class="col-5">
                                <input id="fecRemi2" type="date" class="form-control" />
                            </div>
                        </div>


                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary">Buscar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Header -->
    <header class="d-flex align-items-center p-2 m-0 w-100 bg-light" style='color:#301968;'>
        <h1 class="px-2">Rastreo de Pedidos Granel</h1>
        <img class="ml-auto px-2" src="/today_zk/img/Logo Skyblue horizontal.png" alt="SkyBlue" style='height:60px;' /></a>
    </header>

    <div class="container-fluid">
        <div class="row d-flex align-items-center justify-content-end w-100 m-0 p-2">


            <div class="col-3 p-2 d-flex align-items-center">
                <label class="px-2" for="numPedido">Pedido</label>
                <input id="numPedido" type="text" class="form-control" />
            </div>

            <div class="col-3 p-2 d-flex align-items-center">
                <label class="px-2" for="folioInput">Folio</label>
                <input id="folioInput" type="text" class="form-control" />
            </div>

            <div class="col-2 p-2 d-flex align-items-center text-center">
                <button id="searchBtn" type="button" class="mr-2 btn btn-primary">
                    Buscar
                </button>
            </div>

            <div class="col-2 offset-2 p-2 d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-light" data-toggle="modal" data-target="#filtersModal">
                    Más Filtros
                </button>
            </div>

            

        </div>
        <div class="row" id="showPedido" class="m-0 p-0 w-100">
            <div class="col p-2">
                <table id="resultTable" class="table" style='font-size:12pt;'>
                    <thead>
                        <tr>
                            <th class="text-center">Núm. Pedido</th>
                            <th class="text-center">Fecha Pedido</th>
                            <th class="text-center">ID Rastreo</th>
                            <th class="text-center">ETA</th>
                            <th class="text-center">Cve Cliente</th>
                            <th class="text-center">Cliente</th>
                            <th class="text-center">Cve. Prod.</th>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Destino</th>
                            <th class="text-center">Litros</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>