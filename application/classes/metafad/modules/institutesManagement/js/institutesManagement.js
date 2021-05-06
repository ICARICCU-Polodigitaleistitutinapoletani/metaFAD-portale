$(document).ready(function(){
  $('#institute').on('change',function(){
    $('#name').val($(this).select2('data').text);
  });
})
