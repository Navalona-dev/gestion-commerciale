function addPanier(elt, idAffaire, volumeGros = null, volumeDetail = null) {
    ///alert("ici");
    //return false;
    $(".loadBody").css('display', 'block');

    var idProduit = $(elt).parent('td').parent('tr').find("input[name='idProduit']").val();

    var qtt = $(elt).parent('td').parent('tr').find("input[name='qttProduit']").val();
    var typeVente = 'gros';
    typeVente =  $(elt).parent('td').parent('tr').find("select[name='typeVente']").val();
    var qttRestant = $(elt).parent('td').parent('tr').find("input[name='qttRestant']").val();

    //var prixHt = $(elt).parent('td').parent('tr').find("input[name='prixHt']").val();
    
    //var prixTTC = $(elt).parent('td').parent('tr').find("input[name='prixTTC']").val();
    if (qtt === "") {
        alert("Le champ qtt ne doit pas être vide !!");
        return false;
    }

    var qttTotal =  1;
    if (typeVente.length === 0) {
        typeVente = 'gros';
    }
    if (typeVente == 'gros') {
        qttTotal =  parseFloat(qttRestant);
    } 
    if (typeVente == 'detail' && volumeGros > 0) {
        qttTotal =  parseFloat(qttRestant) * parseFloat(volumeGros);
    } 
    if (typeVente == 'detail' && volumeGros < 1) {
        qttTotal =  parseFloat(qttRestant);
    } 

    if (qttRestant != undefined && qttRestant != "") {
        if (parseFloat(qtt) > parseFloat(qttTotal)) {
            $(elt).parent('td').parent('tr').css('background-color', '#fc8b8b');
            setTimeout(function () {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 3000
                };

                toastr.error("Votre qtt ne doit pas superieur au stock !!");

            }, 800);
            return false;
        }

    }
    $.ajax({
        type: 'post',
        url: '/admin/product/add-to-affaire',
        data: {idProduit: idProduit, qtt: qtt, idAffaire: idAffaire, typeVente: typeVente},
        success: function (response) {
            
            if(response.status == "error") {
                $(elt).parent('td').parent('tr').css('background-color', '#fc8b8b');
                setTimeout(function () {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 3000
                    };
    
                    toastr.error(response.message);
    
                }, 800);
            } else {
                $(elt).parent('td').parent('tr').css('background-color', 'aquamarine');
            }

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
    var anchorName = document.location.hash.substring(1);

    $(".loadBody").css('display', 'block');
    //$("#financiereProductTab tbody").sortable("destroy");
    $.ajax({
        type: 'post',
        url: '/admin/product/financiere/edit_produit',
        data: {idAffaire: idAffaire, type: 'affaire', idProduit: idProduit} ,
        success: function (response) {
            $("#tr_produit_"+idProduit).replaceWith(response);

            $(".loadBody").css('display', 'none');

            if (anchorName) {
                window.location.hash = anchorName;
            }

            return false;
        }
    });

    return false;
}

function editLigneProduct(elt, idAffaire, idProduit, position = null, typeVente = null, volumeGros = null, volumeDetail = null) {
    var anchorName = document.location.hash.substring(1);

    $("#qtt").css('border', '1px solid #e5e6e7');
    
    var qtt = $("#qtt").val();
    var qttRestant = $("#qttRestant").val();
    if (qtt === "" || qtt < 0) {
        $("#qtt").css('border', '1px solid red');
        return false;
    }

    if (qttRestant != undefined && qttRestant != "") {
       
        var qttTotal =  1

        if (typeVente == 'gros') {
            qttTotal =  parseFloat(qttRestant);
        } 
        if (typeVente == 'detail' && volumeGros > 0) {
            qttTotal =  parseFloat(volumeGros);
        } 
        if (typeVente == 'detail' && volumeGros < 1) {
            qttTotal =  parseFloat(qttRestant);
        }

        //return false;

        var message = '';
        if(typeVente == 'detail' && parseFloat(qtt) >= parseFloat(qttTotal)) {
            message = 'Votre qtt ne doit pas dépasser le volume de gros !!';
        } else if(typeVente == 'gros' && parseFloat(qtt) > parseFloat(qttTotal)) {
            message = 'Votre qtt ne doit pas dépasser le stock !!';
        }
        
            if ((typeVente == 'detail' && parseFloat(qtt) >= parseFloat(qttTotal)) || (typeVente == 'gros' && parseFloat(qtt) > parseFloat(qttTotal))) {

                // Alerte si la condition est remplie
                $(elt).parent('td').parent('tr').css('background-color', '#fc8b8b');
                setTimeout(function () {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 3000
                    };

                    toastr.error(message);

                }, 800);
                return false;
            }
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

            if (anchorName) {
                window.location.hash = anchorName;
            }
            financier(idAffaire);

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
    var anchorName = document.location.hash.substring(1);

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

                if (anchorName) {
                    window.location.hash = anchorName;
                }
            financier(idAffaire);

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

function payer(idAffaire = null) {
    $(".loadBody").css("display", "block");
        $.ajax({
            type: 'post',
            url: '/admin/affaires/paiement/'+idAffaire,
            //data: {idProduit: idProduit, idAffaire: idAffaire },
            success: function (response) {
                //$(elt).parent('td').parent('tr').remove();

                //$("#financiereProduct").empty();
                //$("#financiereProduct").replaceWith(response);

                $(".loadBody").css("display", "none");

                if (anchorName) {
                    window.location.hash = anchorName;
                }
            },
            error: function () {
                $(".loadBody").css('display', 'none');
                $(".chargementError").css('display', 'block');
                //alert("Error lors de la suppression");
            }
        });
}