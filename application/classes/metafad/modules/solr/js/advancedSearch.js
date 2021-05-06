//File per la gestione dell'autocomplete dei campi in ricerca avanzata
$(document).ready(function(){
  var searchRequest = null;

  $('body').on('keyup','.bselect-search-input',function(){
    var value = $(this).val();
    var fieldName = $(this).parents('.bselect').siblings('select').data('fieldid');
    var autocompleteUrl = $('#advancedSearchForm').data('url');
    var that = $(this).parents('.bselect');

    var fieldPrefix = $(this).parents('.bselect').siblings('select').attr('name');
    var selectId = $(this).parents('.bselect').siblings('select').attr('id');

    var type = $(this).parents('.bselect').siblings('select').data('type');
    if (searchRequest != null)
    searchRequest.abort();
    searchRequest = $.ajax({
      type: "POST",
      url: Pinax.ajaxUrl + '&controllerName=metafad.modules.solr.controllers.ajax.Autocomplete',
      data: {
        value : value,
        fieldName : fieldName,
        type : type,
        autocompleteUrl: autocompleteUrl
      },
      success: function (data, textStatus, jqXHR) {
        var result = data.result;
        //Refresh della lista della combobox
        $('select[name="'+fieldPrefix+'"]').html(result);

        $("[id='"+selectId+"']").bselect('refresh');
        that.find('.bselect-option-list').css('height','100px');
        if(that.find('ul').html() != '')
        {
          that.find('.bselect-message').css('display','none');
        }
      }
    });

  });

  $('body').on('click','.bselect-label,.bselect-caret',function(){
    var type = $(this).parent().siblings('select').data('type');
    var that = $(this).parent();

    if(type == 'list')
    {
      var fieldName = $(this).parent().siblings('select').data('fieldid');
      var fieldPrefix = $(this).parent().siblings('select').attr('name');
      var selectId = $(this).parent().siblings('select').attr('id');

      var autocompleteUrl = $('#advancedSearchForm').data('url');
      var value = '';

      if (searchRequest != null)
      searchRequest.abort();
      searchRequest = $.ajax({
        type: "POST",
        url: Pinax.ajaxUrl + '&controllerName=metafad.modules.solr.controllers.ajax.Autocomplete',
        data: {
          value : value,
          fieldName : fieldName,
          type : type,
          autocompleteUrl: autocompleteUrl
        },
        success: function (data, textStatus, jqXHR) {
          var result = data.result;
          //Refresh della lista della combobox
          $('select[name="'+fieldPrefix+'"]').html(result);
          $("[id='"+selectId+"']").bselect('refresh');
          that.find('.bselect-option-list').css('height','100px');
          if(that.find('ul').html() != '')
          {
            that.find('.bselect-message').css('display','none');
          }
        }
      });
    }
  });

  $('.label').each(function(){
    var text = $(this).text();
    if(!$(this).next().hasClass('value'))
    {
      var childText = $(this).next().children('.label').first().text();
      if(text == childText)
      {
        $(this).addClass('hide');
      }
      else
      {
        //$(this).next().css('margin-left','10px');
      }
    }
  });
});
