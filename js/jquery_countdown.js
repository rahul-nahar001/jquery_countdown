// $Id$
(function ($, Drupal) {
  Drupal.behaviors.jquery_countdown = {
    attach: function (context, settings) {
      // Only process if the settings exist.
      if (drupalSettings.jquery_countdown.jquerycountdownblock) {
        // Loop through all the jQuery Countdown settings.
        jQuery.each(drupalSettings.jquery_countdown.jquerycountdownblock.options, function(countdown, options) {

          jQuery(countdown + ':not(.jquery-countdown-processed)', context).addClass('jquery-countdown-processed').each(function(){
              // Process the date properties if available.
              if (typeof (options.until) != "undefined") {
                options.until = Drupal.jQueryCountdownProcessDate(options.until);
              }
              if (typeof (options.since) != "undefined") {
                options.since = Drupal.jQueryCountdownProcessDate(options.since);
              }

              // Evaluate the callbacks as function names.
              if (typeof (options.onExpiry) == "string") {
                options.onExpiry = eval(options.onExpiry);
              }
              if (typeof (options.onTick) == "string") {
                options.onTick = eval(options.onTick);
              }
              console.log(options);
              console.log(this);
              jQuery(this).countdown(options);
          });

        });
      }
    // if(drupalSettings.jquery_countdown.countdownblock.accuracy != 'd'){
    //   init_countdown(drupalSettings.countdown.countdownblock.accuracy);
		// $("#block-countdown .block__content").onready();
    // }
    }
  };
  /**
   * Process date values for the jQuery Countdown plugin, based on the date type.
   */
  Drupal.jQueryCountdownProcessDate = function(dateVal) {

    if (typeof (dateVal) == "string") {
      // Create the Date using the string.
      return new Date(dateVal);
    } else if (typeof (dateVal) == "number") {
      // Return the number of seconds.
      return dateVal;
    } else if (dateVal instanceof Array || dateVal instanceof Object) {
      // Create the Date object from available values, avoiding passing invalid
      // objects.
      var date = new Date();
      for (i = 0; i < 6; i++) {
        dateVal[i] = dateVal[i] || 0;
      }
      date.setFullYear(dateVal[0]);
      date.setMonth(dateVal[1]);
      date.setDate(dateVal[2]);
      date.setHours(dateVal[3]);
      date.setMinutes(dateVal[4]);
      date.setSeconds(dateVal[5]);
      return date;
    }
    return dateVal;

  };

})(jQuery, Drupal);
