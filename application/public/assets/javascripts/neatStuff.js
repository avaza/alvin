/**
 * Created by j.murray on 1/21/2015.
 */
//resize an image on demand
function resizeImg() {
    var thisImg= $('.login-logo');
    var refH = thisImg.height();
    var refW = thisImg.width();

    thisImg.children("img").height(refH);
    thisImg.children("img").width(refW);
    thisImg.children("img").css('padding', "10px");
}

$(window).resize(function(){
    resizeImg();
});