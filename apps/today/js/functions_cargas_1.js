var sseids = "";
$(document).ready(function () {
    if (typeof (EventSource) !== "undefined") {
        var source = new EventSource("php/cargas_sse.php");
        $("#buttontry").click(function () {
            $(this).parent().toggleClass("shown")
        });
        source.onmessage = function (event) {
            if ($(".modalContainer").is(':visible')) {
                return false;
            } else {
                var respuesta = JSON.parse(event.data);
                if (respuesta.status === 0) {
                    alert("Error " + respuesta.error);
                    return("false");
                } else {
                    if (respuesta.datos.ids !== sseids) {
                        $("#conteoCargas").html(respuesta.datos.conteocargas);
                        dimeCargas();
                    }
                    sseids = respuesta.datos.ids;
                }
//                $("#resumen tbody").html(respuesta.box);
            }
        };
    } else {
        document.getElementById("result").innerHTML = "Sorry, your browser does not support server-sent events...";
    }
    $("#txtfilter").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#resumen > table > tbody > tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
        if ($(this).val().length > 0) {
            $("#filtros button").show();
        } else {
            $("#filtros button").hide();
        }
    })
            .focus(function () {
                $("#detalle").html("");
            });
    $("#filtros button").click(function () {
        $("#txtfilter").val("").trigger("keyup").focus();
    });
    dimeCargas();
    $(".closeModal").click(function () {
        $(".modalContainer").hide();
        $("input").removeClass("highlighted");
        dimeCargas();
    });
    $("#saveRespCarga").click(saveRespCarga);

    createUploader("uplOcs");
    createUploader("uplRems");
    createUploader("uplPesaje");
});

function saveRespCarga() {
    var resp = $("#responsableCarga").val();
    if (resp === "") {
        alert("Valor no válido");
        return false;
    }
    var file = "php/functions_cargas.php";
    var param = {
        fase: "saveRespCarga",
        responsableCarga: resp,
        folio: $("#foliocerts").html()
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return false;
        }
//        alert("OK");
        $("#responsableCarga").addClass("highlighted");
    }, "json");
}
function dimeCargas() {
    var file = "php/functions_cargas.php";
    var param = {
        fase: "dimeCargas"
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return false;
        }
        $("#resumen tbody").html(proceso.boxes);

    }, "json").done(
            function () {
                $("#identidadVal").css({
                    "text-align": "right",
                    "background": "rgba(182, 240, 169, 0.7)"
                });
                $(".getPapeleta").off("click").on("click", function () {
                    var folio = $(this).attr("folio");
                    if (folio != "") {
                        window.open("../smartRoad/solicitudCarga.php?folio=" + folio);
                    } else {
                        alert("Error, por favor revise que el folio de la papeleta sea correcto");
                    }
                });
                $(".folderCerts").off("click").on("click", function () {
                    dimeCertificados($(this).parent());
                });
                $(".folderRems").off("click").on("click", function () {
                    dimeDoctos($(this).parent().attr("folio"));
                });
                $(".valAMP").off("click").on("click", function () {
                    if ($(this).hasClass("editallowed")) {
                        voboAMP($(this));
                    }
                });
            });
}
function voboAMP(obj) {
    var confirmacion = confirm("Confirmar recepción de producto?\nSólo Material Entregado a Raloy");
    if (confirmacion === false) {
        return false;
    }
    $(obj).find("i").css("color", "#4ca64c");
    var folio = $(obj).parent().attr("folio");
    var param = {
        fase: "voboAMP",
        folio: folio
    };
    var file = "php/functions_cargas.php";
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return false;
        } else {
            window.open("acuseAMP.php?folio=" + folio);
        }

    }, "json");
}
function dimeCertificados(obj) {
    var folio = $(obj).attr("folio");
    $(".modalContainer").hide();
    $("#foliocerts").html(folio);
    $("#divCertificados").show();
    var file = "php/functions_cargas.php";
    var param = {
        fase: "dimeCertificados",
        folio: folio
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return false;
        }
        $("#responsableCarga").val(proceso.responsableCarga);
        $("#certs tbody").html(proceso.trs);
    }, "json")
            .done(
                    function () {
                        $("#divCertificados").show();
                        $(".validarCert").off("click").on("click", function () {
                            save_all($(this).parent().parent());
                        });
                        activateCert();
                        if (!$(obj).hasClass("editallowed")) {
                            $(".validarCert").prop("disabled", "disabled").css("color", "#c0cfd4");
                            $("#saveRespCarga").prop("disabled", "disabled").css("color", "#c0cfd4");
                            $("#divCertificados input").prop("disabled", "disabled");
                        }
                    });
}

function dimeDoctos(folio) {
    $(".modalContainer").hide();
    $("#foliorems").html(folio);
    $(".docSection").each(function () {
        getFiles($(this).find(".linksContainer"), $(this).attr("folder"), folio);
    });
    $("#divDoctos").show();
    var acuseAMP = $("#resumen tr[folio='" + folio + "']").find(".valAMP").find("i").css("color");
    if (acuseAMP === "rgb(76, 166, 76)") {
        $(".deleteFile").off("click").on("click", function () {
            alert("La orden ya fue recibida. No es posible eliminar el documento.");
            $(".dropbox").hide();
        });
    } else {
        $(".deleteFile").off("click").on("click", function () {
            deleteRemision(param.folio, $(this));
        });
    }
}

function save_all(obj) {
    var file = "php/functions_cargas.php";
    var param = {
        fase: "save_all",
        lept: $(obj).find(".lept").val(),
        lpt: $(obj).find(".lpt").val(),
        sellosE: $(obj).find(".sellosE").val(),
        sellosD: $(obj).find(".sellosD").val(),
        rems: $(obj).find(".rems").val(),
        iprs: $(obj).attr("iprs"),
        folio: $(obj).attr("folio")
    };
    if (param.lept === "" || param.lpt === "" || param.sellos === "" || param.rems === "" || param.iprs === "") {
        alert("Datos inválidos");
        return false;
    }
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return false;
        }
