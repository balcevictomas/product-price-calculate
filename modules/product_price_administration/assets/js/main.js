jQuery(document).ready(function() {
    var max_fields = 10;
    var wrapper = jQuery(".container1");
    var add_button = jQuery(".add_form_field");

    var x = 1;
    jQuery(add_button).click(function(e) {
        e.preventDefault();
        if (x < max_fields) {
            x++;
            jQuery(wrapper).append('<div><input type="text" name="width[]"/><input type="text" name="height[]"/><input type="text" name="price[]"/><a href="#" class="delete">Delete</a></div>'); //add input box
        } else {
            alert('You Reached the limits')
        }
    });

  jQuery(wrapper).on("click", ".delete", function(e) {
        e.preventDefault();
        jQuery(this).parent('div').remove();
        x--;
    })
});
