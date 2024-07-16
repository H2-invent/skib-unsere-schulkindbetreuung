import $ from "jquery";

 function addFormToCollection($collectionHolderClass) {
    var $collectionHolder = $('.' + $collectionHolderClass);
    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');
    var newForm = prototype;
    newForm = newForm.replace(/__name__/g, index);
    $collectionHolder.data('index', index + 1);

    let  $newFormLi = $('<div class="card card-body mb-3"></div>').append(newForm);
    $collectionHolder.append($newFormLi);
    addTagFormDeleteLink($newFormLi);
    $newFormLi.find('.pickadate').pickadate({
         format: 'dd.mm.yyyy',
         formatSubmit: 'yyyy-mm-dd',
         selectYears: 2010,
         min: '01.01.2000',
         max: new Date(),
     });
}

function addTagFormDeleteLink($tagFormLi) {
    var $removeFormButton = $('<a href="#" class="removeFromForm" type="remove-group"><i class="text-danger px-1 fa fa-trash" data-toggle="tooltip" title="Löschen" data-original-title="Löschen"></i></a>');
    $tagFormLi.prepend($removeFormButton);
    $removeFormButton.on('click', function(e) {
        e.preventDefault();
        $tagFormLi.remove();
    });
}
function initKeycloakGroups(){
      $('.add_item_link').off('click');

    $('.add_item_link').each(function() {
        var perber = $('.'+$(this).data('collectionHolderClass')).find('div').length+2;
        $(this).text($(this).text().replace(/[0-9]/gm,perber))
        var $groupsCollectionHolder = $('.'+$(this).data('collectionHolderClass'));
        $groupsCollectionHolder.find('.card').each(function() {
            addTagFormDeleteLink($(this));
        });
        $(this).on('click', function (e) {
            e.preventDefault();
            var $collectionHolderClass = $(e.currentTarget).data('collectionHolderClass');
            addFormToCollection($collectionHolderClass);
            var perber = $('.'+$(this).data('collectionHolderClass')).find('.card').length+2;
            $(this).text($(this).text().replace(/[0-9]/gm,perber))
        })
    })
    const deleteBtn = document.querySelectorAll('.removeFromForm');
    deleteBtn.forEach(ele => {
        // Elternknoten des Elements abrufen (dies ist das div mit der Klasse 'card')
        const cardDiv = ele.closest('.card');

        // Hinzufügen des Klickereignisses zum Löschen des Elternelements
        ele.addEventListener('click', () => {
            if (cardDiv) {
                cardDiv.remove();
            }
        });
    });
}
export {initKeycloakGroups};