//        $(obj).find(".validarCert").stop().css({"color":"#4ca64c"}).animate({ color: "#2e6172"}, 1500);
        $(obj).find("input").addClass("highlighted");
        $(obj).find(".certCtr").html("<i class='fa fa-file-alt cert' style='color:#4ca64c' folio='" + param.folio + "' iprs='" + param.iprs + "'></i>");
        activateCert();
    }, "json");
}
function activateCert() {
    $(".cert").off("click").on("click", function () {
        var folio = $(this).attr("folio");
        var iprs = $(this).attr("iprs");
        window.open("../smartRoad/certificadoCalidad.php?folio=" + folio + "&iprs=" + iprs + "");
    });
}
function createUploader(strdivid) {
    var mensajes = {
        "default": "Seleccione o arrastre un archivo <i class='fa fa-cloud-upload-alt'></i>",
        "errtipo": "El tipo de archivo seleccionado es inválido",
        "errsize": "El archivo excede el tamaño permitido (3MB)",
        "errgral": "Error",
        "loading": "Cargando"
    };
    var obj = $("#" + strdivid);
    var id = obj.attr("id");
    //Título de la caja
    var title = "<h3>" + obj.attr("encabezado") + "</h3>";
    //crear el objeto uploader
    var folder = obj.attr("folder");
    var dropbox = "<div class='dropbox' id='dropbox" + id + "'><p>" + mensajes.default + "</p></div>";
    var inputfile = "<input class='dropboxInput' target='" + folder + "' type='file' multiple='multiple' parentid='" + id + "' id='fileinput" + id + "' name='fileinput" + id + "'>";
    var form = "<form id='formuploader" + id + "' name='formuploader" + id + "'>" + dropbox + inputfile + "</form>";
    var idfilescontainer = "linksto" + id;
    var filescontainer = "<div class='linksContainer' id='" + idfilescontainer + "' ></div>";
    var html = title + form + filescontainer;
    //mostrar el objeto en el div
    obj.html(html);

    //métodos del objeto
    //Mostrar el cuadro de diálogo al dar click en el dropbox
    $("#dropbox" + id)
            .off("click")
            .on("click", function () {
                $("#fileinput" + id).click();
            });
    //Inhabilitar las funciones drag default en el dropbox
    $("#dropbox" + id)
            .on("dragover", function (e) {
                e.preventDefault();
                e.stopPropagation();
            })
            .on(
                    'dragenter',
                    function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
            )
            .on(
                    'drop',
                    function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var files = e.originalEvent.dataTransfer.files;
                        var objuploader = $(this).parent().parent();
                        uploadFiles(files, objuploader);
                    });

    //call a function to handle file upload on select file
    $("#fileinput" + id).off("change").on('change', function (event) {
        var files = event.target.files;
        var objuploader = $(this).parent().parent();
//        uploadFiles(files, objuploader);
        uploadFiles(files, objuploader);
    });
}
function uploadFiles(files, objuploader) {
    var folio = $("#foliorems").html();
    var target = objuploader.find(".dropboxInput").attr("target");
    var parentdropboxtext = objuploader.find(".dropbox p");
    var divlinks = objuploader.find(".linksContainer");

    var mensajes = {
        "default": "Seleccione o arrastre un archivo <i class='fa fa-cloud-upload-alt'></i>",
        "errtipo": "El tipo de archivo seleccionado es inválido",
        "errsize": "El archivo excede el tamaño permitido (3MB)",
        "errgral": "Error",
        "loading": "Cargando"
    };

    var fd = new FormData();
//    fd.append("fase", "upload");
    fd.append("folio", folio);
    fd.append("target", target);

    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        if (file.size > 3145728) {
            //Revisar que el archivo no exceda 3MB
            parentdropboxtext.html(mensajes.errsize);
            return false;
        }
        //Guardar el archivo en los parámetros post a enviar
        var filename = file.name.toLowerCase();
        fd.append('file[]', file, filename);
    }
    ;
    $.ajax({
        url: "php/upload.php?fase=upload",
        type: 'post',
        data: fd,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
            console.log(response);
            if (response.status === "ok") {
                parentdropboxtext.html(mensajes.default);
            } else if (response.status === "type_err") {
                parentdropboxtext.html(mensajes.errtipo);
            } else {
                parentdropboxtext.html(mensajes.errgral);
            }
            getFiles(divlinks, target, folio);
        }
    });
}
function getFiles(targetdiv, dir, folio) {
    var param = {
        "fase": "getFiles",
        "dir": dir,
        "folio": folio
    };
    $.get("php/upload.php", param, function (proceso) {
        targetdiv.html(proceso.links);
    }, "json").done(function () {
        $(".deleteFile")
                .off("click")
                .on("click", function () {
                    removeFile(param.folio, $(this));
                });
    });
}
function removeFile(folio, obj) {
    var file = "php/upload.php";
    var param = {
        fase: "removeFile",
        file: obj.attr("filename"),
        folio: folio,
        folder: obj.parent().parent().parent().attr("folder")
    };
    $.get(file, param, function (proceso) {
        if (proceso === "") {
            if (param.folder === "remisiones") {
                var objfolder = $("#resumen tr[folio='" + folio + "']").find(".folderRems");
                var newnumremsare = eval($(objfolder).attr("numremsare")) - 1;
                $(objfolder).attr("numremsare", newnumremsare);
            }
            $(obj).parent().remove();
        } else {
            alert("Error");
            console.log(proceso)
        }
    });
}