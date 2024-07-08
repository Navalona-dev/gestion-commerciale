// JavaScript

function newCompte(isNew = false, genre = 1) {
    $.ajax({
        url: '/admin/comptes/new',
        type: 'POST',
        data: {
            isNew: isNew,
            genre: genre 
        },
        success: function (response) {
    
            $("#blocModalCompteEmpty_" + genre).empty();
            $("#blocModalCompteEmpty_" + genre).append(response.html);
            if(genre == 1) {
                $('#modalNewClient').modal('show');
                //showTabCompte(1);
            } else if(genre == 2) {
                $('#modalNewFournisseur').modal('show');
                //showTabCompte(2);
            }

            
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Gérer l'erreur (par exemple, afficher un message d'erreur)
            alert('Erreur lors de l\'ajout de client.');
        }
    });
}


$(document).ready(function() {
    var anchorName = document.location.hash.substring(1);
    if (anchorName === "tab-categorie-permission") {
        showTabCategoriePermission();
    }
    if (anchorName === "tab-permission") {
        showTabPermission();
    }
    if (anchorName === "tab-dashboard") {
        showTabDasboard();
    }
    if (anchorName === "tab-privilege") {
        showTabPrivilege();
    }
    if (anchorName === "tab-utilisateur") {
        showTabUtilisateur();
    }

    if (anchorName === "tab-application") {
        showTabApplication();
    }

    if (anchorName === "tab-profile") {
        showTabProfile();
    }
    if (anchorName === "tab-categorie") {
        showTabCategorie();
    }
    if (anchorName === "tab-produit-categorie") {
        showTabProduitCategorie();
    }

    var anchorName = document.location.hash.substring(1);
    if (anchorName === "tab-compte_1") {
        showTabCompte(1);
    }
    if (anchorName === "tab-compte_2") {
        showTabCompte(2);
    }

    if (anchorName === "tab-produit-type") {
        showTabProduitType();
    }

    if (anchorName === "tab-stock") {
        listStockByProduitSession();
    }

    if (anchorName === "tab-produit-image") {
        listImageByProduitSession();
    }
    if (anchorName === "tab-import-produit") {
        showTabImportProduit();
    }

    if (anchorName === "tab-transfert-produit") {
        listTransfertByProduitSession();
    }

    if (anchorName === "affaires") {
        showTabAffaireByCompte(id = null, genre = 1);
    }


});

function showTabAffaireByCompte(id = null, genre = 1) {
    $.ajax({
        type: 'post',
        url: '/admin/affaires/'+id,
        data: {
            id: id,
            genre: genre
        },
        success: function(response) {
            // Show the selected compte tab
            if (genre == 1) {
                $('.compte-title').text("clients");
                $('.option-compte').text('Nom du client');
            } else if (genre == 2) {
                $('.compte-title').text("fournisseurs");
                $('.option-compte').text('Nom du fournisseur');

            }

            $(".loadBody").css('display', 'none');
        },
        error: function() {
            $(".chargementError").css('display', 'block');
        }
    });
}

function openModalUpdatePriceProduit(id = null) {
    var anchorName = document.location.hash.substring(1);

        $.ajax({
                url: '/admin/produit/categorie/edit/prix/'+id,
                type: 'POST',
                data: {id: id},
                success: function (response) {
                    if (response.html != "") {
                        $("#blocModalPriceProduitEmpty").empty();
                        $("#blocModalPriceProduitEmpty").append(response.html);

                        $('#modalUpdatePriceProduct').modal('show');

                        if (anchorName) {
                                window.location.hash = anchorName;
                        }

                    }
                    
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // Gérer l'erreur (par exemple, afficher un message d'erreur)
                    alert('Erreur lors de la mise à jour de prix.');
                }
            });
    }


