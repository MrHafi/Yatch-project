jQuery(document).ready(function($){

    var currentPage = 1;

    // URL PARAMS - pre-fill sidebar from homepage search
    var urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('location') || urlParams.has('guests') || urlParams.has('checkin')){
        if(urlParams.get('guests'))   $('#guest_filter').val(urlParams.get('guests'));
        if(urlParams.get('checkin'))  $('#checkin').val(urlParams.get('checkin'));
        if(urlParams.get('checkout')) $('#checkout').val(urlParams.get('checkout'));
        if(urlParams.get('location')){
            var locVal = urlParams.get('location');
            $('input[name="yacht_location"]').each(function(){
                if($(this).val() === locVal){
                    $(this).prop('checked', true);
                    return false;
                }
            });
        }
    }

    // CHECKIN MIN DATE
    $('#checkin').on('change', function(){
        $('#checkout').attr('min', $(this).val());
    });

    // FILTER FUNCTION
    function runFilter(page, append){
        if(!append){
            $('#yacht_results').html('<p>Fetching yachts...</p>');
        } else {
            $('.yacht-load-more').text('Loading...').prop('disabled', true);
        }

        $.ajax({
            url:  ajax_object.ajax_url,
            type: 'POST',
            data: {
                action:   'yacht_sorting',
                sort:     $('#yacht_sort').val(),
                guests:   $('#guest_filter').val(),
                location: $('input[name="yacht_location"]:checked').val(),
                checkin:  $('#checkin').val(),
                checkout: $('#checkout').val(),
                page:     page
            },
    success: function(response){
    var $wrap   = $('<div>').html(response);
    var $button = $wrap.find('.yacht-load-more-wrap').detach();

    $('.yacht-load-more-wrap').remove();

    if(append){
        $('#yacht_results .row').append($wrap.find('.col-md-4'));
    } else {
        $('#yacht_results').html($wrap.find('.row'));
    }

    if($button.length){
        $('#yacht_results').after($button);
    }
}
        });
    }

    // ALL FILTERS - reset to page 1
    $('.yacht_filter').on('change', function(){
        currentPage = 1;
        runFilter(currentPage, false);
    });

    // LOAD MORE BUTTON
    $(document).on('click', '.yacht-load-more', function(){
        currentPage = $(this).data('page');
        runFilter(currentPage, true);
    });

    // AVAILABILITY BUTTON
    $(document).on('click', '#check_availability', function(){
        var checkin  = $('#checkin').val();
        var checkout = $('#checkout').val();

        if(!checkin || !checkout){
            $('#availability_msg').text('Please select both dates.');
            return;
        }
        if(checkout <= checkin){
            $('#availability_msg').text('Check-out must be after check-in.');
            return;
        }
        $('#availability_msg').text('');
        currentPage = 1;
        runFilter(currentPage, false);
    });

});