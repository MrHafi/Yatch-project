jQuery(document).ready(function($){

$('.yacht_filter').change(function(){

var sort = $('#yacht_sort').val();
var guest = $('#guest_filter').val();
var location = $('input[name="yacht_location"]:checked').val();
console.log('location:', location);




$.ajax({
url: ajax_object.ajax_url,
type: "POST",
data:{
action:"yacht_sorting",
sort:sort,
guests: guest,
location: location

},
success:function(response){

$('#yacht_results').html(response);

}

});

});

});


