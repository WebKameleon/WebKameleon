function goto_lang(langs)
{
    var ref = document.referrer;
    var loc=location.href;
    
    if (ref.length)
    {
        loca=loc.split('/');
        refa=ref.split('/');
        
        if (loca[0]==refa[0] && loca[2]==refa[2]) return;
    }
    var lang = window.navigator.userLanguage || window.navigator.language;
    lang=lang.substr(0,2);

    for (key in langs)
    {
        if (key==lang) location.href=langs[lang];
    }
}