jQuery(document).ready(function($){

$('#yacht_sort').change(function(){

var sort = $(this).val();

$.ajax({
url: ajax_object.ajax_url,
type: "POST",
data:{
action:"yacht_sorting",
sort:sort
},
success:function(response){

$('#yacht_results').html(response);

}

});

});

});