(function($){

  /**
   * Sets up the toggle on the postboxes.
   */
  function postboxes(){
    // close postboxes that should be closed
    $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
    // postboxes setup
    postboxes.add_postbox_toggles('');
  }
  
  
  function toggleSubArea(){
    $(':checkbox.toggle-sub-area').each(function(){
      console.log(this.checked);
      var $this = $(this);
      var subArea = $($this.attr('rel')).eq(0);
      subArea.append('<div class="sub-area-disable-screen"> </div>');
      
      subArea.toggleClass('sub-area-disabled', !this.checked);
      
      
      $this.change(function(){
        subArea.toggleClass('sub-area-disabled', !this.checked);
        console.log(this.checked);
      });
    });
  }
  
  
  
  

$(document).ready( function() {
  //postboxes();
  //toggleSubArea();
});




})(jQuery);