(function( $ ) {
	'use strict';

  $(function() {

      // Attach autocomplete to the appropriate form fields
      $( ".autocomplete-multiple" ).each(function( index ) {
        var id = $( this ).attr('id');

        var input = document.getElementById(id);

        new Awesomplete(input, {
        	filter: function(text, input) {
        		return Awesomplete.FILTER_CONTAINS(text, input.match(/[^,]*$/)[0]);
        	},

        	item: function(text, input) {
        		return Awesomplete.ITEM(text, input.match(/[^,]*$/)[0]);
        	},

        	replace: function(text) {
        		var before = this.input.value.match(/^.+,\s*|/)[0];
        		this.input.value = before + text + ", ";
        	}
        });
    });




    $('input#murmurations_image_select').click(function(e) {

                e.preventDefault();
                var image_frame;
                if(image_frame){
                    image_frame.open();
                }
                // Define image_frame as wp.media object
                image_frame = wp.media({
                              title: 'Select Media',
                              multiple : false,
                              library : {
                                   type : 'image',
                               }
                          });

                          image_frame.on('close',function() {
                             // On close, get selections and save to the hidden input
                             // plus other AJAX stuff to refresh the image preview
                             var selection =  image_frame.state().get('selection');
                             var gallery_ids = new Array();
                             var my_index = 0;
                             selection.each(function(attachment) {
                                gallery_ids[my_index] = attachment['id'];
                                my_index++;
                             });
                             var ids = gallery_ids.join(",");
                             $('input#murmurations_image_id').val(ids);
                             Refresh_Image(ids);
                          });

                         image_frame.on('open',function() {
                           // On open, get the id from the hidden input
                           // and select the appropiate images in the media manager
                           var selection =  image_frame.state().get('selection');
                           var ids = $('input#murmurations_image_id').val().split(',');
                           ids.forEach(function(id) {
                             var attachment = wp.media.attachment(id);
                             attachment.fetch();
                             selection.add( attachment ? [ attachment ] : [] );
                           });

                         });

                       image_frame.open();
        });

        /* Get add-on form html when networks list is updated */
        $('input#murmurations_networks').blur(function(e) {

          var networks = $('input#murmurations_networks').val();

          var data = {
              action: 'murmurations_get_addon_fields',
              networks: networks
          };

          //alert("Doing AJAX request with data"+data);

          jQuery.get(ajaxurl, data, function(response) {
              console.log("Logging AJAX response");
              console.log(response);
              if(response.success === true) {
                jQuery('#addon-fields').html(response.data);
              }
          });

        });

     });


})( jQuery );

// Ajax request to refresh the image preview
function Refresh_Image(the_id){
        var data = {
            action: 'murmurations_get_image',
            id: the_id
        };

        jQuery.get(ajaxurl, data, function(response) {

            if(response.success === true) {
                jQuery('#murmurations-preview-image').replaceWith( response.data.image );
            }
        });
}

function addAddOnSchemaFormFields(id,html){
  jQuery('#addon-fields').html(html);
}
