var sseids = "";
$(document).ready(function () {
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
    $("#getfiles")
            .css({
                "display": "inline-block",
                "font-weight": "bold",
                "padding": "2px",
                "box-sizing": "border-box",
                "cursor": "pointer"
            })
            .click(function () {
                console.log("hola");
                dimeCargas();
            });
    $("#filtros button").click(function () {
        $("#txtfilter").val("").trigger("keyup").focus();
    });

    $(".closeModal").click(function () {
        $(".modalContainer").hide();
        $("input").removeClass("highlighted");
        dimeCargas();
    });
    $("#saveRespCarga").click(saveRespCarga);
    createUploader("uplOcs");
    createUploader("uplRems");
    createUploader("uplPesaje");
    $("#fec1").datepicker({dateFormat: "yy-mm-dd"});
    $("#fec2").datepicker({dateFormat: "yy-mm-dd"});
});

function saveRespCarga() {
    var resp = $("#responsableCarga").val();
    if (resp === "") {
        alert("Valor no válido");
        return false;
    }
    var file = "php/functions_archive.php";
    var param = {
        fase: "saveRespCarga",
        responsableCarga: resp,
        folio: $("#foliodocs").html()
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
    var file = "php/functions_archive.php";
    var f1 = $("#fec1").val();
    var f2 = $("#fec2").val();
    var albaran = $("#albaran").val();
    if (f1 === "" || f2 === "") {
        alert("Por Favor seleccione Fechas Válidas");
        $("#fec1").focus();
        return false;
    }
    var param = {
        fase: "dimeCargas",
        "f1": f1,
        "f2": f2
//        "albaran":albaran
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
                $(".folderDoctos").off("click").on("click", function () {
                    dimeCertificados($(this).parent());
                    dimeDoctos($(this).parent().attr("folio"));
                });
                $(".valAMP").off("click").on("click", function () {
                    var folio = $(this).parent().attr("folio");
                    window.open("acuseAMP.php?folio=" + folio);
                });
            });
}

function dimeCertificados(obj) {
    var folio = $(obj).attr("folio");
    $(".modalContainer").hide();
    $("#foliodocs").html(folio);
    $("#divdoctos").show();
    var file = "php/functions_archive.php";
    var param = {
        fase: "dimeCertificados",
        folio: folio
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            $("#plantadocs").html("");
            $("#fechadocs").html("");
            $("#placasdocs").html("");
            $("#responsableCarga").val("");
            $("#certs tbody").html("");
            $("#divdoctos").hide();
            alert(proceso.error);
            return false;
        }
        $("#plantadocs").html(proceso.planta);
        $("#fechadocs").html(proceso.feccar);
        $("#placasdocs").html(proceso.placas);
        $("#responsableCarga").val(proceso.responsableCarga);
        $("#certs tbody").html(proceso.trs);

    }, "json")
            .done(
                    function () {
                        $("#divdoctos").show();
                        $(".validarCert").off("click");
                        activateCert();
                        $(".validarCert").prop("disabled", "disabled").css("color", "#c0cfd4");
                        $("#saveRespCarga").prop("disabled", "disabled").css("color", "#c0cfd4");
                        $("#divdoctos input").prop("disabled", "disabled");
                    });
}

function dimeDoctos(folio) {
    $(".modalContainer").hide();
    $("#foliodocs").html(folio);
    $(".docSection").each(function () {
        getFiles($(this).find(".linksContainer"), $(this).attr("folder"), folio);
    });
//    $("#divDoctos").show();
    var acuseAMP = $("#resumen tr[folio='" + folio + "']").find(".valAMP").find("i").css("color");
    if (acuseAMP === "rgb(76, 166, 76)") {
        $(".deleteFile").off("click").on("click", function () {
            alert("La orden ya fue recibida. No es posible eliminar el documento.");
            $(".dropbox").hide();
        });
    } else {
        $(".deleteFile").off("click").on("click", function () {
            deleteRemision(folio, $(this));
        });
    }
}

function save_all(obj) {
    var file = "php/functions_archive.php";
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
            console.log(proceso);
            return false;
        }
//        $(obj).find(".validarCert").stop().css({"color":"#4ca64c"}).animate({ color: "#2e6172"}, 1500);
        $(obj).find("input").addClass("highlighted");
        $(obj).find(".certCtr").html("<i class='fa fa-file-alt cert' style='color:#4ca64c' folio='" + param.folio + "' iprs='" + param.iprs + "'></i>");
        activateCert();
    }, "json").done(function () {
        dimeremsandpos();
    });
}
function dimeremsandpos() {
    var rems = new Array();
    $(".rems").each(function () {
        if ($(this).val !== "") {
            rems.push($(this).val());
        }
    });
    rems = rems.join(",");
    var param = {
        fase: "dimeremsandpos",
        rems: rems
    };
    $.get("php/functions_archive.php", param, function (respuesta) {
        console.log(respuesta.status);
        if (respuesta.status === 1) {
            $("#detallesuplRems").html(respuesta.compremisiones);
            $("#detallesuplOcs").html(respuesta.ocs);
        } else {
            alert("Los datos ingresados han sido guardados pero las remisiones no fueron localizadas");
        }
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
    var title = "<h3>" + obj.attr("encabezado") + "</h3><div class='detallesbox' id='detalles" + id + "'></div>";
    //crear el objeto uploader
    var folder = obj.attr("folder");
    var dropbox = "<div class='dropbox' id='dropbox" + id + "'><p>" + mensajes.default + "</p></div>";
    var inputfile = "<input type='file' multiple='multiple' class='dropboxInput' target='" + folder + "'  parentid='" + id + "' id='fileinput" + id + "' name='fileinput" + id + "'>";
    var form = "<form id='formuploader" + id + "' name='formuploader" + id + "'>" + dropbox + inputfile + "</form>";
    var idfilescontainer = "linksto" + id;
    var filescontainer = "<div class='linksContainer' id='" + idfilescontainer + "' ></div>";
    var html = title + form + filescontainer;
    //mostrar el objeto en el div
    obj.html(html);

    //métodos del objeto
    //Mostrar el cuadro de diálogo al dar click en el dropbox
    obj.find(".dropbox")
            .off("click")
            .on("click", function () {
                var input = $(this).parent().find(".dropboxInput");
//                console.log(input.attr("id"));
                input.click();
            });
    //Inhabilitar las funciones drag default en el dropbox
    obj
            .on(
                    "dragover",
                    function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).addClass("dragover");
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
                        var objuploader = $(this);
                        uploadFiles(files, objuploader);
                        $(this).removeClass("dragover");
                    });

    //call a function to handle file upload on select file
    $("#fileinput" + id).off("change").on('change', function (event) {
        var files = event.target.files;
        var objuploader = $(this).parent().parent();
        uploadFiles(files, objuploader);
        $(this).val("");
    });
}
function uploadFiles(files, objuploader) {
    var folio = $("#foliodocs").html();
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
$.fn.clearForm = function () {
    return this.each(function () {
        var type = this.type, tag = this.tagName.toLowerCase();
        if (tag == 'form')
            return $(':input', this).clearForm();
        if (type == 'text' || type == 'password' || tag == 'textarea')
            this.value = '';
        else if (type == 'checkbox' || type == 'radio')
            this.checked = false;
        else if (tag == 'select')
            this.selectedIndex = -1;
    });
};
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