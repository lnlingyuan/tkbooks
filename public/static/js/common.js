$(function(){

    /*回到顶部按钮事件*/
    var t = '<div id="toTop"><span class="glyphicon glyphicon-arrow-up"></span></div>'
        , o = $(window)
        , n = o.height()
        , i = $(document).height()
        , a = $(t);
    $("body").append(a),
        $(window).scroll(function() {

            n < i && n < $(window).scrollTop() ? $("#toTop").fadeIn() : $("#toTop").fadeOut();

        }),
        $("#toTop").click(function() {
            $(window).scrollTop("0")
        });



    /*搜索框事件*/
    $("#search_box_btn").click(function() {
        var e = $(".secah-con");
        "none" == e.css("display") ? (e.fadeIn(),
            e.find("input[type=text]").focus()) : e.fadeOut()
    });

    /*自动截取文字长度，超过部分"..."代替*/
    jQuery.fn.limit=function(){
        var self = $("[limit]");
        self.each(function(){
            var objString = $(this).text();
            var objLength = $(this).text().length;
            var num = $(this).attr("limit");
            if(objLength > num){
                $(this).attr("title",objString);
                objString = $(this).text(objString.substring(0,num)+"...");
            }
            $(this).attr("title"," ")
        })
    }


    $(function(){
        $(".synopsis").attr("limit",100);
        $("#chapterlists>li>a").attr("limit",20);
        $(".newchap").attr("limit",145);
        $(".sinfobox h3").attr("limit",7);
        $(".sinfobox p").attr("limit",13);
        $(".bib_chapter").attr("limit",13);
        $("[limit]").limit();
    });

    /*显示隐藏的书籍简介*/
    $(".zk,.sq").click(function(){
        $(".all").toggle();
        $(".part").toggle();
    });

    /*小说章节内的目录显示和隐藏*/
    $("#js_clset").click(function(){
       $(".pcbook_mask").show();
       $(".catalogbox").show();
    });

    $("#js_clos").click(function(){
       $(".pcbook_mask").hide();
       $(".catalogbox").hide();
    });

    $(".shelful li").mouseover(function(){
        $(this).addClass('active');
    });
    $(".shelful li").mouseout(function(){
        $(this).removeClass('active');
    });

    //pc端目录倒序
    $('#js_sort').click(function(){
        var len = $("#chapterlists>li").length;
        var liCollection = $("#chapterlists>li");
        var html='';
        $("#chapterlists>li").each(function(i){
            html+='<li>';
            html+= liCollection.eq(len-i-1).html();
            html+='</li>';
        });
        $('#chapterlists').html(html);

        //图标变化
        $(this).toggleClass('ascsort');
        $(this).toggleClass('descsort');
    });




    //登陆弹窗
    $("#login-link,#login-vipjump-close").click(function () {
        $("#backwhite").toggle();
        $("#loginkuan").toggle();
    });


    //登陆后悬停显示下拉菜单
    $(".user-login-info").mouseover(function(){
        $(".nickname-list").css('display','block');
    });

    $(".user-login-info").mouseout(function(){
        $(".nickname-list").css('display','none');
    });



    //注册时的同意选中框
    $(".register_checkbox").click(function () {
       $(".register_ok").toggleClass('ant-checkbox-checked');
       $("#signUp").toggleClass('disabled');
       $("#signUp").attr('disabled',false);
    });

    //登陆时的同意选中框
    $(".login_checkbox").click(function () {
        $(".login_ok").toggleClass('ant-checkbox-checked');
        $("#signUp").toggleClass('disabled');
        $("#signUp").attr('disabled',false);
    });


    //首页幻灯片效果
   $(".smallul li").click(function () {
       var xiabiao = $(".smallul li").index(this);
       huangdenpian(xiabiao);
   });

    $(".caright").click(function () {
        $(".smallul li").each(function(e){
            if($(this).hasClass('on')){
                if(e<5){
                    var le = e+1;
                }else{
                    var le=0;
                }
                huangdenpian(le);
                return false;
            }
        });
    });
    $(".caleft").click(function () {
        $(".smallul li").each(function(e){
            if($(this).hasClass('on')){
                if(e>0){
                    var le = e-1;
                }else{
                    var le=5;
                }
                huangdenpian(le);
                return false;
            }
        });
    });
    var timer = setInterval(function working(){$(".caright").click();},5000);
    $(".caleft,.caright").mouseover(function(){
        clearInterval(timer);//关闭
    }).mouseout(function(){
        timer=setInterval(function working(){$(".caright").click();},5000);//重新启动
    });


//小说目录点击用post传值并跳传,弃用中
    $(".menus").on("click",".books_url",function () {
        var chapter_url =$(this).attr("chapter_url");
        var books_id = $(this).attr("books_id");
        var target = $(this).attr("target");
        post("/info/index",{chapter_url:chapter_url,books_id:books_id},target);
    });

//post方式传值并跳转 ----  弃用中
    function post(URL, PARAMS, target) {
        target=target||'_blank';
        var temp = document.createElement("form");
        temp.action = URL;
        temp.method = "post";
        temp.target = target;
        temp.style.display = "none";
        for (var x in PARAMS) {
            var opt = document.createElement("textarea");
            opt.name = x;
            opt.value = PARAMS[x]; // alert(opt.name)
            temp.appendChild(opt);
        }
        document.body.appendChild(temp);
        temp.submit(); return temp;
    }




    //手机版js
    $("#openSearchPopup,#closeSearchPopup").click(function () {
        $("#searchPopup").toggle();
    });

    $("#bookSummary").click(function () {

        $(this).toggleClass('cover_sy');
    });

    $(".openshelf").click(function () {
       $.post('/shelf/mobileShelf','',function (e) {
          if(e.code!='200'){
              layer.open({
                  content: '骚年你还未登陆，无法浏览书架'
                  ,btn: ['登陆', '继续浏览']
                  ,yes: function(index){
                      window.location.href=e.res;
                      layer.close(index);
                  }
              });
          }else{
              window.location.href=e.res;
          }
       });
    });


    $(".openshelf_pc").click(function () {
        $.post('/shelf/mobileShelf','',function (e) {
            if(e.code!='200'){
                layer.msg(e.msg);
            }else{
                window.location.href=e.res;
            }
        });
    });



    $("#openGuide").click(function () {
        $(this).toggleClass("active");
        $("#guide").toggleClass("active");
        $("#header").attr('open','');
        if($(this).hasClass('active')){
            $("#header").attr("open");
        }else{
            $("#header").removeAttr("open");
        }

    });

    //功能暂未开放
    $(".ondo_mobile").click(function () {
        //提示
        layer.open({
            content: '功能暂未开放'
            ,skin: 'msg'
            ,time: 2 //2秒后自动关闭
        });
    });








   function huangdenpian(dj) {



       $(".smallul li").removeClass('on');
       $(".smallul li img").animate({"width":"48px","height":"64px","margin-top": "30px","display":"inline-block"});
       $(".smallul li:eq("+dj+")").addClass('on');
       $(".smallul li:eq("+dj+")").find('img').animate({"width":"90px","height":"120px","margin-top": "0px"});
       if($('.smallul li').is(':animated')){
           $('.smallul li').stop(true,true);
       }

       var textleft =new Array();
       textleft[0] = new Array(0,12,24,36,-36,-24);
       textleft[1] = new Array(-12,0,12,60,48,36);
       textleft[2] = new Array(-24,-12,0,12,-72,-60);
       textleft[3] = new Array(-36,-24,-12,0,12,84);
       textleft[4] = new Array(-48,-36,-24,-12,0,12);
       textleft[5] = new Array(12,-48,-36,-24,-12,0);
       $(".txtinof li").each(function(e){
           var leftpx = (textleft[dj][e])*100;
           leftpx+='px';
           $(this).css('left',leftpx);
       });
   }


});


