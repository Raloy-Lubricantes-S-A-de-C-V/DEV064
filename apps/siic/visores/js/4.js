var php = 'php/4.php';
var request = {
        f: 'repcomp',
        fec1 : $('.fec1').attr('value'),
        fec2 : $('.fec2').attr('value')
    };
    var request_bot ;

$(document).ready(function(){

    google.charts.setOnLoadCallback(graficos);
    google.charts.load('43', {'packages':['corechart']});

    $('#dialogo').dialog({
        autoOpen: false,
        width: 850,
        height: 350,
        buttons: [
            {
                text: "Grafica",
                click: function() {
                    grafica_pop();
                }
            },
            {
                
                text: "Cerrar",
                click: function() {
                    $( this ).dialog( "close" );
                }

            }

        ]
    });

    $('#dialogo2').dialog({
        autoOpen: false,
        width: 450,
        height: 450,
        buttons: [            
            {
                
                text: "Cerrar",
                click: function() {
                    $( this ).dialog( "close" );
                }

            }

        ]
    });

    // graficos();
    repcomp();
});


function repcomp(argument) {

    $('.siic-tabla').hide();
    $('.siic-loading').show();
    $.get(
        php,
        request,
        function (response) {
            if(response){
                   
                $('#tabla').DataTable({
                    dom: 'Bfrtip',
                    buttons: [ { extend: 'pdfHtml5', exportOptions: {columns: [0,1,2,3,4]} }, 
                    { extend: 'excelHtml5', filename: 'Estudio de mercado', extension: '.xlsx' , exportOptions: {columns: [0,1,2,3,4]}} ],

                        "columnDefs":[{
                        "targets": [4],
                        "render": function(data, type, full, meta){
                            return +data+'%';
                        }
                    }, {"type": "num-fmt", "targets": [2,3], "render": function(data, type, full, meta){
                            return '$ '+data;
                        }}],

                    columns: [
                        {data: 'Competidor', title:'Competidor', className: 'dt-body-center'},
                        {data: 'Litros_Totales', title:'Litros_Totales',  className: 'dt-body-center'},
                        {data: 'USD', title: 'USD_DAT',  className: 'dt-body-center'},
                        {data: 'USDxLitro', title: 'USDxLitro',  className: 'dt-body-center'},
                        {data: 'Porcentaje', title: 'Porcentaje', className: 'dt-body-center'},
                        {data: 'Detalles', title: 'Detalles', "type": "html", className: 'dt-body-center'}

                    ],
                    data: response,
                    destroy: true,
                    language:{
                        url: '../datatables/Spanish.json'
                    },
                    // select: true
                }).on('draw.dt', function(){
                    $('.boton_desc').unbind().click(function(e){
                        e.preventDefault();
                        boton(e);
                    });   
                });
                
                $('.siic-loading').hide();
                $('.siic-tabla').show();
                var tabla = $('#tabla').DataTable();
                tabla
                    .column('4:visible')
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

function boton(e){
    e.preventDefault();
    
    var request_bot = {
        f: 'boton',
        comp: $(e.currentTarget).attr('comp'),
        fec1 : $('.fec1').attr('value'),
        fec2 : $('.fec2').attr('value')
    };

    
        $.get(php, request_bot, function(response) {
            if(response){
                console.log(response);
                var html = '<table style="width:100%" class="mdl-data-table mdl-js-data-table mdl-shadow--2dp siic-tabla"> <tr> <th>Presentacion</th> <th>Litros_Totales</th> <th>USD_DAT</th> <th>USDxLitro</th> <th> Porcentaje</th></tr>';
                $.each(response, function (i,x) {
                        html +=
                        '<tr>' +
                            '<td>' + x.Presentacion + '</td>' +
                            '<td>' + x.Litros_Totales + '</td>' +
                            '<td>$ ' + x.USD + '</td>' +
                            '<td>$ ' + x.USDxLitro + '</td>' +
                            '<td>' + x.Porcentaje+ ' %</td>' +                        
                        '</tr>';
                        
                    });
                html +='</table>';
                html += '<input type="hidden" id="competidor" value="' + request_bot.comp + '">';


                $('.ui-dialog-title').html("Detalle del Competidor: " + request_bot.comp); // Modificar el Titulo del Dialogo
                $('#dialogo').find('p').html(html); // Insertar contenido del Dialogo
                $('#dialogo').dialog('open'); // Mostrar el Dialogo
                         

                }else{
                    $('#rep').html('¡No se encontraron datos!');
                }


        },
        'json'
    );
}


function grafica_pop(){
    var pie;
    var pieval=[];
    var pielabel=[];
    var request_bot = {
        f: 'boton',
        comp: $("#competidor").val(),
        fec1 : $('.fec1').attr('value'),
        fec2 : $('.fec2').attr('value')
    };

    console.log(request_bot);

    
    $.get(
        php,
        request_bot,
        function (response) {
            if(response){
                console.log(response);
                $('.ui-dialog-title').html(request_bot.comp); // Modificar el Titulo del Dialogo
                //$('#dialogo2').find('p').html(html); // Insertar contenido del Dialogo
                $('#dialogo2').dialog('open'); // Mostrar el Dialogo
                $.each(response,function(i, item){
                    pieval.push(item.Porcentaje);
                    pielabel.push(item.Presentacion);

                });

                pie=[{values: pieval,
                    labels: pielabel,
                    type: 'pie'
                }];

                var layout1 = {
                height: 300,
                width: 400
                };
                // console.log(pie);

                Plotly.newPlot('grafica_pop', pie, layout1);
                    
            }
        },
    'json'
    );
}


function graficos() {
    var valores={};
    var pie;
    var pieval=[];
    var pielabel=[];
    var data = [];

    request = {
        f: 'graficos',
        fec1 : $('.fec1').attr('value'),
        fec2 : $('.fec2').attr('value')
    };

    $('#graficos').empty();
    $.get(
        php,
        request,
        function (response) {
            if(response){
                $.each(response,function(i, item){
                    valores={y:[parseFloat(item.USDxLitro)], x: [parseFloat(item.Litros_Totales)],
                        name: item.Competidor, mode: 'markers', type:'scatter',  text: item.Competidor, marker: { size: 12}};
                        data.push(valores);

                    pieval.push(item.Porcentaje);
                    pielabel.push(item.Competidor);

                });



                    pie=[{values: pieval,
                        labels: pielabel,
                        type: 'pie'}];

                    var layout1 = {
                        //margin: {r:550},                
                        legend: {borderwidth: 3, font:{size: 10}},
                        height: 400,
                        width: 1050
                    };
                        // console.log(pie);

                    Plotly.newPlot('grafica_pastel', pie, layout1 );

                    var layout = {
                        //margin: {r:500},
                        yaxis:{title: 'USDxLitro', titlefont:{ color: '#f40202'}},
                        xaxis:{title: 'Litros Totales', titlefont:{ color: '#f40202'}},
                        legend: {borderwidth: 3, font:{size: 10}},
                        height: 400,
                        width: 1050
                    };

                    Plotly.newPlot('graficos', data, layout );
                }
        },
                    'json'
    );
}
