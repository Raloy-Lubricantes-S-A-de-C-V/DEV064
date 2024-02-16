var php = 'php/funciones.php';

$(document).ready(function() {

    reportesUsuario();

    // $('.siic-navigation').find('a').click(function () {
    // 	$(document).find('.mdl-layout__drawer').removeClass('is-visible');
    // });

    // $('#frmbuscarcliente').submit(function(e) {
    //    e.preventDefault();
    //    buscarCliente();
    // });

    $('#mostrar').click(reporteMostrar);

    var hoy = new Date();

    $('#fec1').val(hoy.getFullYear() + '-' + pad2((hoy.getMonth() + 1)) + '-' + pad2(hoy.getDate()));
    $('#fec2').val(hoy.getFullYear() + '-' + pad2((hoy.getMonth() + 1)) + '-' + pad2(hoy.getDate()));

    $('.siic-nomUsuario').html(sessionStorage.getItem('nomUsuario'));
    $('.siic-nomUsuario-large').html(sessionStorage.getItem('nomUsuario'));

});

// function buscarCliente(){
// 	var request = {
// 		f: 'buscarCliente',
// 		q: $('#buscarnombre').val()
// 	};

// 	if (request.q != '') {
// 		$.get(php, request, function(response){
// 			if (response) {
// 				$('#clientes').empty();
// 				var html = '';
// 				$.each(response, function(idx, item){
// 					html =
// 					'<div class="mdl-shadow--2dp mdl-cell mdl-cell--top siic-buscar--cliente">' +
// 						'<i class="mdl-color-text--blue-900 material-icons">person</i>' +
// 						'<div class="siic-buscar--cliente-item">' +
// 							'<div class="siic-buscar--cliente-nom">' +  item.NomCliente + '</div>' +
// 							'<div class="siic-buscar--cliente-clave">' +  item.CveCliente + '</div>' +
// 						'</div>' +
// 					'</div>';
// 					$('#clientes').append(html);
// 				});
// 				$('#clientes').find('.siic-buscar--cliente').click(function(e){
// 					var clave = $(this).find('.siic-buscar--cliente-clave').text();
// 					$('#cliente')
// 						.val(clave)
// 						.parent().addClass('is-dirty');
// 				});
// 			};

// 		},'json');

// 	};

// }



function pad2(number) {
    return (number < 10 ? '0' : '') + number;
}

function reporteMostrar() {

    var url = $('.siic-ul-reportes').find('.siic-selected').attr('url');
    var cual = $('.siic-ul-reportes').find('.siic-selected').html();
    var id = $('.siic-ul-reportes').find('.siic-selected').attr('id');
    var titulo = $('.siic-ul-reportes').find('.siic-selected').attr('titulo');

    if (typeof url != 'undefined') {

        var request = {
            f: 'registraHit',
            cliente: $('#cliente').val(),
            fec1: $('#fec1').val(),
            fec2: $('#fec2').val(),
            sesion: sessionStorage.getItem('nomSesion'),
            usuario: sessionStorage.getItem('userSesion'),
            idReporte: id,
            hit: url + '?fec1=' + $('#fec1').val() +
                '&fec2=' + $('#fec2').val() +
                '&cliente=' + $('#cliente').val() +
                '&t=' + sessionStorage.getItem("token"),
            reporte: cual
        };

        url +=
            '?fec1=' + request.fec1 +
            '&fec2=' + request.fec2 +
            '&cliente=' + request.cliente +
            '&id=' + request.idReporte +
            '&titulo=' + titulo +
            '&t=' + sessionStorage.getItem("token");
        var params = { userSesion: sessionStorage.getItem('userSesion') };
        openWindow(url, params);
        /*console.log(url);*/
        $.get(php, request);
    }
}

function openWindow(url, params) {
    var f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
        action: url
    }).appendTo(document.body);

    for (var i in params) {
        if (params.hasOwnProperty(i)) {
            $('<input type="hidden" />').attr({
                name: i,
                value: params[i]
            }).appendTo(f);
        }
    }

    f.submit();
    f.remove();
}

function reportesUsuario() {
    var request = {
        f: 'reportesUsuario',
        t: sessionStorage.getItem("token")
    }

    $.get(php, request, function(response) {
        $.each(response, function(index, value) {
            $('.siic-ul-reportes').append(
                '<li class="siic-ul-reportes__item mdl-navigation__link" ' +
                'id="' + value.IDReporte + '" ' +
                'url="' + value.url + '" ' +
                'descrip="' + value.Descrip + '" ' +
                'titulo="' + value.Titulo + '">' +
                value.NomReporte +
                '</li>'
            );
        });

        $('.siic-ul-reportes__item').click(function(e) {
            $('.siic-ul-reportes__item').removeClass('mdl-color--grey-400 mdl-color-text--blue-grey-90 siic-selected');
            $(this).addClass('mdl-color--grey-400 mdl-color-text--blue-grey-90 siic-selected');
            $('.siic-selected__reporte-titulo').html($(this).html());
            ($(this).attr('descrip') !== "null") ? $('.siic-selected__reporte-descrip').html($(this).attr('descrip')): $('.siic-selected__reporte-descrip').empty();
            $('.siic-card').show();
        });

    }, 'json');
}