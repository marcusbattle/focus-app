jQuery(document).ready(function($){
	
	$('#add-task-button-view').on('click', function(){
		$('#add-task-view').removeClass('hide');
	});

	$('#add-task-form form button[type="submit"]').on( 'click', function( form_submit ) {
		
		form_submit.preventDefault();

		var form = $(this).closest('form');
		var form_is_valid = validate_form();
		var submit_button = $(this);

		if ( form_is_valid ) {

			$( submit_button ).attr( 'disabled', true );

			request = $.ajax({
				url: focus.ajax_url,
				type: "POST",
				data: $( form ).serialize(),
				dataType: "JSON"
			});

			request.done( function( response ) {
				
				$( submit_button ).removeAttr( 'disabled' );
				
				alert( response.message );
				
				location.reload();
				
			});

		}

	});

	function validate_form() {

		var form_is_valid = true;

		$('#add-task-form form .field.required').removeClass('error');

		$('#add-task-form form .field.required').each(function(){
			
			if ( $(this).find('input,select,textarea').val() == '' ) {
				
				form_is_valid = false;
				$(this).addClass('error');
				
			}

		});

		return form_is_valid;

	}

});