var file = "php/sakbe.php";
$(document).ready(function () {
//    dimeruta(10240,1517);
//dimeInfo();
    $("#buscarentrega").click(dimeDestinosEntrega);
    $("#identificardestinos").click(identificardestinos);
    $("#dimeruta").click(dimeruta);
    $("#dimetotales").click(dimetotales);
});
function dimeDestinosEntrega() {
    var param = {
        fase: "dimeDestinosEntrega",
        id_entrega: $("#id_entrega").val()
    };
    $.get(file, param, function (proceso) {
        $("#tbldestinos tbody").html(proceso.tabla);
    }, "json");
}
function identificardestinos() {
    $(".ruteable").each(function () {
        var search = $(this).find(".mpio").html() + "," + $(this).find(".edo").html();
        var obj = $(this).find(".iddestino");
        dimeInfoDestino(search, obj);
    });
}
function dimeInfoDestino(search, obj) {
    console.log(search);
    $.post("http://gaia.inegi.org.mx/sakbe_v3/buscadestino",
            {
                type: "json",
                key: "Xm9Fi1n0-i3a9-6avL-hHFv-UbHOgkplHf6e",
                buscar: search,
                num: 1
            })
            .done(function (resultado) {
                obj.html(resultado.data[0].id_dest);
            });
}
function dimeruta() {
    var origen = "";
    $(".iddestino").each(function () {
        var destino=$(this).html();
        console.log(origen);
        console.log(destino);
        if (origen !== "" && origen!==destino) {
            var param={
                        type: "json",
                        key: "Xm9Fi1n0-i3a9-6avL-hHFv-UbHOgkplHf6e",
                        v: 8,
                        dest_i: origen,
                        dest_f: destino
                    }
            $.post("http://gaia.inegi.org.mx/sakbe_v3.1/cuota",
                    param)
                    .done(function (resultado) {
                        $("#resultados").append("<span class='tag'>"+param.dest_i+" - "+param.dest_f+"</span><br/>");
                        $("#resultados").append("$<span class='casetas'>"+resultado.data.costo_caseta+"</span> ");
                        $("#resultados").append("<span class='kms'>"+resultado.data.long_km+"</span> kms<br/>");
                        console.log(resultado);
                    });
        }
        origen=destino;
    });

}
function dimetotales(){
    var sumapeajes=0;
    var sumakms=0;
    $(".casetas").each(function(){
        sumapeajes+=eval($(this).html());
    });
    $(".kms").each(function(){
        sumakms+=eval($(this).html());
    });
    $("#totalpeajes").html(sumapeajes);
    $("#totalkms").html(sumakms);
}