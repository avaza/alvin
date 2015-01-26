$(document).ready(function(){

    $('input, textarea').placeholder();

    $('.flip-click').bind("click", function(){
        $('.flip-container').toggleClass('flipped');
    });

});
