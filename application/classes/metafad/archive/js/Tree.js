function treeGen() {

    var id = $('#id').val();
    var parentId = $('#parentId').val();

    var getRoot = false;

    $(".gerarchia").fancytree({
        extensions: ["dnd", "glyph"],
        checkbox: false,
        clickFolderMode: 1,
        activate: function (event, data) {
            return true;
        },
        glyph: {
            map: {
				doc: "fa fa-file-text",
				docOpen: "fa fa-file-text",
                checkbox: "fa fa-square-o",
                checkboxSelected: "fa fa-check-square",
                checkboxUnknown: "fa fa-share",
                dropMarker: "fa fa-arrow-right",
                expanderClosed: "fa fa-caret-right",
                expanderLazy: "fa fa-caret-right",
                expanderOpen: "fa fa-caret-down",
                folder: "fa fa-folder",
                folderOpen: "fa fa-folder-open",
            }
        },
        dnd: {
            autoExpandMS: 400,
            draggable: {
                zIndex: 1000,
                scroll: false,
                revert: "invalid"
            },
            preventVoidMoves: true,
            preventRecursiveMoves: true,
            dragStart: function (node, data) {
                if (node.parent.children.length > 1) {
                    node.parent.folder = true;
                }
                else {
                    node.parent.folder = false;
                }
                node.parent.renderStatus();
                return true;
            },
            dragEnter: function (node, data) {
                return true;
            },
            dragOver: function (node, data) {
            },
            dragLeave: function (node, data) {

            },
            dragStop: function (node, data) {
                if (node.parent.children.length > 0) {
                    node.parent.folder = true;
                }
                else {
                    node.parent.folder = false;
                }
                node.parent.renderStatus();
            },
            dragDrop: function (node, data) {
                $('#myModalConfirm .modal-body').text('Sicuro di voler proseguire?')
                $('#myModalConfirm').modal();
                $('.annulla').click(function () {
                    return;
                });
                $('.ok').click(function () {
                    node.setExpanded(true).always(function () {
                      var parentId = data.node.data.id;
                      var childId = data.otherNode.data.id;

                      $.ajax({
                        url: Pinax.ajaxUrl + '&controllerName=metafad.archive.controllers.ajax.ModifyTree',
                        type: 'get',
                        action: 'modify',
                        dataType : 'json',
                        data: {
                          id: childId,
                          parentId: parentId
                        },
                        success: function (data, textStatus, jqXHR) {
                          if(data.status === false)
                          {
                            alert('Attenzione: operazione non permessa. Controllare i livelli di descrizione dei nodi con i quali si sta operando.');
                          }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                          alert('Attenzione: operazione non permessa. Controllare i livelli di descrizione dei nodi con i quali si sta operando.');
                        }
                      })
                    });

                    if (node.children && node.children.length > 0) {
                        node.folder = true;
                    }
                    else {
                        node.folder = false;
                    }
                    node.renderStatus();
                });
            }
        },
        click: function (event,data)
        {
          if(data.targetType == 'title' && data.node.data.url !== undefined )
          {
            window.location.href = data.node.data.url;
          }
          else if(data.node.data.url === undefined)
          {
            window.location.href = $(".gerarchia").fancytree("getActiveNode").data.url;
          }
        },
        dblclick: function (event, data) {
            data.node.setExpanded(false);
        },
        source:
          $.ajax({
            url: Pinax.ajaxUrl + '&controllerName=metafad.archive.controllers.ajax.GetTree',
            type: 'get',
            dataType : 'json',
            data: {
              id: id
            }
          })
        ,
        lazyLoad: function (event, data) {
            var node = data.node;
            data.result =
              $.ajax({
                url: Pinax.ajaxUrl + '&controllerName=metafad.archive.controllers.ajax.GetTreeFromParent',
                type: 'get',
                dataType : 'json',
                data: {
                  id : node.data.id
                }
              })
            ;
        },
        renderNode: function (event, data) {
            var node = data.node;
        },
        removeNode: function (event, data) {
          //TODO? non so se necessario
        }
    });
}

$(document).ready(function () {
    treeGen();
});

$( document ).ajaxComplete(function( event, xhr, settings ) {
  var action = settings.data;
  var settings = settings.url;

  if(settings.includes('GetTree&'))
  {
    var node = $(".gerarchia").fancytree("getActiveNode");
    $('.fancytree-container').scrollTop( node.span.offsetTop );
    $('.fancytree-title').first().addClass('font-size-fix');
  }

  if(action === undefined)
  {
    action = settings.action;
  }
  if(action !== undefined)
  {
    if(action.indexOf("action=saveDraft&") == 0 || action.indexOf("action=save&") == 0 || action == 'delete' || action == 'modify')
    {
      var id = $('#id').val();
      var source = $.ajax({
        url: Pinax.ajaxUrl + '&controllerName=metafad.archive.controllers.ajax.GetTree',
        type: 'get',
        dataType : 'json',
        data: {
          id: id
        }
      });

      var tree = $(".gerarchia").fancytree('getTree');
      tree.reload(source);
    }
  }
});
