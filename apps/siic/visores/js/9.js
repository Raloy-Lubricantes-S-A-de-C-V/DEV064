var php = 'php/9.php';

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
                    buttons: [ { extend: 'excel', filename: 'Reporte de competencia completo', extension: '.xlsx' }],
                    "columnDefs":[ {"type": "num-fmt", "targets": [30,31,32], "render": function(data, type, full, meta){
                            return '$ '+data;
                        }}],
                    columns: [
                        {data: 'NAME_MX', title:'NAME_MX', className: 'dt-body-center'},
                        {data: 'DESCRIPTION_8DIGITS', title:'DESCRIPTION_8DIGITS', className: 'dt-body-center'},
                        {data: 'USD', title:'USD', className: 'dt-body-center'},
                        {data: 'LTS', title:'LTS', className: 'dt-body-center'},
                        {data: 'USD_LT', title:'USD_LT', className: 'dt-body-center'},
                        {data: 'NAME', title:'NAME', className: 'dt-body-center'},
                        {data: 'WEIGHT', title:'WEIGHT', className: 'dt-body-center'},
                        {data: 'DAY', title: 'DAY', className: 'dt-body-center'},
                        {data: 'MONTH', title: 'MONTH',  className: 'dt-body-center'},
                        {data: 'YEAR', title:'YEAR', className: 'dt-body-center'},
                        {data: 'CLASIFICACION', "type": "html", title: 'CLASIFICACION', className: 'dt-body-center'},
                        {data: 'PRESENTACION', title: 'PRESENTACION', className: 'dt-body-center'},
                        {data: 'QUANTITY', title:'QUANTITY', className: 'dt-body-center'},
                        {data: 'UNIT', title:'UNIT', className: 'dt-body-center'},
                        {data: 'COMER_QTY', title:'COMER_QTY', className: 'dt-body-center'},
                        {data: 'COMER_UNIT', title:'COMER_UNIT', className: 'dt-body-center'},
                        {data: 'COMER_VALUE', title:'COMER_VALUE', className: 'dt-body-center'},
                        {data: 'IMPORT_EXPORT', title:'IMPORT_EXPORT', className: 'dt-body-center'},
                        {data: 'HSCODE_6DIGITS', title:'HSCODE_6DIGITS', className: 'dt-body-center'},
                        {data: 'DESCRIPTION_6DIGITS', title:'DESCRIPTION_6DIGITS', className: 'dt-body-center'},
                        {data: 'HSCODE_8DIGITS', title:'HSCODE_8DIGITS', className: 'dt-body-center'},
                        {data: 'PESOS', title:'PESOS', className: 'dt-body-center'},
                        {data: 'IRS_MX', title:'IRS_MX', className: 'dt-body-center'},
                        {data: 'ADDRESS_MX', title:'ADDRESS_MX', className: 'dt-body-center'},
                        {data: 'ZIP_MX', title:'ZIP_MX', className: 'dt-body-center'},
                        {data: 'CITY_MX', title:'CITY_MX', className: 'dt-body-center'},
                        {data: 'STATE_MX', title:'STATE_MX', className: 'dt-body-center'},
                        {data: 'CUSTOM_KEY', title:'CUSTOM_KEY', className: 'dt-body-center'},
                        {data: 'SECTION_KEY', title:'SECTION_KEY', className: 'dt-body-center'},
                        {data: 'CUSTOM', title:'CUSTOM', className: 'dt-body-center'},
                        {data: 'CUSTOM_PORT_STATE', title:'CUSTOM_PORT_STATE', className: 'dt-body-center'},
                        {data: 'CUSTOM_BROKER', title:'CUSTOM_BROKER', className: 'dt-body-center'},
                        {data: 'DOCUMENT', title:'DOCUMENT', className: 'dt-body-center'},
                        {data: 'TRANSPORT', title:'TRANSPORT', className: 'dt-body-center'},
                        {data: 'ORIGIN_DESTINY', title:'ORIGIN_DESTINY', className: 'dt-body-center'},
                        {data: 'BUYER_SELLER', title:'BUYER_SELLER', className: 'dt-body-center'},
                        {data: 'EXCHANGE_RATE', title:'EXCHANGE_RATE', className: 'dt-body-center'},
                        {data: 'IRS', title:'IRS', className: 'dt-body-center'},
                        {data: 'ADDRESS', title:'ADDRESS', className: 'dt-body-center'},
                        {data: 'INTERIOR', title:'INTERIOR', className: 'dt-body-center'},
                        {data: 'EXTERIOR', title: 'EXTERIOR', className: 'dt-body-center'},
                        {data: 'ZIP', title: 'ZIP',  className: 'dt-body-center'},
                        {data: 'CITY_STATE', title: 'CITY_STATE', className: 'dt-body-center'}
                        
                    ],
                    data: response,
                    destroy: true,
                    "paging": false,
                    "scrollY": '60vh',
                    "scrollX": true,
                    language:{
                        url: '../datatables/Spanish.json'
                    },
                    select: true,
                    fixedHeader: true
                }).on('draw.dt', function(){ 
                    $('.combito').change(prod_Cambia);
                    $('.combito_pres').change(pres_Cambia);

                });
                
                $('.siic-loading').hide();
                $('.siic-tabla').show();
                /*var tabla = $('#tabla').DataTable();
                tabla
                    .column('6:visible')
                    .order('desc')
                    .draw();*/
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

    console.log(request);
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

function pres_Cambia(e) {
    e.preventDefault();
    var request = {
        f: 'pres_Cambia',
        desc: $(this).attr('desc'),
        ant: $(this).attr('ant'),
        valor: $(this).val()
    }

    console.log(request);
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
