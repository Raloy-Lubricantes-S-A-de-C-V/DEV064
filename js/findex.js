var sessionToken = sessionStorage.getItem("token")

$(document).ready(function() {
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    })

    $(".tag").click(function() {
        var url = $(this).attr("app");
        window.location.href = url + "?t=" + sessionToken;
    });
    $("#loading").hide();
});