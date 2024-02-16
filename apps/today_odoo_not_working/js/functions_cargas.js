var sseids = "";
var timerKA = 300000; //5 minutos
console.log()

$(document).ready(function () {
    // $("#addReg").click(addReg)
    
    getData()
    inputValidationEvent()
    createUploader("divFileSolCarga")
    $("#addReg").click(createNewRecord)
    
});
function getData(){
    var req = {
        "fase": "getData",
        "t": sessionStorage.getItem("token")
    }
    console.log("req", req)
    var url = "php/functions_cargas.php"
    $.get(url, req, function (res) {
        console.log(res)
        $("#loading").hide()
    }, "json")
}
function createNewRecord() {

    var req = {
        "fase": "createNewRecord",
        "folio": $("#inputFolioSolCar").val(),
        "planta": $("#inputPlanta").val(),
        "fechaCarga": $("#inputFecha").val(),
        "doctosSolCargaZK": "res.fileName",
        "t": sessionStorage.getItem("token")
    }
    console.log("req", req)
    var url = "php/functions_cargas.php"
    $.get(url, req, function (res) {
        getData()
    }, "json")

    // uploadFiles($('#fileinputdivFileSolCarga')[0].files, $("#divFileSolCarga"), function (res) {
    //     console.log("rrr", res)
    //     var req = {
    //         "folio": $("#inputFolioSolCar").val(),
    //         "planta": $("#inputPlanta").val(),
    //         "fechaCarga": $("#inputFecha").val(),
    //         "doctosSolCargaZK": res.fileName
    //     }
    //     var url = "php/functions_cargas.php"
    //     $.post(url, req, function (res) {
    //         console.log(res)
    //     }, "json")
    // })

}
function inputValidationEvent() {
    $('#inputRemisionZK').on('change', function () {
        var file = this.files[0];

        if (file.type != "application/pdf") {
            alert('Sólo se permiten Archivos PDF');
        }
        $(this).attr("file-name", file.name);

        // Also see .name, .type
    });
}
function addReg() {
    var req = {
        "fx": "addReq",
        "fecha": $("#inputFecha").val(),
        "planta": $("#inputPlanta").val(),
        "folio": $("#inputFolio").val(),
        "sozk": $("#inputSOZK").val(),
        "fileName": $("#inputRemisionZK").attr("file-name")
    }
    console.log(req)
}
function cambiaStatus() {
    if ($("#saveAllBtn").prop("disabled") == true) {
        var file = "php/functions_cargas.php";
        var param = {
            fase: "changeStatus",
            folio: $("#foliodocs").html(),
            t: sessionStorage.getItem("token")
        }
        $.get(file, param, function (proceso) {
            if (proceso.status == 1) {
                console.log("Status Cargado")
                $(".closeModal").click()
                $("#resumen").find("tr[folio='" + param.folio + "']").remove()
            } else {
                alert("No es posible cerrar la orden de carga")
                console.log(proceso)
            }
        }, "json")
    } else {
        alert("Guarde los cambios e intente nuevamente")
    }


}

function kSA() {
    var file = "php/functions_cargas.php";
    var param = {
        fase: "kSA",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function (proceso) {
        if (proceso.status === 1) {
            console.log(proceso.rand);
            setTimeout(function () {
                kSA();
            }, timerKA);
        } else {
            location.reload();
        }
    }, "json");
}

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
        folio: $("#foliodocs").html(),
        t: sessionStorage.getItem("token")
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
        fase: "dimeCargas",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return false;
        }
        $("#resumen tbody").html(proceso.boxes);

    }, "json").done(
        function () {
            $("#loading").hide();

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
                if ($(this).hasClass("editallowed")) {
                    $("#foliodocs").html($(this).parent().attr("folio"))
                    $("#modal-recepcion-raloy").modal("show")
                }
            })
        });
}

