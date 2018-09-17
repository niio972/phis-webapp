var currentBackground = 0;

var backgrounds = [];

backgrounds[0] = 'images/background/wallpaper_grapes_vine.jpg';

backgrounds[1] = 'images/background/wallpaper_green_house.jpg';

backgrounds[2] = 'images/background/wallpaper_leaf.jpg';

backgrounds[3] = 'images/background/wallpaper_tomato.jpg';

backgrounds[4] = 'images/background/wallpaper_vine.jpg';

function changeBackground() {

    currentBackground++;

    if(currentBackground > 4) currentBackground = 0;

    $('.wrap-image').fadeOut(1500,function() {
        $('.wrap-image').css({
            'background-image' : "url('" + backgrounds[currentBackground] + "')"
        });
        $('.wrap-image').fadeIn(1500);
    });


    setTimeout(changeBackground, 20000);
}

$(document).ready(function() {
    setTimeout(changeBackground, 0);  

}); 