function listImageByProduitSession() {
    $.ajax({
             type: 'post',
             url: '/admin/produit/image/refresh/produit',
             //data: {},
             success: function (response) {
                 $("#tab-produit-image").empty();
                 $("#tab-produit-image").append(response.html);
                 $("#tab-produit-image").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

function listStockByProduitSession() {
    $.ajax({
             type: 'post',
             url: '/admin/stock/refresh/produit',
             //data: {},
             success: function (response) {
                 $("#tab-stock").empty();
                 $("#tab-stock").append(response.html);
                 $("#tab-stock").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function listTransfertByProduitSession() {
    $.ajax({
             type: 'post',
             url: '/admin/transfert/refresh/produit',
             //data: {},
             success: function (response) {
                 $("#tab-transfert-produit").empty();
                 $("#tab-transfert-produit").append(response.html);
                 $("#tab-transfert-produit").css('display', 'block');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-stock"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

function showTabCompte(genre = 1) {
        
    $.ajax({
        type: 'post',
        url: '/admin/comptes/',
        data: {genre: genre},
        success: function(response) {
            $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-profile"]').removeClass('collapsed');
            $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

            // Hide all compte tabs
            $('[id^="tab-compte_"]').removeClass('active').empty();

            // Show the selected compte tab
            $('#tab-compte_' + genre).append(response.html).addClass('active');

            if (genre == 1) {
                $('.compte-title').text("clients");
                $('.option-compte').text('Nom du client');
            } else if (genre == 2) {
                $('.compte-title').text("fournisseurs");
                $('.option-compte').text('Nom du fournisseur');

            }

            // Handle sidebar navigation
            $('.sidebar-nav a[href^="#tab-compte_"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-compte_' + genre + '"]').removeClass('collapsed').tab('show');

            $(".loadBody").css('display', 'none');
        },
        error: function() {
            $(".chargementError").css('display', 'block');
        }
    });
}

function showTabProfile() {
    $.ajax({
             type: 'post',
             url: '/admin/utilisateurs/profile/user',
             //data: {},
             success: function (response) {
                 $("#tab-profile").empty();
                 $("#tab-profile").append(response.html);
                 $('.sidebar-nav a[href="#tab-profile"]').tab('show');
                 $("#tab-profile").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-profile"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }



function showTabApplication() {
    $.ajax({
             type: 'post',
             url: '/admin/applications/',
             //data: {},
             success: function (response) {
                 $("#tab-application").empty();
                 $("#tab-application").append(response.html);
                 $('.sidebar-nav a[href="#tab-application"]').tab('show');
                 $("#tab-application").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');
                 
                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

function showTabUtilisateur() {
    $.ajax({
             type: 'post',
             url: '/admin/utilisateurs/',
             //data: {},
             success: function (response) {
                 $("#tab-utilisateur").empty();
                 $("#tab-utilisateur").append(response.html);
                 $('.sidebar-nav a[href="#tab-utilisateur"]').tab('show');
                 $("#tab-utilisateur").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

function showTabPrivilege() {
    $.ajax({
             type: 'post',
             url: '/admin/privileges/',
             //data: {},
             success: function (response) {
                 $("#tab-privilege").empty();
                 $("#tab-privilege").append(response.html);
                 $('.sidebar-nav a[href="#tab-privilege"]').tab('show');
                 $("#tab-privilege").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

function showTabPermission() {
    $.ajax({
             type: 'post',
             url: '/admin/permissions/',
             //data: {},
             success: function (response) {
                 $("#tab-permission").empty();
                 $("#tab-permission").append(response.html);
                 $('.sidebar-nav a[href="#tab-permission"]').tab('show');
                 $("#tab-permission").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabCategoriePermission() {
    $.ajax({
             type: 'post',
             url: '/admin/categorypermission/',
             //data: {},
             success: function (response) {
                 $("#tab-categorie-permission").empty();
                 $("#tab-categorie-permission").append(response.html);
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').tab('show');
                 $("#tab-categorie-permission").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabDasboard() {
    $.ajax({
             type: 'post',
             url: '/admin',
             //data: {},
             success: function (response) {
                 $("#tab-dashboard").empty();
                 $("#tab-dashboard").append(response.html);
                 $('.sidebar-nav a[href="#tab-dashboard"]').tab('show');
                 $("#tab-dashboard").addClass('active');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-dashboard"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabCategorie() {
    $.ajax({
             type: 'post',
             url: '/admin/categorie/',
             //data: {},
             success: function (response) {
                 $("#tab-categorie").empty();
                 $("#tab-categorie").append(response.html);
                 $('.sidebar-nav a[href="#tab-categorie"]').tab('show');
                 $("#tab-categorie").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }


 function showTabProduitCategorie() {
    $.ajax({
             type: 'post',
             url: '/admin/produit/categorie/',
             //data: {},
             success: function (response) {
                 $("#tab-produit-categorie").empty();
                 $("#tab-produit-categorie").append(response.html);
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').tab('show');
                 $("#tab-produit-categorie").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');


                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabProduitType() {
    $.ajax({
             type: 'post',
             url: '/admin/produit/type/',
             //data: {},
             success: function (response) {
                 $("#tab-produit-type").empty();
                 $("#tab-produit-type").append(response.html);
                 $('.sidebar-nav a[href="#tab-produit-type"]').tab('show');
                 $("#tab-produit-type").addClass('active');
                 
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 //$('.sidebar-nav a[href="#tab-import-produit"]').addClass('collapsed');
                 $(".tab-import-produit").removeClass('active');
                 $("#tab-import-produit").removeClass('active');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabImportProduit() {
    $.ajax({
             type: 'post',
             url: '/admin/import/produit/',
             //data: {},
             success: function (response) {
                 $("#tab-import-produit").empty();
                 $("#tab-import-produit").append(response.html);
                 //$('.sidebar-nav a[href="#tab-import-produit"]').tab('show');
                 //$("#tab-import-produit").addClass('active');
                 $("#tab-import-produit").css('display', 'block');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-import-produit"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_1"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_2"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');
                
                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 

