$(document).ready(function() {
    console.log("Calling API");
    callAPI("GPS/Asset", (apiResponse) => {

        var options = ""
        console.log(apiResponse)
        $.each(apiResponse.Data, (i, asset) => {
            console.log(i, asset)
            options += "<option City='" + asset.Position.City + ", " + asset.Position.Province + "' Latitude='" + asset.Position.Latitude + "' value='" + asset.ID + "'>" + asset.Description + "</option>"
        })
        $("#selector").html(options).off("change").on("change", function() {
            $("#content").html($("#selector option:selected").attr("City"));
        })
    })
})

function callAPI(api, _callback) {
    authenticate((res) => {
        console.log("auth ok", res)
        var url = "https://tlshosted.fleetcomplete.com/v8_5_0/Integration/WebAPI/" + api

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            headers: {
                'ClientID': res.ClientID,
                'UserID': res.UserID,
                'Token': res.Token,
            },
            contentType: 'application/json; charset=utf-8',
            success: function(result) {
                _callback(result)
            },
            error: function(error) {
                console.log("ERROR", error)
            }
        });

        // $.get(url, cred, function(apiRes) {
        //     _callback(apiRes)
        // }, "json")
    })
}

function authenticate(_callback) {
    var param = {
        clientid: "42116",
        userlogin: "hyescas",
        userpassword: "Totich182308"
    }
    $.get("https://tlshosted.fleetcomplete.com/Authentication/Authentication.svc/authenticate/user", param, function(res) {
        res.ClientID = param.clientid
        console.log
        _callback(res)
    }, "json")
}