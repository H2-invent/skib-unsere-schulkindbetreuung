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

}

function addTagFormDeleteLink($tagFormLi) {
    var $removeFormButton = $('<a href="#" class="deleteKeyCloakGroup" type="remove-group"><i class="text-danger px-1 fas fa-trash" data-toggle="tooltip" title="Löschen" data-original-title="Löschen"></i></a>');
    $tagFormLi.prepend($removeFormButton);
    $removeFormButton.on('click', function(e) {
        $tagFormLi.remove();
    });
}
function initKeycloakGroups(){
      $('#add_item_link').off('click')
    var $groupsCollectionHolder = $('ul.keycloakGroups');
    $groupsCollectionHolder.find('li').each(function() {
        addTagFormDeleteLink($(this));
    });

    $groupsCollectionHolder.data('index', $groupsCollectionHolder.find('input').length);

    $('#add_item_link').on('click', function(e) {
        console.log('1.324');
        var $collectionHolderClass = $(e.currentTarget).data('collectionHolderClass');
        addFormToCollection($collectionHolderClass);
    })
};
export {initKeycloakGroups};