function datosRecepcionRaloy(_callback) {
    $("#detalles-recepcion tbody").html("")
    $("#btn-save-recepcion").attr("disabled", "disabled")
    $("#numReciboRaloy").val("")
    var param = {
        fase: "datosRecepcionRaloy",
        folio: $("#foliodocs").html(),
        t: sessionStorage.getItem("token")
    };
    var file = "php/functions_cargas.php";
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return false;
        } else {
            $("#recepcion-raloy-folio").html(param.folio)
            $("#detalles-recepcion tbody").html(proceso.data)
        }

    }, "json").done(_callback);
}

function voboAMP() {
    var folio = $("#foliodocs").html()
    if (folio == "" || typeof (folio) == "undefined") {
        alert("Error")
        return
    }

    if ($("#input-num-recepcion").val() == "") {
        alert("Ingrese el número de recepción")
        return
    }

    var confirmacion = confirm("Desea firmar el acuse de entrega del Folio: " + folio + "?\nSólo Material Entregado a Raloy");
    if (confirmacion === false) {
        return false;
    }
    var param = {
        fase: "voboAMP",
        folio: folio,
        numRec: $("#input-num-recepcion").val(),
        t: sessionStorage.getItem("token")
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
    $("#loading").show();
    var folio = $(obj).attr("folio");
    $(".modalContainer").hide();
    $("#foliodocs").html(folio);
    $("#divdoctos").show();
    var file = "php/functions_cargas.php";
    var param = {
        fase: "dimeCertificados",
        folio: folio,
        t: sessionStorage.getItem("token")
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

        $(".lept").off("change").on("change", function () {
            var $obj = $(this)
            let lept = $(this).val()
            let lts_a_surtir = $(this).attr("lts_a_surtir")
            $.get(file, { "fase": "validate_tank_capacity", "lept": lept, "lts": lts_a_surtir }, function (validation) {
                console.log(validation)
                if (validation.alert != "OK") {
                    $obj.css("color", "red")
                    alert(validation.alert + " Capacidad:" + validation.capacidad + " Litros Surtidos:" + validation.lts_surtidos);
                } else {
                    $obj.css("color", "green")
                }
            }, "json")
        })

    }, "json")
        .done(
            function () {
                $("#loading").hide();
                $("#divdoctos").show();
                //                        $(".validarCert").off("click").on("click", function () {
                //                            save_all($(this).parent().parent());
                //                        });
                //                        save_all($(this).parent().parent());
                resetSaveFunctionality();
                activateCert();
                if (!$(obj).hasClass("editallowed")) {
                    //                            $(".validarCert").prop("disabled", "disabled").css("color", "#c0cfd4");
                    //                            $("#saveRespCarga").prop("disabled", "disabled").css("color", "#c0cfd4");
                    $("#saveAllBtn").prop("disabled", "disabled").css("color", "#c0cfd4");
                    $("#divdoctos input").prop("disabled", "disabled");
                }

            });
}

function resetSaveFunctionality() {
    $("#saveAllBtn").off("click").on("click", function () {
        $("#saveAllBtn").html("Guardando...").attr("disabled", true);
        save_all_processAll(function () {
            resetSaveFunctionality();
            $("#saveAllBtn").html("Guardar Todo").attr("disabled", false).removeAttr("disabled");
        });
    });
}

