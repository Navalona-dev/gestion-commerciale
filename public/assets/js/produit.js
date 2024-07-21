function addPanier(elt, idAffaire) {
    ///alert("ici");
    //return false;
    $(".loadBody").css('display', 'block');

    var idProduit = $(elt).parent('td').parent('tr').find("input[name='idProduit']").val();

    var qtt = $(elt).parent('td').parent('tr').find("input[name='qttProduit']").val();
    
    var typeVente =  $(elt).parent('td').parent('tr').find("select[name='typeVente']").val();;
    //var prixHt = $(elt).parent('td').parent('tr').find("input[name='prixHt']").val();

    //var prixTTC = $(elt).parent('td').parent('tr').find("input[name='prixTTC']").val();
    if (qtt === "") {
        alert("Le champ qtt ne doit pas être vide !!");
        return false;
    }
    console.log(typeVente);
    $.ajax({
        type: 'post',
        url: '/admin/product/add-to-affaire',
        data: {idProduit: idProduit, qtt: qtt, idAffaire: idAffaire, typeVente: typeVente},
        success: function (response) {
            $(elt).parent('td').parent('tr').css('background-color', 'aquamarine');

            $("#financiereProduct").empty();
            $("#financiereProduct").replaceWith(response);
            
            $(".loadBody").css('display', 'none');

            return false;

        },
        error: function () {
            $(".loadBody").css('display', 'none');
            $(".chargementError").css('display', 'block');
            //alert('Erreur insertion');
        }
    });

    return false;
}

function updateLigneProduct(elt, idProduit, idAffaire) {
    $(".loadBody").css('display', 'block');
    //$("#financiereProductTab tbody").sortable("destroy");
    $.ajax({
        type: 'post',
        url: '/admin/product/financiere/edit_produit',
        data: {idAffaire: idAffaire, type: 'affaire', idProduit: idProduit} ,
        success: function (response) {
            $("#tr_produit_"+idProduit).replaceWith(response);

            /*$("#financiereProductTab tbody").sortable({

            }).disableSelection();

            $("#financiereProductTab tbody").sortable("destroy");*/
            

            $(".loadBody").css('display', 'none');

            return false;
        }
    });

    return false;
}

function editLigneProduct(elt, idAffaire, idProduit, position = null) {

    $("#qtt").css('border', '1px solid #e5e6e7');

    var qtt = $("#qtt").val();
    if (qtt === "" || qtt < 0) {
        $("#qtt").css('border', '1px solid red');
        return false;
    }
    $(".loadBody").css('display', 'block');
    $.ajax({
        type: 'post',
        url: '/admin/product/financiere/save/edit_produit',
        data: {idAffaire: idAffaire, idProduit: idProduit, qtt: qtt},
        success: function (response) {
            //reloadTabFinanciere(response);
            $("#financiereProduct").empty();
            $("#financiereProduct").replaceWith(response);
            //updatePositionBdd()
            $(".loadBody").css('display', 'none');
        },
        error: function () {
            $(".loadBody").css('display', 'none');
            $(".chargementError").css('display', 'block');

        }
    });

    /*$("#financiereProductTab tbody").sortable({
        helper: fixHelperModifiedTabFinanciere,
        stop: updateIndexFinanciere
    }).disableSelection();*/

    return false;
}

function deleteProduitAffaire(elt, idProduit, idAffaire) {
    if (confirm("Voulez vous vraiment supprimer ce produit")) {
        $(".loadBody").css("display", "block");
        $.ajax({
            type: 'post',
            url: '/admin/product/financiere/delete-produit',
            data: {idProduit: idProduit, idAffaire: idAffaire },
            success: function (response) {
                //$(elt).parent('td').parent('tr').remove();

                $("#financiereProduct").empty();
                $("#financiereProduct").replaceWith(response);

                $(".loadBody").css("display", "none");
            },
            error: function () {
                $(".loadBody").css('display', 'none');
                $(".chargementError").css('display', 'block');
                //alert("Error lors de la suppression");
            }
        });
    }


    return false;
}