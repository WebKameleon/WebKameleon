var tag = document.createElement('script');
tag.src = "//www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);


function onYouTubeIframeAPIReady() {    
    jQueryKam('.wkw_youtube_iframe').each(function() {
        var mute=jQueryKam(this).attr('mute');
        var player=new YT.Player(jQueryKam(this).attr('id'), {
            events: {
                'onReady': function () {
                    if (mute=="1") player.mute();
                }
            }
        });
    });

}