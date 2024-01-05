jQuery(document).ready(function($) {
    $('.target_popup').on('click', function(event) {
        event.preventDefault();
        
        var popup = $(this).parent().parent().find('.popup');
        popup.toggleClass('d-none');
        
        // Show or hide overlay based on popup visibility
        if(popup.is(':visible')) {
            $('.overlay').show();
        } else {
            $('.overlay').hide();
        }
    });

    // Optionally: Close the popup when clicking outside
    $('.overlay').on('click', function() {
        var popup = $(this).parent().parent().find('.popup');
        popup.toggleClass('d-none');
        $(this).hide();
    });

    // $('.target_popup').on('click', function() {
    //     $(this).parent().parent().find('.popup').removeClass('d-none');
    // });
});

