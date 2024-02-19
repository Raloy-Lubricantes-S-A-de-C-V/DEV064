$(document).ready(function () {
    clearSession();
    $("#user")
            .focus();
    $("#loginBtn").click(function () {
        doLogin();
    });
});

function clearSession() {
    var file = "php/fLogin.php";
    var param = {fase: "clearSession"};
    $.get(file, param, function (proceso) {
        return true;
    }, "json");
}

function doLogin() {
    var file = "php/fLogin.php";
    if ($("#user").val() === "" || $("#password").val() === "") {
        alert("Por favor valide los datos de inicio de sesión");
        return;
    }
    var param = {
        fase: "doLogin",
        usuario: encodeURI($("#user").val()),
        password: encodeURI($("#pssw").val())
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 2) {
            alert("Usuario o Password incorrectos");
        } else if (proceso.status === 1) {
            window.location.href = "index.php";
        } else {
            alert("Por favor revise la conexión a internet y vuelva a intentarlo");
        }
    }, "json");
}