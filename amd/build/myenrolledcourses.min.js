define(['jquery', 'core/ajax', 'core/sortable_list'], function($, ajax, SortableList ) {
  return{
    sorting: function(){
        
        new SortableList('ul#course_list_in_block');
        $('ul#course_list_in_block > *').on(SortableList.EVENTS.DROP, function(evt, info) {
            var ids = {};
            var courseids = [];
            var index = 0;
            var str = '[\"';
            $('.li_course').each(function() {
                var id = $(this).data('id');
                if(id != null && !courseids.includes(id) ){
                    courseids[index] = id;
                    if(index!=0)
                    str += '\",\"';
                    str +=  JSON.stringify(id);
                    ++ index;
                }
            });
            str += '\"]';
            ids.courseids = str;
            var promises = ajax.call([
                {
                    methodname: 'moodle_my_enrolled_courses_shorting',
                    args: ids
                }
            ])[0];
           
        });

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
    showhide: function(){
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