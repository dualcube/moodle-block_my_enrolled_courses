define(['jquery', 'core/ajax','core/url'], function($, ajax, url) {
  return{
    sorting: function(){
            var ids = {};
            var index = 0;
            $('#course_list_in_block li').each(function() {
                var id = $(this).find('.li_course').data('id');
                ids[index] = id;
                ++ index;
            });
            var rooturl = url.fileUrl("/blocks/my_enrolled_courses/sorting.ajax.php", "");
            $.post(
                rooturl ,
                {
                    courseids: ids
                }
            );
        $( ".expandable_icon" ).unbind('click');
        $( ".expandable_icon" ).click(function() {
        var icon = $( this ).text();
        $( this ).parent().parent().find( ".course_modules" ).slideToggle( "slow" );
        if( icon == '+' ) {
            $( this ).text( '-' );
        } else if( icon == '-' ) {
            $( this ).text( '+' );
        }
    });
    },
    disablebutten: function(){
      $('#hide').attr('disabled', 'disabled');
      $('#visible').change(function() {
          $('#hide').removeAttr('disabled');
      });

      $('#show').attr('disabled', 'disabled');
      $('#hidden').change(function() {
          $('#show').removeAttr('disabled');
      });
    }
  };
});