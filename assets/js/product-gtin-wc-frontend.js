( function( $ ) {
    'use strict';

    var $body = $("body"),
      $form = $(".variations_form"),
      $product_id = $form.data("product_id"),
      $code = '';

      if (typeof $product_id !== "undefined") {
        $code = $body.find(
          ".wpm_pgw_code[data-product-id=" + $product_id + "]"
        );
      } else {
        $code = $form.closest(".summary").find(".wpm_pgw_code");
      }
     

    var $reset = $code.length > 0 ? $code.text() : "",
      $hide = "yes" == wpm_product_gtin.hide_is_empty ? true : false,
      $parent_code = $code.parent();
        
    $.fn.wpm_gtin_variations = function() {

        $form.on( 'found_variation', function( event, variation ){

            if ( variation.wpm_pgw_code ) {
                $code.text( variation.wpm_pgw_code );
                $parent_code.show();
            } else {
                $code.wpm_reset_content();
            }
        });

        $form.on( 'reset_data', function(){
            $.fn.wpm_reset_content();
        });

    };

    $.fn.wpm_reset_content = function(){
        if( $reset !== $code.text() ){
            $code.text($reset);
        }
        if( $hide && $reset == '' ){
            $parent_code.hide();
        }
    };

    if( $body.hasClass('single-product') ){
        if( $hide && $reset == '' ){
            $parent_code.hide();
        }
        $.fn.wpm_gtin_variations();
    }
})( jQuery );