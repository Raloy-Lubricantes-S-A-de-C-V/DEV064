$(document).ready(function () {
    console.log($(location).attr('href'));
    console.log($(location));
});
//$("#btnAddPrecedencia").click(function () {
//    addPrecedencia();
//});
function addPrecedent(obj) {

    let value = $("#selprecedents").val();
    let text = $("#selprecedents option:selected").text();
    ;
    console.log("apr:" + text);
    $("#projectDate").parent().append("<input id='currprecid" + value + "' type='hidden' name='precedentids[]' value='" + value + "'/>");
    $("#precedentstoadd").append("<div style='padding:3px;' precid='"+value+"'>" + text + " <a style='cursor:pointer;color:orangered;font-size:70%;' onclick='removeprecedent($(this).parent());'>Eliminar</a></div>");
}
//function addPrecedencia() {
//    let idprecedencia = $("#selPrecedencias").val();
//}
//function removePrecedencia() {
//
//}
function precedentsCheck(obj) {
//    let deadline=$("#projectDate").val();
//    return false;
    console.log(obj);
    return true;
}
function removeprecedent(obj){
    let precid=obj.attr("precid");
    $("#currprecid"+precid).remove();
    obj.remove();
}