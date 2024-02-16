$(document).ready(function() {
    $('#result').jsonForm({
        "schema": {
            "oldpass": {
                "type": "password",
                "title": "Contraseña Actual"
            },
            "newpass1": {
                "type": "password",
                "title": "¿Cuál es la nueva contraseña?"
            },
            "confirmacion": {
                "type": "password",
                "title": "Otra vez por favor"
            }
        },
        "form": [
            "*",
            {
                "type": "submit",
                "title": "Cambiar"
            }
        ],
        onSubmit: function(errors, values) {
            if (errors) {
                $('#res').html('<p>I beg your pardon?</p>');
            } else {
                if (values.newpass1 !== values.confirmacion) {
                    $("#res").html("Las contraseñas no coinciden");

                    $("#jsonform-1-elt-confirmacion").css("background", "#ffffe3");
                    $("#jsonform-1-elt-newpass1")
                        .css("background", "#ffffe3")
                        .change(function() {
                            $("#jsonform-1-elt-confirmacion").css("background", "none");
                            $("#jsonform-1-elt-confirmacion").css("background", "#ffffe3")
                        }).focus();
                } else {
                    var param = {
                        fase: "changePass",
                        pass: $("#jsonform-1-elt-oldpass").val(),
                        npass: $("#jsonform-1-elt-newpass1").val(),
                    }
                    $.get("/today.zar-kruse.com//php/fPasswordChange.php", param, function(response) {
                        if (response == "1") {
                            $("#res").html("Listo");
                            location.href = "/today_zk/login.html";
                        } else {
                            $("#res").html("Error");
                        }
                    }, "json");
                }
            }
        }
    });
    $("#loading").hide();
});