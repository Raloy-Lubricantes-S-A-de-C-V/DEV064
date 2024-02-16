$(document).ready(function () {
    $('.fill').on('click', function () {
        alert($(this));
        var $that = $(this);
        var percentage = $that.attr('data-fill');
        setTimeout(function () {
            animate($that, percentage, "#a0c884");
        }, 400);
    });
    $(".qtylts").on("keyup", function () {
        var tanque = $(this).parent().parent().parent().find(".tank");
        var texto_tanque = $(this).parent().parent().parent().find(".tank-text");
        var sltsActuales = $(this).parent().parent().parent().find(".ltsactuales");
        sltsActuales.html($(this).val());
        var scapacidad = $(this).parent().parent().parent().find(".capacidad");
        var ltsactuales = eval(sltsActuales.html().replace(",", ""));
        var capacidad = eval(scapacidad.html().replace(",", ""));
        var utilizacion = Math.round(eval(ltsactuales) / eval(capacidad) * 100) || 0;
        var utilizacion_perc = utilizacion + "%";
        tanque.attr("data-fill", utilizacion);
        texto_tanque.html(utilizacion_perc);

        animate(tanque, utilizacion);


    });
    $(".bardays").each(function () {
        animate($(this), $(this).attr("data-fill"));
    });
});
function animate(tanque, utilizacion, bgcolor, forecolor) {
    tanque.css("background", bgcolor);
    var percentage = (100 - utilizacion) || 0,
            percentage_initial = 100,
            percentage_current = percentage_initial,
            interval = 0.5;
    var interval_gradient = setInterval(function () {

        tanque.css(
                'background',
                'linear-gradient( ' + bgcolor + ' ' + percentage_current + '%,' + forecolor + ' ' + percentage_current + '%)'
                );

        percentage_current -= interval;
        if (percentage_current <= percentage)
            clearInterval(interval_gradient);
    }, 5);
}
;




/*
 // Works with jquery.inview.js
 $('.fill').on('inview', function(event, isInView, visiblePartX, visiblePartY) {
 if (isInView) {
 // element is now visible in the viewport
 if (visiblePartY == 'top') {
 // top part of element is visible
 } else if (visiblePartY == 'bottom') {
 // bottom part of element is visible
 } else {
 var $that = $(this);
 var percentage = $that.attr('data-fill');
 setTimeout(function(){
 animate( $that, percentage )
 }, 400);
 }
 } else {
 // element has gone out of viewport
 }
 });
 */


