var php = 'php/flogin.php';
var appOrigen = '';

$(document).ready(function() {
    $('#errors').hide();
    sessionStorage.setItem('userSesion', '');
    sessionStorage.setItem('nomUsuario', '');
    sessionStorage.setItem('dateSesion', '');
    sessionStorage.setItem('nomSesion', '');
    appOrigen = GetURLParameter("app");
    rememberMe_get();
    clearSessions();
    $('#submitData').click(function(e) {
        e.preventDefault();
        dologin();
    });
    $("#password").keyup(function(e) {
        if (e.keyCode === 13) {
            $("#submitData").click();
        }
    });
});

function GetURLParameter(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) {
            return sParameterName[1];
        } else {
            return '';
        }
    }
}

function rememberMe_get() {
    $("#shouldIrememberyou").click(function() {
        if ($(this).prop("checked") === false) {
            var request = {
                f: 'rememberMe_kill'
            };
            $.post(php, request, function(response) {
                $("#usuario").val("");
                $("#password").val("");
            });
        }
    });
    var request = {
        f: 'rememberMe_get'
    };
    $.post(php, request, function(response) {
        //        console.log(response);
        if (response.status === 1) {
            $("#usuario").val(response.username);
            $("#password").val(response.password);
            $("#shouldIrememberyou").prop("checked", true);
        } else {
            $("#usuario").val("");
            $("#password").val("");
            $("#shouldIrememberyou").prop("checked", false);
        }
    }, "json");

}

function clearSessions() {
    var request = {
        f: 'clearSession'
    };
    $.post(php, request, function(response) {
        if (response == "Clear") {
            sessionStorage.clear()
        }
    });

}

function dologin() {

    var request = {
        f: 'dologin',
        usuario: $('#usuario').val(),
        password: $('#password').val(),
        shouldIrememberyou: $("#shouldIrememberyou").prop("checked")
    }
    if (request.usuario === '' || request.password === '') {
        $('#errors')
            .html('Debe especificar Usuario y Contraseña')
            .show()
            .fadeOut(5000);
    } else {

        $.post(php, request, function(response) {
            if (response.status == 1) {
                //                sessionStorage.setItem('userSesion', response.sesion.userSesion);
                //                sessionStorage.setItem('nomUsuario', response.sesion.nomUsuario);
                //                sessionStorage.setItem('dateSesion', response.sesion.dateSesion);
                //                sessionStorage.setItem('nomSesion', response.sesion.nomSesion);
                //                console.log(response.sesion);
                sessionStorage.setItem("token", response.token)
                    // if (appOrigen !== '') {
                    //     document.location = 'apps/' + appOrigen
                    // } else {
                document.location = 'index.php';
                // }

            } else {
                $('#errors')
                    .html('Usuario o Password Incorrectos')
                    .show()
                    .fadeOut(5000);
                console.log(response)
            }
        }, 'json');
    }

}