// item selection
    $(document).on("click",".photolistdiv li",function () {
      $(this).toggleClass('selected');
      if ($('.photolistdiv li.selected').length == 0)
        $('.photolistdiv .select').removeClass('selected');
      else
        $('.photolistdiv .select').addClass('selected');
      counter();
    });

// all item selection
$('.photolistdiv .select').on("click",function () {
  if ($('.photolistdiv li.selected').length == 0) {
    $('.photolistdiv li').addClass('selected');
    $('.photolistdiv .select').addClass('selected');
  }
  else {
    $('.photolistdiv li').removeClass('selected');
    $('.photolistdiv .select').removeClass('selected');
  }
  counter();
});

// number of selected items
function counter() {
  if ($('.photolistdiv li.selected').length > 0)
    $('.photolistdiv .send').addClass('selected');
  else
    $('.photolistdiv .send').removeClass('selected');
  $('.photolistdiv .send').attr('data-counter',$('li.selected').length);
}

function initSelect(){
                                if ($('.photolistdiv li.selected').length == 0)
                                $('.photolistdiv .select').removeClass('selected');
                              else
                                $('.photolistdiv .select').addClass('selected');
                              counter();
}