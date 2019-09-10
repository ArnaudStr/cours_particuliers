//création de 3 éléments HTMLElement
var $addButtonCreneau = $('<button type="button" class="add_collection_link">Ajouter un creneau</button>');
var $delButton = $('<button type="button" class="del_collection_link">Supprimer</button>');
//le premier élément li de la liste (celui qui contient le bouton 'ajouter')
var $newLinkLiCreneau = $('<li></li>').append($addButtonCreneau);

function generateDeleteButton($collection){
    var $btn = $delButton.clone();
    $btn.on("click", function(){//événement clic du bouton supprimer
        $(this).parent("li").remove();
        $collection.data('index', $collection.data('index')-1)

    })

    return $btn;
}

//fonction qui ajoute un nouveau champ li (en fonction de l'entry_type du collectionType) dans la collection
function addCreneauForm($creneau, $newLinkLiCreneau) {
    
    //contenu du data attribute prototype qui contient le HTML d'un champ
    var newFormCreneau = $creneau.data('prototype');
    //le nombre de champs déjà présents dans la collection
    var index = $creneau.data('index');
    //on remplace l'emplacement prévu pour l'id d'un champ par son index dans la collection
    newFormCreneau = newFormCreneau.replace(/__name__/g, index);
    //on modifie le data index de la collection par le nouveau nombre d'éléments
    $creneau.data('index', index+1);

    //on construit l'élément li avec le champ et le bouton supprimer
    var $newFormLiCreneau = $('<li></li>').append(newFormCreneau).append(generateDeleteButton($creneau));
    //on ajoute la nouvelle li au dessus de celle qui contient le bouton "ajouter"
    $newLinkLiCreneau.before($newFormLiCreneau);
}


//rendu de la collection au chargement de la page
$(document).ready(function() {

    //on pointe la liste complete (le conteneur de la collection)
    var $creneaux = $("ul#creneaux")
    var $stagli = $("ul#creneaux li")

     //pour chaque li déjà présente dans la collection (dans le cas d'une modification)
    $stagli.each(function(){
        //on génère et ajoute un bouton "supprimer"
        $(this).append(generateDeleteButton($creneaux));
    })
    //on y ajoute le bouton ajouter (à la fin du contenu)
    $creneaux.append($newLinkLiCreneau);
    //le data index de la collection est égal au nombre de input à l'intérieur
    $creneaux.data('index', $stagli.length);

    $addButtonCreneau.on('click', function() { // au clic sur le bouton ajouter

        //on appelle la fonction qui ajoute un nouveau champ
        addCreneauForm($creneaux, $newLinkLiCreneau);
    });

});