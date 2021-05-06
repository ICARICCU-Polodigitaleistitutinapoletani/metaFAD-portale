;(function ($, window, document, undefined) {
$(document).ready(function() {
(function main() {
    $(function() {
        initAccordionAndCollapsibleBox();
        // initMoreFactes();
        fixModuleParallaxHeight();
        initPopup();
        initRequestIFrame();
        setTargetBlank();
        fixPseudoclassPlaceholderForMSIE();
        initScrollAddHeaderClass();
        loginError();
    });


    function setTargetBlank() {
        $("a[rel='external']")
            .attr("target", "_blank")
            .attr("rel", "external noopener noreferrer");
    }

    function fixPseudoclassPlaceholderForMSIE() {
        // per browser explorer attiva la pseudoclass placeholder
        if ($.browser && $.browser.msie) {
            $('input[placeholder]').each(function() {
                var input = $(this);
                $(input).val(input.attr('placeholder'));

                $(input).focus(function() {
                    if (input.val() == input.attr('placeholder')) {
                        input.val('');
                    }
                });

                $(input).blur(function() {
                    if (input.val() == '' || input.val() == input.attr('placeholder')) {
                        input.val(input.attr('placeholder'));
                    }
                });
            });
        };
    }

    function initAccordionAndCollapsibleBox() {
        $(".js-show-element").click(function(e){
            e.preventDefault();
            $(this).next().slideToggle('fast', function(){
                $(this).prev(".js-show-element").toggleClass("active");
                $(this).prev(".js-show-element").children('.js-showHide-message').toggle();
            });
        });

        $(".js-show-element-reverse").on('click',function(e){
            e.preventDefault();
            $(this).next().slideToggle('fast', function(){
                $(this).prev(".js-show-element-reverse").toggleClass("active");
            });
        });

    }

    function initMoreFactes()
    {
        $('ul.js-solrFacets li.more').hide();
        $('a.js-solrFacetClosing').parent().hide();
        $('a.js-solrFacetOpening').click(function(e){
            e.preventDefault();
            $(this).closest('ul').find('li.more,li.show-less-link').show();
            $(this).parent().hide();
            return false;
        });

        $('a.js-solrFacetClosing').click(function(e){
            e.preventDefault();
            $(this).closest('ul').find('li.more,li.show-more-link').hide();
            return false;
        });
    }

    function fixModuleParallaxHeight() {
        // add style to change the position of main content according to header height
        var headerHeight = $('#header').outerHeight();
        $('.mcp-module-main-parallax').css('top', headerHeight);
    }

    function initPopup() {
        // show pop-up
        // info: http://stackoverflow.com/questions/24853723/jquery-popup-windows-automaticly-pops-up
        $(".js-my-pop-up").removeClass( "hidden" );
        $('[data-popup-target]').click(function () {
            $('.block-my-pop-up').hide();
            var activePopup = $(this).attr('data-popup-target');
			$(activePopup).removeClass("hide").show();
            return false;
        });
        $('[data-popup-target-close]').click(function () {
            var activePopup = $(this).attr('data-popup-target-close');
			$(activePopup).addClass("hide");
            return false;
        });
        /* questa regola permette di chiudere il pop-up quando si preme fuori dal pop-up, via css devi determinare la class :before altrimenti non viene tenuto di conto
        $('.js-my-pop-up').before().click(function () {
            $(this).toggleClass('js-called');
            return false;
        });*/

/*
        if($('.js-my-pop-up').before() !== undefined) {
          $('.js-my-pop-up').before().click(function () {
            if($(this).attr('id') !== 'plogin' && !$(this).hasClass('js-popup-carrello')) {
              $(this).toggleClass('js-called');
              return false;
            }
          });
        }
*/
        $(document).mouseup(function(e) {
            var container = $('.js-my-pop-up');

            var popupTarget = e.target.dataset.popupTarget;
            var classRemove = e.target.classList[0];

            if ($(e.target).hasClass('js-openhere')) {
                return;
            }

            if(popupTarget !== undefined) {
                var isSocial = popupTarget.indexOf('social');
            }

            if ((!container.is(e.target) && container.has(e.target).length === 0) || classRemove == 'icon-remove2231') {
                $('.block-my-pop-up').hide();
            }

            if(isSocial != 1) {
                $('.bcp-block-pop-up-share').removeClass('js-called');
            }
        });

    }

    function initScrollAddHeaderClass() {
        // add class when start scroll
        $(window).on('scroll', function(){
            if ($(window).scrollTop()) {
                $('#header').addClass('init-scroll');
            } else {
                 $('#header').removeClass('init-scroll');
            }
        });
    }

    function initScrollAddOuterClass() {
         // user scroll
        var iScrollPos = 0;

        $('#outer').scroll(function () {
            var iCurScrollPos = $(this).scrollTop();
            if (iCurScrollPos > iScrollPos) {
                $(this).addClass('user-init-scroll');
            } else {
                $(this).removeClass('user-init-scroll');
            }
            iScrollPos = iCurScrollPos;
        });
    }

    function initRequestIFrame() {
        $('.js-get-request-iframe').on('click',function(){
            $('#popupRequest-iframe').attr('src',$(this).data('iframe'));
        });
    }

    function loginError() {
      if($('#js-error-login').html() == '') {
        $('#js-error-login').addClass('hide');
      }
      if($('#js-error-login').html() != '') {
        $('#plogin').addClass('js-called');
      }
    }

})();
});
})(jQuery, window, document);
