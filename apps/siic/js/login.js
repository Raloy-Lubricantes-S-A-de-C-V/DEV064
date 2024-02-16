var php = '../php/funciones.php';

$(document).ready(function () {

	$('#frmlogin').submit(function(e){
		e.preventDefault();
		dologin();
	});

	// $('#reset').click(function (e) {
	// 	e.preventDefault();
	// 	$('#frmlogin')[0].reset();
	// });
});

function dologin () {
	var request = {
		f: 'dologin',
		usuario: $('#usuario').val(),
		password: $('#password').val()
	}

	if (request.usuario == '' || request.password == '') {
		console.log("vacio");
		$('.siic-error')
			.html('<span class="mdl-badge" data-badge="!">Debe especificar Usuario y Contraseña</span>')
			.show()
			.fadeOut(5000);
	}else{

		$.get(php, request, function (response) {
			console.log("sesion");

			if (response.nomSesion != '') {
				sessionStorage.setItem('userSesion',response.userSesion);
				sessionStorage.setItem('nomUsuario',response.nomUsuario);
				sessionStorage.setItem('dateSesion',response.dateSesion);
				sessionStorage.setItem('nomSesion',response.nomSesion);
				document.location = '../index.html';
			}else{
				$('.siic-error')
					.html('<span class="mdl-badge" data-badge="!">Usuario y/o Contraseña Incorrectos</span>')
					.show()
					.fadeOut(5000);
			}
		},'json');
	}

}