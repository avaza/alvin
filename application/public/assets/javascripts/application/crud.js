var one = 380;
var two = 770;
var thr = 1420;
var fou = 2720;

$(document).ready(function(){
	$('#field-language').change(function(){
		language = $('#field-language').val();
		get_lang_details(language);
	});
	set_frame();
});

$('#per_page').change(function(){
	set_frame();
});

function get_lang_details(lang){
	$.ajax({
        url: '/crud/get_lang_details/' +lang,
        dataType:'json',
        success: function(details){
            $('#field-language_code').val(details.language_code);
            $('#field-lid').val(details.lid);
        }
	});
}

function set_frame(){
	if($('#per_page').val() == 10){
		parent.change_iframe(one);
	} else if($('#per_page').val() == 25){
		parent.change_iframe(two);
	} else if($('#per_page').val() == 50){
		parent.change_iframe(thr);
	} else{
		parent.change_iframe(fou);
	}
}