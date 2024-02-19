var php = 'php/2.php';

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
                    buttons: [ 'pdfHtml5', { extend: 'excel', filename: 'Reporte competidores (especifico) ', extension: '.xlsx' } ],
                    "columnDefs":[
                    {"width": "20%", "targets": 4}, 
                    {   "targets": [6],
                        "render": function(data, type, full, meta){
                            return +data+'%';
                        }
                    }, {"type": "num-fmt", "targets": [4,5], "render": function(data, type, full, meta){
                            return '$ '+data;
                        }}], 

                    columns: [
                        {data: 'Competidor', title:'Competidor', className: 'dt-body-center'},
                        {data: 'Fecha', title:'Fecha', className: 'dt-body-center'},
                        {data: 'Distribuidor', title:'Distribuidor', className: 'dt-body-center'},
                        {data: 'Litros_Totales', title:'Litros_Totales', className: 'dt-body-center'},
                        {data: 'USD', title: 'USD_DAT', className: 'dt-body-center'},
                        {data: 'USDxLitro', title: 'USDxLitro',  className: 'dt-body-center'},
                        {data: 'Porcentaje', title: 'Porcentaje', type:'num-fmt', className: 'dt-body-center'}
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
                    .column('6:visible')
                    .order('desc')
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
    var url = 'php/excel2.php?fec1=' + fec1 + '&fec2=' + fec2;
    console.log(url);
    window.open(url);
}