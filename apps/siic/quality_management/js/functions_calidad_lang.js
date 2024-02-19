function changeLang() {
    var lang = $("#lang").html();
    if (lang === "eng") {
        var index = 0;
        var langItems = new Array();
        langItems.push("SKYBLUE TEST RESULT REPORT");
        langItems.push("Batch Number");
        langItems.push("Date of Sample");
        langItems.push("Date of Analysis");
        langItems.push("Analist");
        langItems.push("Normative References");
        langItems.push("TEST RESULTS");
        langItems.push("Parameter");
        langItems.push("Unit");
        langItems.push("Limits");
        langItems.push("Value");
        langItems.push("Min");
        langItems.push("Max");
        langItems.push("Urea Content");
        langItems.push("Density at 20°C");
        langItems.push("Refractive Index at 20°C");
        langItems.push("Alkalinity as NH3");
        langItems.push("Biuret");
        langItems.push("Aldehydes");
        langItems.push("Insoluble Matter");
        langItems.push("Phosphate (PO4)");
        langItems.push("Calcium");
        langItems.push("Iron");
        langItems.push("Copper");
        langItems.push("Zinc");
        langItems.push("Chromium");
        langItems.push("Nickel");
        langItems.push("Aluminium");
        langItems.push("Magnesium");
        langItems.push("Sodium");
        langItems.push("Potassium");
        langItems.push("Identity");
        langItems.push("- End Of Text -");

console.log(langItems);
        $(".langC").each(function () {
            $(this).html(langItems[index]);
            index = index+1;
        });
    } else {
        return;
    }
}


