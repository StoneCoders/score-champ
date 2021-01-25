/**
 * Created by compie on 18/01/18.
 */

$(function() {

    $('.clickToHide').on('click', function ()
    {

        var gameId = $(this).data('gameId');
        if(gameId){
            if($(this).is(':checked'))
            {
                $("div").find("[data-showOrHide='" + gameId + "']").removeClass('hidden');
            }
            else
                {
                    $("div").find("[data-showOrHide='" + gameId + "']").addClass('hidden');

            }
        }
    });


    $('#insert-time-push').on('click', function ()
    {
        if($(this).is(':checked'))
        {
            $('.date-picker').prop('disabled',false);
            $('button[name=send_in][value="now"]').addClass('hidden');
            $('button[name=send_in][value="time"]').removeClass('hidden');
        }
        else
        {
            $('.date-picker').prop('disabled',true);
            $('button[name=send_in][value="now"]').removeClass('hidden');
            $('button[name=send_in][value="time"]').addClass('hidden');
            // alert('ההודעה תשלח במידי');
        }
    });

});