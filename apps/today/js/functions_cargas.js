$(document).ready(function () {
    dimeCargas(function () {
        $("#tabla-cargas").DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ],
            "paging": false,
            order: [[2, 'desc'], [0, 'asc'], [1, 'asc']],
            deferRender: true,
            scrollX: "95%",
            scrollY: "70vh",
            scrollCollapse: true,
            scroller: true,
            "language": {
                "decimal": ".",
                "emptyTable": "No hay datos disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Show _MENU_ entries",
                "loadingRecords": "Loading...",
                "processing": "",
                "search": "Buscar:",
                "zeroRecords": "No matching records found",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
            "drawCallback": function (settings) {
                $(".dt-button").addClass("btn")
                $(".dataTables_filter").addClass("mb-2")
            }
        })
    })
    files_functionality()
    on_show_modal()
    filter_functionality()
})
function files_functionality() {
    $(".dropbox")

        .on("click", function () {
            var targetId = $(this).attr("input-file-target")
            $("#" + targetId).click()
        })

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
                var objuploader = $(this).parent();
                uploadFiles(files, objuploader);
                $(this).removeClass("dragover");
            });

    //call a function to handle file upload on select file
    $(".input-file").off("change").on('change', function (event) {
        var files = event.target.files;
        var uploader = $(this).parent()
        uploadFiles(files, uploader);
        $(this).val("");
    });
}
function on_show_modal() {
    $('#modal-doctos').on('show.bs.modal', function (e) {
        var trigger = e.relatedTarget
        var ruta = $(trigger).attr("ruta")
        var pedido = $(trigger).attr("pedido")
        var remision = $(trigger).attr("remision")
        var litros = $(trigger).attr("litros")
        $("#modal-span-route").html(ruta)
        $("#modal-span-pedido").html(pedido)
        $("#modal-span-albaran").html(remision)
        $("#modal-span-litros").html(litros)
        getFilesRemision(ruta, remision)
        getTicketsBascula(ruta)
    })
}
function filter_functionality() {
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
    }).focus(function () {
        $("#detalle").html("");
    });

    $("#filtros button").click(function () {
        $("#txtfilter").val("").trigger("keyup").focus();
    });

}
function save_aptin_raloy() {
    if ($(this).val() == "") {
        $(this).removeClass("bg-success text-white bg-danger")
        return
    }
    var file = "php/functions_cargas.php";
    var param = {
        fase: "save_aptin_raloy",
        aptin: $(this).val(),
        route: $(this).attr("route"),
        move_id: $(this).attr("move-id"),
        aptout_zk: $(this).attr("apt-out-zk"),
        invoice_zk: $(this).attr("invoice-zk"),
        liters: $(this).attr("liters"),
        t: sessionStorage.getItem("token")
    }
    $(".input-move-" + param.move_id).removeClass("bg-success text-white bg-danger")
    $.get(file, param, function (res) {
        console.log(res)
        console.log(param.route_id)
        if (res.status == 1) {
            $(".input-move-" + param.move_id).addClass("bg-success text-white")
        } else if (res.status == -1) {
            $(".input-move-" + param.move_id).addClass("bg-danger text-white").focus()
            alert(res.error)
        } else {
            $(".input-move-" + param.move_id).addClass("bg-danger text-white")
        }
    }, "json")
}


