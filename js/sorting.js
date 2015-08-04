jQuery(document).ready(function($) {
		$( "#course_list_in_block" ).sortable({
				stop: function() {
						var ids = {};
						var index = 0;
						$('#course_list_in_block li').each(function() {
								var id = $(this).find('.li_course').data('id');
								ids[index] = id;
								++ index;
						});
						$.post(
								wwwroot + '/blocks/my_enrolled_courses/sorting.ajax.php',
								{
										courseids: ids
								}
						);
				}
		});
		$( "#course_list_in_block" ).disableSelection();
		$( ".colapsible_icon" ).click(function() {
				var icon = $( this ).text();
				$( this ).parent().parent().find( ".course_modules" ).slideToggle( "fast" );
				if( icon == '+' ) {
						$( this ).text( '-' );
				} else if( icon == '-' ) {
						$( this ).text( '+' );
				}
		});
});