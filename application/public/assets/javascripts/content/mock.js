/**
 * Created by j.murray on 1/23/2015.
 */
$(document).ready(function(){

    $('input, textarea').placeholder();

    $('.flip-click').bind("click", function(){
        $('.flip-container').toggleClass('flipped');
    });

});