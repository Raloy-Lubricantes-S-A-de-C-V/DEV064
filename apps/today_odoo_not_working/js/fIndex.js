$(document).ready(function() {
    $(".tab").click(function() {
        console.log("clicked");
        var url = $(this).attr("app");
        window.location.href = url;
    });
})