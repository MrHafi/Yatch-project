jQuery(document).ready(function($){

    // URL PARAMS - pre-fill sidebar
    var urlParams = new URLSearchParams(window.location.search);

    if(urlParams.has('location') || urlParams.has('guests') || urlParams.has('checkin')){

        if(urlParams.get('guests'))   $('#guest_filter').val(urlParams.get('guests'));
        if(urlParams.get('checkin'))  $('#checkin').val(urlParams.get('checkin'));
        if(urlParams.get('checkout')) $('#checkout').val(urlParams.get('checkout'));

        // CHECKING THE RIGHT CHECKED RADIO BUTON FORM ALL AVAILANBLE
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
    function runFilter(){
        $('#yacht_results').html('<p>Fetching yachts...</p>');
        $.ajax({
            url:  ajax_object.ajax_url,
            type: 'POST',
            data: {
                action:   'yacht_sorting',
                sort:     $('#yacht_sort').val(),
                guests:   $('#guest_filter').val(),
                location: $('input[name="yacht_location"]:checked').val(),
                checkin:  $('#checkin').val(),
                checkout: $('#checkout').val()
            },
            success: function(response){
                $('#yacht_results').html(response);
            }
        });
    }

    // ALL FILTERS
    $('.yacht_filter').on('change', function(){
        runFilter();
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
        runFilter();
    });

});