/*关键词检索高亮标出
     *param idHtmlContent 需要检索的HTML内容ID
     *param keyword 关键字，多个以空格隔开
     */
function keywordHighlight(idHtmlContent,keyword) {
    var content= $("#"+idHtmlContent).html();//获取内容
    if ($.trim(keyword)==""){
        return;//关键字为空则返回
    }
    var htmlReg = new RegExp("\<.*?\>", "i");
    var arrA = new Array();
    //替换HTML标签
    for (var i = 0; true; i++) {
        var m = htmlReg.exec(content);
        if (m) {
            arrA[i] = m;
        }else {
            break;
        }
        content = content.replace(m, "{[(" + i + ")]}");
    }
    words = unescape(keyword.replace(/\+/g, ' ')).split(/\s+/);
    //替换关键字
    for (w = 0; w < words.length; w++) {
        var r = new RegExp("(" + words[w].replace(/[(){}.+*?^$|\\]/g, "\\$&") + ")", "ig");
        content = content.replace(r, "<span style=\"color:#f36f20;\">"+words[w]+"</span>");//关键字样式
    }
    //恢复HTML标签
    for (var i = 0; i < arrA.length; i++) {
        content = content.replace("{[(" + i + ")]}", arrA[i]);
    }
    $("#"+idHtmlContent).html(content);
}


//书籍轮播
function lunpo(obj,next,prev,time=6000) {
    var obj = obj;
    var next = next;
    var prev = prev;
    var num = (obj.length)/6-1;



    setInterval(function working(){
        var end = endnum();
        if(end == num*(-1056)){
            obj.css({"position":"relative"});
            obj.animate({left: '0'},"slow");
        }else{
            next.click();
        }

    },time);


    next.click(function () {

        var end = endnum();
        if(end == num*(-1056)){
            return false;
        }

        obj.css({"position":"relative"});
        obj.animate({left: '+=-1056px'},"slow");
    });
//
    prev.click(function () {

        var end = endnum();
        if(end == 0 || isNaN(end) ){
            return false;
        }

        obj.css({"position":"relative"});
        obj.animate({left: '+=1056px'},"slow");

    });
    function endnum() {
        //防止多次点击出现的bug
        if(obj.is(':animated')){
            obj.stop(true,true);
        }
        var end = obj.css('left');
        end = parseInt(end);

        return end;
    }

}

