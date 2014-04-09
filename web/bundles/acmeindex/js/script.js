$(document).ready(function(){

    var url = $("#form").attr("action");


    // Loads the active state of checkbox after loading page
    if ($("#view-page").length > 0)
    {
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: url,
            data: "loadActive=",
            success: function(response)
            {
                $.each(response.arrLoadActive, function(i,ind) {
                    $(".checkSave").each(function() {
                        if ($(this).attr("value") == ind)
                        {
                            $(this).attr('checked','checked');
                        }
                    });
                });
            }
        })
    }


    /**
     * Saves checked sources for wiew in future
     */
    $("#viewForm_Save").click(function()
    {
        var arrSaveInd = [];

        $(".checkSave").filter(':checked').each(function() {
            arrSaveInd.push($(this).attr("value"));
        });

        if (!arrSaveInd.length)
        {
            arrSaveInd = -1;
        }

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: url,
            data: "arrSaveInd=" + $.toJSON(arrSaveInd),
            success: function(response)
            {
                window.location.href = $(location).attr('href');
            }
        })
    });


    /**
     * Deletes checked sources
     */
    $("#viewForm_Delete").click(function()
    {
        var arrDeleteInd = [];

        $(".checkDelete").filter(':checked').each(function() {
            arrDeleteInd.push($(this).attr("value"));
        });

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: url,
            data: "arrDeleteInd=" + $.toJSON(arrDeleteInd),
            success: function(response)
            {
                window.location.href = $(location).attr('href');
            }
        })
    });

})