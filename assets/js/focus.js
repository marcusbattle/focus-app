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

	$('#add-task-view .close').on('click', function(){
		$('#add-task-view').addClass('hide');
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

	$(document).on('click', '.task', function( click ) {

		click.stopPropagation();

		var task = $(this).closest('.task');
		var target = $( click.target );


		if ( $(target).hasClass('close-task') ) {

			$(task).removeClass('selected');

		} else {

			if ( $(task).hasClass('selected') ) {

				// $(task).removeClass('selected');

			} else {

				$('.task.selected').removeClass( 'selected' );
				$( this ).addClass('selected');

			}

		}
	
		console.log( target );

	
	});

	$(document).on('click', '.task .actions .delete', function(){

		var task = $(this).closest('.task');
		var task_id = $(task).data('task-id');

		$(task).fadeOut();

		request = $.ajax({
			url: focus.ajax_url,
			type: "POST",
			data: {
				task_id : task_id,
				action : 'delete_task'
			},
			dataType: "JSON"
		});

		request.done( function( response ) {
			
		});

	});

	$(document).on('click', '.task .actions .complete', function(){

		var task = $(this).closest('.task');
		var task_id = $(task).data('task-id');

		

		request = $.ajax({
			url: focus.ajax_url,
			type: "POST",
			data: {
				task_id : task_id,
				action : 'complete_task'
			},
			dataType: "JSON"
		});

		request.done( function( response ) {
			
			$(task).removeClass('complete');
			$(task).addClass( response.status );
		});

	});

	$(document).on('click', '.add-note-button', function(){

		var task = $(this).closest('.task');
		var task_id = $(task).data('task-id');
		var new_note = $(task).find('textarea.new-note').val();

		request = $.ajax({
			url: focus.ajax_url,
			type: "POST",
			data: {
				task_id : task_id,
				action : 'add_note',
				new_note : new_note
			},
			dataType: "JSON"
		});

		request.done( function( response ) {
			
			if ( response.success ) {

				location.reload();
				
			}

		});

	});

});