$(document).ready(function () {
    $(".hideable").hide();
    $("#loginBtn").click(function () {
        login();
    });
});
function login() {
//    var file = "php/functions_index.php";
//    var param = {fase: "dimeLotes"};
    var loginok = 1;
//    $.get(file, param, function (proceso) {
//        if (proceso.status !== 1) {
//            $(".hideable").hide();
//            alert(proceso.error);
//            return;
//        }
//
//
//    }, "json").done(
//            function () {

    if (loginok === 1) {
        $("#divLogin").html("<span id='welcome'>WELCOME</span><br/>Please select an option from the menu");
        $("#divParams dl").show();
        buttonsFunctionality();
    } else {
        alert("Login Failed");
    }
//            });


}
function dimeLotes() {
    
    var file = "php/functions_index.php";
    var param = {fase: "dimeLotes"};
    $.get(file, param, function (proceso) {
        if (proceso.status !== 1) {
            $(".hideable").hide();
            console.log("error: "+proceso.error);
            return;
        }
        $("#numLote").html(proceso.options);

    }, "json");


}
function buttonsFunctionality() {
    $("#batchBtn").click(
            function () {
                $(".hideable").hide();
                $("#batchOpts").show();
                dimeLotes();
                $("#consultar").click(function(){
                    var folio=$("#numLote").val();
                    var url="batch.php?f="+folio;
                    window.open(url);
                });
            });
            
    $("#datesBtn").click(
            function () {
                $(".hideable").hide();
                $("#datesOpts").show();

                var fecha = new Date();
                $("#fi").datepicker({
                    dateFormat: "yy-mm-dd"}).datepicker("setDate", fecha);
                $("#ff").datepicker({
                    dateFormat: "yy-mm-dd"}).datepicker("setDate", fecha);
            });
}