function save_all_processAll(callback) {
    saveRespCarga();
    $(".editableRow").each(function (i, v) {
        save_all($(this));
    });
    callback();
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

    var file = "php/functions_cargas.php";
    var param = {
        fase: "save_all",
        lept: $(obj).find(".lept").val(),
        lpt: $(obj).find(".lpt").val(),
        sellosE: $(obj).find(".sellosE").val(),
        sellosD: $(obj).find(".sellosD").val(),
        rems: $(obj).find(".rems").val(),
        iprs: $(obj).attr("iprs"),
        folio: $(obj).attr("folio"),
        t: sessionStorage.getItem("token")
    };

    if (param.lept === "" || param.lpt === "" || param.sellos === "" || param.rems === "" || param.iprs === "") {
        alert("Datos inválidos");
        $(obj).find("input").addClass("highlightedRed");
        return false;
    }

    $("#saveAllBtn").html("Guardando...").attr("disabled", true);
    $.get(file, param, function (proceso) {
        if (proceso.status.datos === 1) {
            $(obj).find("input").removeClass("highlightedRed");
            $(obj).find("input").addClass("highlighted");
            $(obj).find(".certCtr").html("<i class='fa fa-file-alt cert' style='color:#4ca64c' folio='" + param.folio + "' iprs='" + param.iprs + "'></i>");
        } else {
            $(obj).find("input").removeClass("highlighted");
            $(obj).find("input").addClass("highlightedRed");
            alert("Error al guardar los datos");
        }
        if (proceso.status.certificado === 1) {
            $(obj).find("input").removeClass("highlightedRed");
            $(obj).find("input").addClass("highlighted");
            $(obj).find(".certCtr").html("<i class='fa fa-file-alt cert' style='color:#4ca64c' folio='" + param.folio + "' iprs='" + param.iprs + "'></i>");
        } else {
            $(obj).find(".lept").removeClass("highlighted");
            $(obj).find(".lept").addClass("highlightedRed");
            alert("Error al emitir el certificado de calidad. Verifique el número de lote");
        }

        //        $(obj).find(".validarCert").stop().css({"color":"#4ca64c"}).animate({ color: "#2e6172"}, 1500);

        activateCert();
        $("#saveAllBtn").html("Guardar Todo").attr("disabled", false).removeAttr("disabled");
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
        rems: rems,
        t: sessionStorage.getItem("token")
    };
    $.get("php/functions_cargas.php", param, function (respuesta) {
        console.log(respuesta.status);
        if (respuesta.status === 1) {
            $("#detallesuplRems").html(respuesta.compremisiones);
            $("#detallesuplOcs").html(respuesta.ocs);
            $("#acuses").html(respuesta.acuses);
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
        "default": "<i class='fa fa-cloud-upload-alt'></i>",
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
    var dropbox = "<div class='dropbox d-flex justify-content-around' id='dropbox" + id + "'><p>" + mensajes.default + "</p></div>";
    var inputfile = "<input type='file' multiple='multiple' class='dropboxInput' target='" + folder + "'  parentid='" + id + "' id='fileinput" + id + "' name='fileinput" + id + "'>";
    var form = "<form id='formuploader" + id + "' name='formuploader" + id + "'>" + dropbox + inputfile + "</form>";
    var idfilescontainer = "linksto" + id;
    var filescontainer = "<div class='linksContainer' id='" + idfilescontainer + "' ></div>";
    // var html = title + form + filescontainer;
    var html = form + filescontainer;
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
    // $("#fileinput" + id).off("change").on('change', function (event) {
    //     var files = event.target.files;
    //     var objuploader = $(this).parent().parent();
    //     uploadFiles(files, objuploader);
    //     $(this).val("");
    // });
}

function uploadFiles(files, objuploader, _calback = "") {
    var folio = $("#inputFolioSolCar").val();
    console.log(folio)
    var target = objuploader.find(".dropboxInput").attr("target");
    var parentdropboxtext = objuploader.find(".dropbox p");
    var divlinks = objuploader.find(".linksContainer");

    var mensajes = {
        "default": "<i class='fa fa-cloud-upload-alt'></i>",
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
    };

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
            if (typeof _callback == "function") {
                _callback({ "fileName": response.fileName })
            }
            // getFiles(divlinks, target, folio);

        }
    });
}
$.fn.clearForm = function () {
    return this.each(function () {
        var type = this.type,
            tag = this.tagName.toLowerCase();
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
        "folio": folio,
        t: sessionStorage.getItem("token")
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
        folder: obj.parent().parent().parent().attr("folder"),
        t: sessionStorage.getItem("token")
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