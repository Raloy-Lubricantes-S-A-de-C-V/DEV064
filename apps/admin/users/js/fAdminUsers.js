$(document).ready(() => {

    console.log("hola")

    $("#guardarUsuario").click(guardarUsuario)

    dimeUsuarios()

})



function dimeUsuarios() {



    $("#formUsuarios").hide()



    param = {

        fase: "dimeUsuarios"

    }

    $.get("php/fAdminUsers.php", param, function(res) {

        $("#main").html(res)

    }).done(function() {

        $(".editBtn").off("click").on("click", function() {

            formUsuario($(this).attr("idu"), $(this))

        })

        $("#addNewBtn").off("click").on("click", function() {

            formUsuario(0)

        })

        $("#backBtn").off("click").on("click", function() {

            dimeUsuarios()

        })

        $("#main").show()

    })

}



function limpiaFormUsuario() {



    $("#idUsuario").val("0")

    $("#inputNombre").val("")

    $("#inputUsuario").val("")

    $("#inputArea").val("")

    $("#inputEmail").val("")

    $("#inputPassword").val("")

    $("#inputPassword2").val("")



    $(".checkPlantas").each(function() {

        $(this).prop("checked", false)

    })

    $(".checkPermisos").each(function() {

        $(this).prop("checked", false)

    })

    $(".checkReportes").each(function() {

        $(this).prop("checked", false)

    })



    $("#inputPassword").removeClass("border-danger")

    $("#inputPassword2").removeClass("border-danger")

}



function formUsuario(id_usuario, $obj) {

    $("#main").hide()

    limpiaFormUsuario()

    $("#formUsuarios").show()

    $("#idUsuario").val(id_usuario)



    if (id_usuario == 0)

        return



    var

        plantas = $obj.attr("plantas"),

        permisos = $obj.attr("permisos"),

        reportes = $obj.attr("reportes"),

        nombre = $obj.attr("nombre"),

        usuario = $obj.attr("usuario"),

        area = $obj.attr("area"),

        email = $obj.attr("email")



    $("#inputNombre").val(nombre)

    $("#inputUsuario").val(usuario)

    $("#inputArea").val(area)

    $("#inputEmail").val(email)



    arrPlantas = plantas.split(",")

    $.each(arrPlantas, function(i, v) {

        $("#checkPlantas" + v).prop("checked", true)

    })

    arrPermisos = permisos.split(",")

    $.each(arrPermisos, function(i, v) {

        $("#checkPermisos" + v).prop("checked", true)

    })

    arrReportes = reportes.split(",")

    $.each(arrReportes, function(i, v) {

        $("#checkReportes" + v).prop("checked", true)

    })



}



function guardarUsuario() {



    $("#inputPassword").removeClass("border-danger")

    $("#inputPassword2").removeClass("border-danger")



    var

        arrPlantas = [],

        arrPermisos = [],

        arrReportes = []



    $(".checkPlantas:checked").each(function() {

        arrPlantas.push($(this).attr("id_planta"))

    })

    $(".checkPermisos:checked").each(function() {

        arrPermisos.push($(this).attr("id_permiso"))

    })

    $(".checkReportes:checked").each(function() {

        arrReportes.push($(this).attr("id_reporte"))

    })



    var param = {

        fase: "guardarUsuario",

        "idu": $("#idUsuario").val(),

        "Nombre": $("#inputNombre").val(),

        "Usuario": $("#inputUsuario").val(),

        "Area": $("#inputArea").val(),

        "Email": $("#inputEmail").val(),

        "Password": $("#inputPassword").val(),

        "Password2": $("#inputPassword2").val(),

        "Plantas": arrPlantas,

        "Permisos": arrPermisos,

        "Reportes": arrReportes

    }

    console.log(param.valores);

    if (param.idu == "0") {

        if (param.Nombre == "") {

            $("#inputNombre").focus()

            return

        }

        if (param.Usuario == "") {

            $("#inputUsuario").focus()

            return

        }

        if (param.Area == "") {

            $("#inputArea").focus()

            return

        }

        if (param.Email == "") {

            $("#inputEmail").focus()

            return

        }

        if (param.Password == "") {

            $("#inputPassword").focus()

            return

        }

        if (param.Password2 == "") {

            $("#inputPassword2").focus()

            return

        }

        if (param.Password !== param.Password2) {

            $("#inputPassword").addClass("border-danger")

            $("#inputPassword2").addClass("border-danger")

            $("#inputPassword").focus()

            return

        }

    } else {

        if (param.Password != "" && param.Password !== param.Password2) {

            $("#inputPassword").addClass("border-danger")

            $("#inputPassword2").addClass("border-danger")

            $("#inputPassword").focus()

            return

        }

    }

    $.post("php/fAdminUsers.php", param, function(res) {

        if (res == "1") {

            dimeUsuarios()

        } else {

            alert("Error")

        }

    })

}