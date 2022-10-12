(function ($) {

    // Make sure you run this code under Elementor.
    $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction("frontend/element_ready/the7_nav-menu.default", function ($scope, $) {
           
            $( document ).ready(function() {

                $scope.find(".dt-sub-menu-display-on_click li.has-children, .dt-sub-menu-display-on_item_click li.has-children").each(function () {
                 
                    var item = $(this);
                    var itemLink = item.find(' > a'),
                    iconData = itemLink.find('.next-level-button i').attr('class')
                    iconDataAct = itemLink.find('.next-level-button').attr('data-icon');
                    $(this).each(function(){
                        var $this = $(this);
                        
                        $this_sub = $this.find(" > .dt-mega-menu-wrap > .vertical-sub-nav");
                        $this_sub.unwrap();
                        var subMenu = $this.find(" > .vertical-sub-nav");
                        if($this.find(".vertical-sub-nav li").hasClass("act")){
                            $this.addClass('active');
                        };


                        if($this.find(".vertical-sub-nav li.act").hasClass("act")){
                            $this.addClass('open-sub');
                            subMenu.stop(true, true).slideDown(100);
                             $(this).find(' > a').addClass('active');
                            $(this).find(' > a .next-level-button i').attr('class', iconDataAct);
                            subMenu.layzrInitialisation();
                        };
                        if(itemLink.hasClass('not-clickable-item') && $this.parents('nav').hasClass("dt-sub-menu-display-on_item_click")){
                            var clickItem = itemLink;
                        }else{
                             var clickItem =  itemLink.find(" > .next-level-button");
                        }
                        clickItem.on("click", function(e){
                            if(itemLink.hasClass('not-clickable-item') && itemLink.parents('nav').hasClass("dt-sub-menu-display-on_item_click")){
                                var $this = $(this),
                                    openIcon = $(this).find('.next-level-button i');
                            }else{
                                var $this = $(this).parent(),
                                    openIcon = $(this).find('i');
                            }
                            if ($this.hasClass("active")){
                                openIcon.attr('class', iconData);
                                subMenu.stop(true, true).slideUp(500, function(){
                                    $(" .main-nav").layzrInitialisation();
                                });
                                $this.removeClass("active");
                                $this.removeClass('open-sub');
                                $this.find('a').removeClass('act');
                            
                            }else{
                               openIcon.attr('class', iconDataAct);

                                $this.siblings().find(" .vertical-sub-nav").stop(true, true).slideUp(400);
                                subMenu.stop(true, true).slideDown(500);
                                $this.siblings().removeClass("active");
                                $this.addClass('active');
                                $this.siblings().removeClass('open-sub');
                                $this.addClass('open-sub');

                                $this.siblings().find("> a").removeClass("act");
                                $this.find('a').addClass('act');
                                
                                $(" .main-nav").layzrInitialisation();
                            };

                        })
                    });
                
                });
            });
        });
    });

})(jQuery);