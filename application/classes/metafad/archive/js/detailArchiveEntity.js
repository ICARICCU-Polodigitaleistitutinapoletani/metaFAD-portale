$(document).ready(function(){
  $('.Rtable-cell').each(function(){
    var arrayLabels = ["Codice identificativo di sistema",
                       "Altri codici identificativi",
                       "Acronimo di sistema",
                       "Codice identificativo precedente",
                       "Altri codici identificativi",
                       "Acronimo di sistema",
                       "Codice identificativo precedente",
                       "Livello superiore",
                       "Tipologia",
                       "Numero di ordinamento provvisorio",
                       "Numero di ordinamento definitivo",
                       "Codice di classificazione",
                       "Segnatura precedente",
                       "Consistenza"];

    if(arrayLabels.indexOf($(this).html()) != -1)
    {
      $(this).addClass('hide');
      $(this).next().addClass('hide');
    }
  });

  $('.Rtable-cell--1of4').each(function(){
    var text = $(this).html();
    $(this).next().find('.label').each(function(){
      if($(this).html() == text)
      {
        $(this).addClass('hide');
      }
    });
  });

  $('.label').each(function(){
    var arrayLabels = ["codifica della data","validità","secolo","specifica","note alla data","Codifica della data","Validità","Secolo","Specifica","Note alla data","Qualifica della data","Nota alla datazione"];
    if(arrayLabels.indexOf($(this).html()) != -1)
    {
      $(this).addClass('hide');
      $(this).next().addClass('hide');
    }
    if($(this).html() == 'estremo cronologico testuale' || $(this).html() == 'Estremo cronologico testuale')
    {
      $(this).addClass('hide');
      $(this).siblings('.label').each(function(){
        if($(this).html() == 'data')
        {
          $(this).addClass('hide');
          $(this).next().addClass('hide');
        }
      });
    }

    if($(this).html() === 'Collegamento Soggetto Produttore')
    {
      $(this).addClass('hide');
    }
  });

  $('.Rtable-cell').each(function(){
    var arrayEntity = ["Denominazione",
                       "Cronologia",
                       "Titolo",
                       "Attività, professione o qualifica",
                       "Soggetti produttori"]
    if(arrayEntity.indexOf($(this).html()) != -1)
    {
      $(this).next().find('.label').each(function(){
        if($(this).html() === 'Data'){
          $(this).addClass('hide');
          $(this).next().addClass('hide');
        }
      });
    }
  });
});
