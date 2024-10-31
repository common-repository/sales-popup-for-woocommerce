jQuery(document).ready(function($) {
    jQuery.ajax({
        type: "post",
        url: WTConfig.url,
        data: "action=ajax_product",
        success: function(result){
            if(result['html']) {
                if(result['names'] && result['names'] != null) {
                    var yaSeteados = accessCookie('wts_names');
                    if(yaSeteados == '') {
                        var yaSeteados = new Array();
                    }else{
                        var yaSeteados = JSON.parse(yaSeteados);
                    }
                    yaSeteados.push(result['names'].trim());
                    createCookie('wts_names', JSON.stringify(yaSeteados));
                }

                if(result['id_line'] && result['id_line'] != null) {
                    var idsSeteados = accessCookie('wts_ids');
                    if(idsSeteados == '') {
                        var idsSeteados = new Array();
                    }else{
                        var idsSeteados = JSON.parse(idsSeteados);
                    }
                    idsSeteados.push(result['id_line']);
                    createCookie('wts_ids', JSON.stringify(idsSeteados));
                }
            
                 //Print content
                var contenido = '<div id="WTSPopup" class="animated '+WTConfig.delay+' '+WTConfig.duration+' '+WTConfig.effect+' wts_popup '+WTConfig.position+'"><a class="wts_close_popup">X</a>'+result['html']+'</div>';
                $("body").delay(parseInt(WTConfig.delayPopup)).append(contenido);
                setTimeout(() => {
                    $(".wts_popup").fadeOut(parseInt(WTConfig.delayPopup));
                    setTimeout(() => {
                        $(".wts_popup").remove();
                    }, parseInt(WTConfig.delayPopup))
                }, parseInt(WTConfig.timeout));
            }
        }
    });
    $(document).on("click", ".wts_close_popup", function(){
        $(".wts_popup").fadeOut(parseInt(500));
        setTimeout(() => {
            $(".wts_popup").remove();
        }, parseInt(600))
    });
    function createCookie(cookieName,cookieValue,daysToExpire) {
          var date = new Date();
          date.setTime(date.getTime()+(daysToExpire*24*60*60*1000));
          document.cookie = cookieName + "=" + cookieValue + "; expires=" + date.toGMTString();
    }
    function accessCookie(cookieName) {
          var name = cookieName + "=";
          var allCookieArray = document.cookie.split(';');
          for(var i=0; i<allCookieArray.length; i++)
          {
            var temp = allCookieArray[i].trim();
            if (temp.indexOf(name)==0)
            return temp.substring(name.length,temp.length);
       	  }
        	return "";
    }
    
    
});