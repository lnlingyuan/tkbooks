$(function () {


//当阅读取到底部时自动加载下一章，由于体验效果不佳，暂时弃用
    //定义事件锁，防止重复请求同个章节
//    var is_running = true;
//
//    $(window).scroll(function() {
//
//        var foot = $(document).scrollTop()+1;
//
//        if ( foot >= $(document).height() - $(window).height()) {
//
//            if(is_running == true) {
//
//                is_running = false;
//                /*下一章id*/
//                var chapter_name = $("input[name='chapter_name']").val();
//                chapter_name = $.trim(chapter_name);
//                var books_id = "{$books_id}";
//
//
//                $.post("/info/nextInfo",{chapter_name:chapter_name,books_id:books_id},function(result){
//                    if(result.code=='200'){
//                        /*将下一章内容添加进来*/
//                        var html = '';
//                        html = '<section class="read-section jsChapterWrapper" >';
//                        html +='<h3>'+result.chapter_name+'</h3>';
//                        html +='<p>'+result.content+'</p>';
//                        html +='<div class="read-chapter-forum"></div></section>';
//
//                        $(".read-article").append(html);
//
//                        $("input[name='chapter_name']").val(result.chapter_name);
//                        $("h1").html(result.chapter_name);
//
//                        is_running = true;
//                    }
//
//
//                }, "json");
//
//            }
//        }
//
//    });


    //开启或关闭悬浮层
    $("#asideOverlay,#lookchapter").click(function () {
        $("#asideChapter").toggle();
//    $("#asideChapter").slideToggle(1500);
    });







    //当屏幕中间被点击时
    $("#pageRead").click(function(event){
        var clientWidth = document.documentElement.clientWidth;
        var clientHeight = document.documentElement.clientHeight;


        var e = event || window.event;

        var centerx = Math.round(clientWidth/3);
        var centery = Math.round(clientHeight/3);

        var wcolumn = parseFloat($(".H .read-section").css("column-width"))+16;
        var trans =  $(".jsChapterWrapper").css('transform');
        var reg="\\((.+?)\\)";
        trans=parseFloat( trans.match(reg)[1].split(',')[4] );

        //容量实际宽度
        var kd = document.getElementById("jsChapterWrapper").scrollWidth;
        //如果为true则为左右滑动事件
        var Slide =  $("#pageRead").hasClass('H');

        if(e.clientX>centerx && e.clientX<(2*centerx) && e.clientY>centery &&  e.clientY<(2*centery) ){
            $("#pageReadOpt").toggle();
            $(".read-opt-public").removeClass('active');
        }else if(e.clientX>(2*centerx) &&  Slide==true){
            trans =trans-wcolumn;
            //判断已经滑动到底部
            if(Math.abs(trans)<kd){
                $(".jsChapterWrapper").css('transform','translateX('+trans+'px)');
            }else{
                $('#btnLoadNextChapter>span').trigger('click') ;
            }
        }else if(e.clientX<(2*centerx) &&  Slide==true ){

            trans =parseFloat(trans)+wcolumn;
            if(trans<0 || trans==0){
                $(".jsChapterWrapper").css('transform','translateX('+trans+'px)');
            }else{
                $('#readProgPrev>span').trigger('click') ;
            }

        }


    });

    //当屏幕中间被点击时,这个是有悬浮层时的点击
    $("#pageReadOpt").click(function(event){
        var clientWidth = document.documentElement.clientWidth;
        var clientHeight = document.documentElement.clientHeight;
        var e = event || window.event;
        var centerx = Math.round(clientWidth/3);
        var centery = Math.round(clientHeight/3);

        if(e.clientX>centerx && e.clientX<(2*centerx) && e.clientY>centery &&  e.clientY<(2*centery) ){
            $("#pageReadOpt").fadeToggle("slow");
            $(".read-opt-public").removeClass('active');
        }


    });



    //更换背景色
    $("input[name='skin']").click(function () {
        var color = $(this).val();
        $("input[name='background']").val('skin-'+color).trigger("change");
        $("body").attr('class','skin-'+color);

        //当改变背景色时，自动更换为日间阅读
        $("input[name='time']").val('0').trigger("change");
        $("#readBtnMode").attr("data-mode",'night');
        $("#readBtnMode>h4").text('夜晚');
    });

//更换白天黑夜
    $("#readBtnMode").click(function () {
        $("body").toggleClass('read-night');
        var has = $("body").hasClass('read-night');
        if(has){
            $("#readBtnMode").attr("data-mode",'day');
            $("#readBtnMode>h4").text('日间');
            $("input[name='time']").val('1').trigger("change");
        }else{
            $("#readBtnMode").attr("data-mode",'night');
            $("#readBtnMode>h4").text('夜晚');
            $("input[name='time']").val('0').trigger("change");
        }



    });




    <!--底部选项卡切换-->
    $(".btn_bottom").click(function () {
        var obj_id = $(this).attr('id');

        var ac = $("#"+obj_id).attr('data-rel');
        var has = $("#"+ac).hasClass('active');

        $(".read-opt-public").removeClass('active');

        if(has==true){
            $("#"+ac).addClass('active');
        }

        switch (obj_id) {
            case ("readBtnMore"):
                $("#"+ac).toggleClass('active');
                break;
            case ("readBtnProg"):
                $("#"+ac).toggleClass('active');
                break;
            case ("readBtnSet"):
                $("#"+ac).toggleClass('active');
                break;

        }
    });



    var  left = 0, bgleft = 0;


    //改变字体大小
    $('.range_size').click(function(e){
        bgleft = $('#size_width').offset().left;
        left = e.pageX - bgleft;
        slide(left);

    });
    $('#readFontUp').click(function(e){
        var left = $("#range_size").css('border-left-width');
        var width =$("#size_width").width();
        var mean = parseFloat((width/7).toFixed(1));
        left = parseFloat(left)+mean;
        slide(left);

    });
    $('#readFontDown').click(function(e){
        var left = $("#range_size").css('border-left-width');
        var width =$("#size_width").width();
        var mean = parseFloat((width/7).toFixed(1));
        left = parseFloat(left)-mean;
        slide(left);

    });

    //滑动块的公共改变事件
    function slide(left) {

        //取得阅读条的长度
        var width =$("#size_width").width();

        if (left <= 0) {
            left = 0;
        }else if (left >= width) {
            left = width;
        }else{
            left = Math.ceil(left);
        }
        var mean = parseFloat((width/7).toFixed(1));
        var arr = new Array();
        for (var i=0;i<8;i++)
        {
            var n = Math.ceil(mean*i);
            arr[i] =[n,0.875+i*0.125];
        }
        var size ;
        $.each(arr,function (i,e){
            if(left<Math.ceil(mean)){
                left=0;
                size = '0.875rem';
                $('#chapterContent').css('font-size',size);

            }else if(left<e[0]){
                left =arr[i-1][0];
                size = arr[i-1][1]+'rem';
                $('#chapterContent').css('font-size', size);
                return false

            }else if(left==width){
                size = '1.75rem';
                $('#chapterContent').css('font-size',size);
            }

        });

        $("input[name='font']").val(left);
        $("input[name='size']").val(size).trigger("change");
        //    $.cookie('info_left', left, { expires: 7 });
        $('.range_size').css('border-left-width', left);
        $('.range_size').animate({'border-left-width':left},300);

//        $('.range-thumb').css('left', left);
//        $('.range-thumb').animate({'left':left},300);
    }


    //改变进度条
    $('.range_jdt').click(function(e){
        bgleft = $('.range').offset().left;
        left = e.pageX - bgleft;
        var width = $("#schedule").width();
        if (left <= 0) {
            left = 0;
        }else if (left > width) {
            left = width;
        }
        $('.range_jdt').css('border-left-width', left);
        $('.range_jdt').animate({'border-left-width':left},300);
        $('#read_jd').css('left', left);

        changejd(left);


    });

    //避免默认事件 2018.7.10 更新 优化uc浏览器左右滑动时候页面被拖动
    //这个加上时，浏览器无法下拉
    // document.addEventListener('touchmove', function(e) {
    //     e.preventDefault();
    // }, { passive: false });

    function dragSlide(id,tid) {
        this.minDiv = document.getElementById(id); //小方块
        var lvDiv = $("."+tid); //小方块

        this.width = parseInt(window.getComputedStyle(this.minDiv, null).width); //小方块的宽度

        this.lineDiv = this.minDiv.parentNode; //长线条

        //滑动的数值呈现
        this.vals = this.minDiv.children[0];

        var that = this;
        var lastX = null; //判断鼠标移动方向，解决向左侧滑动时候的bug
        var move = function(e) {
            var x = e.touches[0].pageX,
                direction = '';
            if (lastX == null) {
                lastX = x;
                return;
            }
            if (x > lastX) {
                direction = 'right';
            } else if (x < lastX) {
                direction = 'left';
            } else {
                direction = '';
            }

            var lineDiv_left = that.getPosition(that.lineDiv).left; //长线条的横坐标
            var minDiv_left = x - lineDiv_left; //小方块相对于父元素（长线条）的left值
            if (minDiv_left >= that.lineDiv.offsetWidth - that.width) {
                minDiv_left = that.lineDiv.offsetWidth - that.width;
            }
            if (minDiv_left < 0) {
                minDiv_left = 0;
            }
            //设置拖动后小方块的left值
            that.minDiv.style.left = minDiv_left + "px";
            lvDiv.css("border-left-width",minDiv_left + "px");
            changejd(minDiv_left);


            //percent百分比改为如下所示,解决开始和最后滑动的体验不好问题
            var percent = (minDiv_left / (that.lineDiv.offsetWidth - that.width)) * 100;
            if (percent < 0.5 && direction == 'right') {
                percent = Math.ceil(percent);
            } else if (percent > 0.5 && direction == 'right') {
                percent = Math.floor(percent);
            } else {
                percent = Math.ceil(percent);
            }
            // that.vals.innerText = percent;
        }
        //获取元素的绝对位置,工具函数
        this.getPosition = function(node) {
            var left = node.offsetLeft; //获取元素相对于其父元素的left值var left
            var top = node.offsetTop;
            current = node.offsetParent; // 取得元素的offsetParent
            // 一直循环直到根元素

            while (current != null) {
                left += current.offsetLeft;
                top += current.offsetTop;
                current = current.offsetParent;
            }
            return {
                "left": left,
                "top": top
            };
        }
        this.minDiv.addEventListener("touchmove", move);


    }
    var drag0 = new dragSlide("read_jd","range_jdt");

    //取消移动端手势长按弹出提示框的操作
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });





    //上下滑动
    $(".Slide_tb").click(function () {
        $("#pageRead").removeClass('H');
        $(".jsChapterWrapper").css('transform','translateX(0px)');
        $("input[name='slide']").val('Slide_tb').trigger("change");
    });
    //左右滑动
    $(".Slide_lr").click(function () {
        $("#pageRead").addClass('H');
        $("input[name='slide']").val('Slide_lr').trigger("change");
    });


