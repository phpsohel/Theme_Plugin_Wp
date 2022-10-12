(function ($) {

    // Make sure you run this code under Elementor.
    $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction("frontend/element_ready/the7_photo-scroller.default", function ($scope, $) {
           
            $( document ).ready(function() {

                $scope.find(".photoSlider").each(function () {
                    var $this = $(this);

                    $this.photoSlider();
                    $this.parents(".photo-scroller").css("visibility", "visible");
                    $this.parents(".photo-scroller").each(function(){
                        var $this = $(this);
                        
                        $(".btn-cntr, .slide-caption", $this).css({
                            "bottom": parseInt($this.attr("data-thumb-height")) + 15
                        });

                        if( $this.hasClass("hide-thumbs")){
                            $this.find(".hide-thumb-btn").addClass("act");
                            $(".scroller-thumbnails", $this).css({
                                "bottom": -(parseInt($this.attr("data-thumb-height")) +20)
                            });
                            $(".btn-cntr, .slide-caption", $this).css({
                                "bottom": 5 + "px"
                            });
                        }
                        $(".hide-thumb-btn", $this).on("click", function(e){
                            e.preventDefault();
                            var $this = $(this),
                                $thisParent = $this.parents(".photo-scroller");
                            if( $this.hasClass("act")){
                                 $this.removeClass("act");
                                $thisParent.removeClass("hide-thumbs");
                                $(".scroller-thumbnails", $thisParent).css({
                                    "bottom": 0
                                });
                                $(".btn-cntr, .slide-caption", $thisParent).css({
                                    "bottom": parseInt($thisParent.attr("data-thumb-height")) + 15
                                });

                            }else{
                                 $this.addClass("act");
                                $thisParent.addClass("hide-thumbs");
                                $(".scroller-thumbnails", $thisParent).css({
                                    "bottom": -(parseInt($thisParent.attr("data-thumb-height")) +20)
                                });
                                $(".btn-cntr, .slide-caption", $thisParent).css({
                                    "bottom": 5 + "px"
                                });
                            }
                        });
                    });
                    

                     function launchFullscreen(element) {
                        if(element.requestFullscreen) {
                            element.requestFullscreen();
                        } else if(element.mozRequestFullScreen) {
                            element.mozRequestFullScreen();
                        } else if(element.webkitRequestFullscreen) {
                            element.webkitRequestFullscreen();
                        } else if(element.msRequestFullscreen) {
                            element.msRequestFullscreen();
                        }
                    }
                    function exitFullscreen() {
                        if(document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if(document.mozCancelFullScreen) {
                            document.mozCancelFullScreen();
                        } else if(document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        }
                    };

                    /* !- Fullscreen button */
                    if(!dtGlobals.isWindowsPhone){
                        $(".full-screen-btn").each(function(){
                            var $this = $(this),
                                $thisParent = $this.parents(".photo-scroller"),
                                $frame = $thisParent.find(".ts-wrap");
                            document.addEventListener("fullscreenchange", function () {
                                if(!document.fullscreen){
                                    $this.removeClass("act");
                                    $thisParent.removeClass("full-screen");
                                    $("body, html").css("overflow", "");
                                    var scroller = $frame.data("thePhotoSlider");
                                    if(typeof scroller!= "undefined"){
                                        scroller.update();
                                    };
                                }
                            }, false);
                            document.addEventListener("mozfullscreenchange", function () {
                                if(!document.mozFullScreen){
                                    $this.removeClass("act");
                                    $thisParent.removeClass("full-screen");
                                    $("body, html").css("overflow", "");
                                }
                            }, false);
                            document.addEventListener("webkitfullscreenchange", function () {
                                if(!document.webkitIsFullScreen){
                                    $this.removeClass("act");
                                    $thisParent.removeClass("full-screen");
                                    $("body, html").css("overflow", "");
                                    var scroller = $frame.data("thePhotoSlider");
                                    if(typeof scroller!= "undefined"){
                                        scroller.update();
                                    };
                                }
                            }, false);
                            $(window).on("debouncedresize", function() {
                                var scroller = $frame.data("thePhotoSlider");
                                if(typeof scroller!= "undefined"){
                                    scroller.update();
                                };
                             })
                        })
                         this.fullScreenMode = document.fullScreen || document.mozFullScreen || document.webkitIsFullScreen;
               
                        $(".full-screen-btn").on("click", function(e){
                            e.preventDefault();
                                var $this = $(this),
                                $thisParent = $this.parents(".photo-scroller"),
                                $frame = $thisParent.find(".ts-wrap"),                                      
                                $thumbs = $thisParent.find(".scroller-thumbnails").data("thePhotoSlider"),
                                $scroller = $frame.data("thePhotoSlider");
                            $this.parents(".photo-scroller").find("figure").animate({"opacity": 0},150);
                            if( $this.hasClass("act")){
                            
                                $this.removeClass("act");
                                exitFullscreen();
                                $thisParent.removeClass("full-screen");

                                setTimeout(function(){
                                    $this.parents(".photo-scroller").find("figure").delay(600).animate({"opacity": 1},300)
                                }, 300);
                            }else{
                                 $this.addClass("act");
                                $thisParent.addClass("full-screen");
                                launchFullscreen(document.documentElement);
                                $("body, html").css("overflow", "hidden");
                                setTimeout(function(){
                                    $this.parents(".photo-scroller").find("figure").delay(600).animate({"opacity": 1},300)
                                }, 300)
                            }
                            var scroller = $frame.data("thePhotoSlider");
                            if(typeof scroller!= "undefined"){
                                scroller.update();
                            };
                        });
                    }
                   
                
                });
            });
        });
    });

})(jQuery);