function dimeCargas(_callback = "") {
    var file = "php/functions_cargas.php";
    var param = {
        fase: "dimeCargasOdoo",
        t: sessionStorage.getItem("token")
    };
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            alert(proceso.error);
            return false;
        }
        $("#tabla-cargas tbody").html(proceso.boxes)
        // var rows=""
        // $.each(proceso.datos,function(i,v){
        //     var row=$("<tr class='"+v.ruta+"'></tr>")
        //     var tds=[]
        //     tds.push(v.planta)
        //     tds.push(v.movimiento_fecha)
        //     tds.push(v.producto_clave)
        //     tds.push(v.pedido)
        //     tds.push(v.remision)
        //     tds.push(v.factura)
        //     tds.push(v.determinante+":"+v.determinante_municipio+","+v.determinante_estado)
        //     tds.push(v.litros+" L")
        //     row.append("<td>"+tds.join("</td><td>"+"</td>"))
        //     console.log("<td>"+tds.join("</td><td>"+"</td>"))
        // })

    }, "json").done(
        function () {

            $(".input-in-raloy").off("change").on("change", save_aptin_raloy)

            $("#loading").hide();


            if (typeof _callback == "function") {
                _callback()
            }
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
    var ruta = $("#modal-span-route").html();
    var remision = $("#modal-span-albaran").html();
    var target = objuploader.attr("target-folder");
    var parentdropboxtext = objuploader.find(".dropbox-messages");
    var divlinks = objuploader.find(".files-container");

    var mensajes = {
        "default": "",
        "errtipo": "El tipo de archivo seleccionado es inválido",
        "errsize": "El archivo excede el tamaño permitido (3MB)",
        "errgral": "Error",
        "loading": "Cargando"
    };

    var fd = new FormData();
    //    fd.append("fase", "upload");
    fd.append("ruta", ruta);
    fd.append("remision", remision);
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
            console.log(ruta, remision)
            getFilesRemision(ruta, remision)
            getTicketsBascula(ruta)

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
function getFilesRemision(ruta, remision) {
    var targetRemisiones = $(".dropbox-container[target-folder='remisiones']").find(".files-container")
    var targetAptIns = $(".dropbox-container[target-folder='apt_ins']").find(".files-container")
    $("#dropbox-container-apt-ins").html("")

    targetRemisiones.html("")
    targetAptIns.html("")
    var param = {
        "fase": "getFilesRemision",
        "ruta": ruta,
        "remision": remision,
        t: sessionStorage.getItem("token")
    };
    $.get("php/upload.php", param, function (res) {
        console.log(res)
        targetRemisiones.html(res["remisiones"])
        targetAptIns.html(res["apt_ins"])
    }, "json").done(function () {
        $(".deleteFile")
            .off("click")
            .on("click", function () {
                removeFile($(this));
            });
    });
}
function getTicketsBascula(ruta) {
    var target = $(".dropbox-container[target-folder='tickets_bascula']").find(".files-container")
    $("#dropbox-container-apt-ins").html("")

    target.html("")
    var param = {
        "fase": "getTicketsBascula",
        "ruta": ruta,
        t: sessionStorage.getItem("token")
    };
    $.get("php/upload.php", param, function (res) {
        console.log(res)
        target.html(res["tickets"])
    }, "json").done(function () {
        $(".deleteFile")
            .off("click")
            .on("click", function () {
                removeFile($(this));
            });
    });
}
function getFiles(ruta, remision) {

    $("#dropbox-container-remisiones").html("")
    $("#dropbox-container-tickets-bascula").html("")
    $("#dropbox-container-apt-ins").html("")
    var param = {
        "fase": "getFiles",
        "ruta": ruta,
        "remision": remision,
        t: sessionStorage.getItem("token")
    };
    $.get("php/upload.php", param, function (res) {
        console.log(res)
        // $("#dropbox-container-remisiones").html(res["links"]["remisiones"])
        // $("#dropbox-container-tickets-bascula").html(res["links"]["tickets_bascula"])
        // $("#dropbox-container-apt-ins").html(res["links"]["apt_ins"])
    }, "json").done(function () {
        $(".deleteFile")
            .off("click")
            .on("click", function () {
                removeFile($(this));
            });
    });
}

function removeFile(obj) {
    if (window.confirm("Desea ELIMINAR el archivo " + obj.attr("filename") + " ?")) {
        var file = "php/upload.php";
        var param = {
            fase: "removeFile",
            file: obj.attr("filename"),
            folder: obj.attr("folder"),
            t: sessionStorage.getItem("token")
        };
        $.get(file, param, function (proceso) {
            if (proceso === "") {
                $(obj).parent().remove();
            } else {
                alert("Error");
                console.log(proceso)
            }
        });
    }

}