var php = 'php/1.php';

$(document).ready(function(){
    $('#savebutton').click(savebutton);
    repcomp();
});


function repcomp(argument) {

    var request = {
        f: 'repcomp',
        fec1 : $('.fec1').attr('value'),
        fec2 : $('.fec2').attr('value')
    };
    $('.siic-tabla').hide();
    $('.siic-loading').show();
    $.get(
        php,
        request,
        function (response) {
            console.log("entro a get");
            // var cliente;
            if(response){
                $('#tabla').DataTable({
                    dom: 'Bfrtip',
                    buttons: [ { extend: 'excel', filename: 'Reporte', extension: '.xlsx' }],
                    "columnDefs":[
                    {"width": "20%", "targets": 4},
                    {"type": "num-fmt", "targets": [4,5], "render": function(data, type, full, meta){
                            return '$ '+data;
                        }}], 
                    columns: [
                        {data: 'Competidor', title:'Competidor', className: 'dt-body-center'},
                        {data: 'IRS', title:'IRS', className: 'dt-body-center'},
                        {data: 'Estado', title:'Estado', className: 'dt-body-center'},
                        {data: 'Litros_Totales', title:'Litros_Totales', className: 'dt-body-center'},
                        {data: 'Costo', title:'Costo', className: 'dt-body-center'},
                        {data: 'USDxLt', title:'USDxLt', className: 'dt-body-center'},
                        {data: 'Presentacion', title:'Presentacion', className: 'dt-body-center'},
                        {data: 'Name', title:'Name', className: 'dt-body-center'},
                        {data: 'Convertido', title:'Convertido', className: 'dt-body-center'},
                        {data: 'CITY_STATE', title:'CITY_STATE', className: 'dt-body-center'},
                        {data: 'ORIGIN_DESTINY', title:'ORIGIN_DESTINY', className: 'dt-body-center'},
                        {data: 'Transporte', title:'Transporte', className: 'dt-body-center'},
                        {data: 'CUSTOM_PORT_STATE', title:'CUSTOM_PORT_STATE', className: 'dt-body-center'},
                        {data: 'CUSTOM', title:'CUSTOM', className: 'dt-body-center'},
                        {data: 'COMER_UNIT', title:'COMER_UNIT', className: 'dt-body-center'},
                        {data: 'CDV', title:'CDV', className: 'dt-body-center'},
                        {data: 'Descripcion', title:'Descripcion', className: 'dt-body-center'},
                        {data: 'Cantidad', title: 'Cantidad', className: 'dt-body-center'},
                        {data: 'Cantidad_kg', title: 'Cantidad_kg',  className: 'dt-body-center'},
                        {data: 'Fecha', title: 'Fecha', className: 'dt-body-center'}
                    ],
                    data: response,
                    destroy: true,
                    "paging": false,
                    language:{
                        url: '../datatables/Spanish.json'
                    },
                    select: true,
                    fixedHeader: true

                });
                $('.siic-loading').hide();
                $('.siic-tabla').show();
                var tabla = $('#tabla').DataTable();
                tabla
                    .column('1:visible')
                    .draw();
            }else{
                $('.siic-loading').hide();
                $('#rep').html('¡No se encontraron datos!');
            }
        },
        'json'
        );
}

function savebutton(e){
    e.preventDefault();
    fec1 = $('.fec1').attr('value');
    fec2 = $('.fec2').attr('value');
    var url = 'php/excel1.php?fec1=' + fec1 + '&fec2=' + fec2;
    window.open(url);
}

function prod_Cambia(e) {
    e.preventDefault();
    var request = {
        f: 'prod_Cambia',
        desc: $(this).attr('desc'),
        ant: $(this).attr('ant'),
        valor: $(this).val()
    }

    if (request.ant != request.valor) {
        $.get(php, request, function(response) {
            if (response != "") {
                console.log(e.target);
                $(e.target).addClass(response);
                console.log($(this));
                $(e.target).attr('ant', request.valor);

            }
        });
    }
}
