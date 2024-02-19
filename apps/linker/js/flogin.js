var php = 'php/flogin.php';
$(document).ready(function () {
    $("#acc").click(function () {
        dologin();
    });

});

function dologin() {

    var request = {
        f: 'dologin',
        user: $('#user').val(),
        pass: $('#cveAcc').val()
    };

    if (request.user === '' || request.pass === '') {
        $('#errors')
                .html('Por favor especifique Usuario y Contraseña')
                .show()
                .fadeOut(6000);
        return;
    } else {

        $.get(php, request, function (response) {
            if (response.status == 1) {
                window.location.href = "index.php";
                sessionStorage.setItem('edicion', response.edicion);
                sessionStorage.setItem('visualizacion', response.visualizacion);
            } else {
                $('#errors')
                        .html('Usuario y/o Contraseña Incorrectos')
                        .show()
                        .fadeOut(6000);
            }
        },"json");
    }

}