//设置调整时ajax记录相应值
    $(".fit").bind("change", function(){
        var font = $("input[name='font']").val();
        var size = $("input[name='size']").val();
        var background = $("input[name='background']").val();
        var slide = $("input[name='slide']").val();
        var time = $("input[name='time']").val();

        $.post("/info/fit",{font:font,size:size,background:background,slide:slide,time:time},function (e) {
            
        });
    });

    //下载书籍
    $("#btnDownloadFreeChapters").click(function () {
        //提示
        layer.open({
            content: '由于版权问题，下载功能暂时关闭'
            ,skin: 'msg'
            ,time: 2 //2秒后自动关闭
        });
    });



});
//进度条改变后相应数值变化
function changejd(left) {
    var width = $("#schedule").width();
    var jd_num = (100/width*left).toFixed(1);
    $(".readProgJd").text(jd_num+'%');
    var total = $(".chapter_num").html();

    var jg = total-Math.round(jd_num/100*total)-1;
    $(".chapter-index").each(function (i,e) {
        if(jg<0){
            jg=0;
        }
        if(i==jg){
            var chapter_name =$(this).html();
            $(".ell").text(chapter_name);
            return false;
        }
    });
}


//页面加载后进度条位置
function chapterwz() {
    var name = $("#jsChapterWrapper>h3").text();
    $(".chapter-index").each(function (i,e) {
        var chapter_name =$(this).html();
        if(name == chapter_name){

            var total = $(".chapter_num").html();
            var jg = total-i-1;
            var percentage =(jg/total*100).toFixed(1);
            var width = $("#schedule").width();
            var left = (width*percentage/100);

            $(".readProgJd").text(percentage+'%');
            $('.range_jdt').css('border-left-width', left);
            $('.range_jdt').animate({'border-left-width':left},300);
            $('#read_jd').css('left', left);
        }

    });

}

function base64Encode(input){
    var rv;
    rv = encodeURIComponent(input);
    rv = unescape(rv);
    rv = window.btoa(rv);
    return rv;
}


