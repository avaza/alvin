$(document).ready(function(){
    console.log('loaded');
    $('input, textarea').placeholder();

    $('.login-links a').bind("click", function(){
        $('#flip-form').toggleClass('flipped');
    });
    console.log(document.getElementById('flip-form').className);
});
