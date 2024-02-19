var php = 'php/fIndex.php';

$(document).ready(function () {
    $("#menu div").click(function () {
        window.location.href = $(this).attr("page");
    });
    $("#tabInicio").addClass("tabselected");
});
