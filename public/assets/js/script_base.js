// JavaScript

$(document).ready(function() {
    var anchorName = document.location.hash.substring(1);
    var idAffaire = $('.id-affaire').data('affaire');
    var idCompte = $('.id-compte').data('compte');
    var idProduit = $('.id-produit').data('produit');

    if (anchorName === "affaires_client") {
        showTabAffaireClient();
    }

    if (anchorName === "tab-categorie-permission") {
        showTabCategoriePermission();
    }
    if (anchorName === "tab-permission") {
        showTabPermission();
    }
    if (anchorName === "tab-dashboard") {
        showTabDashboard();
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

   /* if (anchorName === "tab-stock") {
        listStockByProduitSession();
    }*/

    /*if (anchorName === "tab-produit-image") {
        listImageByProduitSession();
    }*/
    if (anchorName === "tab-import-produit") {
        showTabImportProduit();
    }

    if (anchorName === "tab-transfert-produit") {
        //listTransfertByProduitSession();
        listTransfert(idProduit);
    }

    if (anchorName === "tab-financier-affaire") {
        financier(idAffaire);
    }

    if (anchorName === "tab-info-affaire") {
        information(idAffaire);
    }

    if (anchorName === "affaires_fournisseur") {
        listProduitByCompte(idCompte);
    }

    if (anchorName === "tab-fiche-client") {
        ficheClient(idCompte, 1);
    }

    if (anchorName === "tab-fiche-fournisseur") {
        ficheClient(idCompte, 2);
    }

    if (anchorName === "tab-facture") {
        showTabFacture();
    }

    if (anchorName === "tab-facture-affaire") {
        showTabFactureAffaire(idAffaire);
    }

    if (anchorName === "tab-stock") {
        listStock(idProduit);
    }

    if (anchorName === "tab-produit-image") {
        listImage(idProduit);
    }

    if (anchorName === "tab-quantite-vendu") {
        showTabQttVendu(idProduit);
    }

    if (anchorName === "tab-notification") {
        showTabNotification();
    }

    if (anchorName === "tab-historique-produit") {
        showTabHistoriqueProduit();
    }

    if (anchorName === "tab-historique-affaire") {
        showTabHistoriqueAffaire();
    }

});


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

function information(id = null) {
    showSpinner();
    $.ajax({
            type: 'get',
            url: '/admin/affaires/information/'+id,
            data: {id: id},
            success: function (response) {
                $("#tab-info-affaire").empty();
                $("#tab-info-affaire").append(response.html);
                $("#tab-info-affaire").addClass('active');
                $("#tab-info-affaire").css('display', 'block');
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
                $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                $(".loadBody").css('display', 'none');

                // Réinitialiser le DataTable avec un léger retard
            setTimeout(function() {
                hideSpinner();
            }, 2000);
            },
            error: function () {
                // $(".loadBody").css('display', 'none');
                $(".chargementError").css('display', 'block');
                hideSpinner();
            }

        });
}

function listProduitByCompte(id = null) {
    showSpinner();
    
    $.ajax({
            type: 'post',
            url: '/admin/affaires/produit/'+id,
            data: {
                id: id,
            },
            success: function (response) {
              
                $("#affaires_fournisseur").empty();
                $("#affaires_fournisseur").append(response.html);
                $("#affaires_fournisseur").addClass('active');
                $('#tab-dashboard').removeClass('active').empty();
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
                $('.sidebar-nav a[href="#tab-produit-image"]').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                
                $(".loadBody").css('display', 'none');
                // Réinitialiser le DataTable avec un léger retard
                setTimeout(function() {
                    $('#table-produit-fournisseur').DataTable({
                        responsive: true,
                        language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                    },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                    });
                    hideSpinner();
                }, 2000);

            },
            error: function () {
                // $(".loadBody").css('display', 'none');
                $(".chargementError").css('display', 'block');
                hideSpinner();
            }

        });
}
    
function newAffaire(idCompte = null, statut = "devis") {
    var anchorName = document.location.hash.substring(1);
        $.ajax({
            url: '/admin/affaires/new/'+idCompte,
            type: 'POST',
            data: {statut: statut},
            success: function (response) {
                $("#blocModaAffaireEmpty").empty();
                $("#blocModaAffaireEmpty").append(response.html);
                $('#modalNewAffaire').modal('show');
                if (anchorName) {
                    window.location.hash = anchorName;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Gérer l'erreur (par exemple, afficher un message d'erreur)
                alert('Erreur lors de l\'ajout affaire.');
            }
        });
    }

function updateAffaire(id = null) {
    var anchorName = document.location.hash.substring(1);
    $.ajax({
        url: '/admin/affaires/edit/'+id,
        type: 'POST',
        success: function (response) {
            $("#blocModaAffaireEmpty").empty();
            $("#blocModaAffaireEmpty").append(response.html);
            $('#modalUpdateAffaire').modal('show');
            if (anchorName) {
                window.location.hash = anchorName;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Gérer l'erreur (par exemple, afficher un message d'erreur)
            alert('Erreur lors de la mise à jour affaire.');
        }
    });
}

function deleteAffaire(id = null) {
    var anchorName = document.location.hash.substring(1);

    if (confirm('Voulez vous vraiment supprimer cet affaire?')) {
        $.ajax({
            url: '/admin/affaires/delete/'+id,
            type: 'POST',
            data: {category: id},
            success: function (response) {
                var nextLink = $('#sidebar').find('li#affaire').find('a');
                setTimeout(function () {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 1000
                    };
                    toastr.success('Avec succèss', 'Suppression effectuée');

                    //$(".loadBody").css('display', 'none');

                }, 800);
                if (anchorName) {
                    window.location.hash = anchorName;
                }
                    showTabAffaireClient();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Gérer l'erreur (par exemple, afficher un message d'erreur)
                alert('Erreur lors de la suppression d\'une affaire.');
            }
        });
    }
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function listTransfertByProduitSession() {
    showSpinner();
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
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');


                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#liste-table-transfert').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
                 hideSpinner();
             }

         });
 }

function showTabCompte(genre = 1) {
    showSpinner();
    
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
            $('.sidebar-nav a[href="#tab-produit-type"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');


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

            setTimeout(function() {
                $('#liste-table-compte').DataTable({
                    responsive: true,
                    language: {
                      url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                  },
                    border: false,
                    scrollX: '100%',
                    pageLength: 10,
                    scrollCollapse: false,
                  });
                hideSpinner();
            }, 2000);
        },
        error: function() {
            $(".chargementError").css('display', 'block');
        }
    });
}

function showTabProfile() {
    showSpinner();
    
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
                 setTimeout(function() {
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

function showTabApplication() {
    showSpinner();

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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');     
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                  // Réinitialiser le DataTable avec un léger retard
                setTimeout(function() {
                    $('#liste-table-application').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
                 hideSpinner();
             }

         });
 }

function showTabUtilisateur() {
    showSpinner();
    
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#liste-table-utilisateur').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

function showTabPrivilege() {
    showSpinner();
    
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#liste-table-privilege').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabPermission() {
    showSpinner();
    $.ajax({
        type: 'post',
        url: '/admin/permissions/',
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
            $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');


            // Réinitialiser le DataTable avec un léger retard
            setTimeout(function() {
                $('#liste-table-permission').DataTable({
                    responsive: true,
                    language: {
                      url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                  },
                    border: false,
                    scrollX: '100%',
                    pageLength: 10,
                    scrollCollapse: false,
                  });
                hideSpinner();
            }, 2000);
        },
        error: function () {
            $(".chargementError").css('display', 'block');
            hideSpinner();
        }
    });
}


 function showTabCategoriePermission() {
    showSpinner();
    
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#liste-table-category-permission').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }
 

 function showTabAffaireClient() {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/affaires/refresh',
             //data: {},
             success: function (response) {
                //if(genre == 1){
                $("#tab-dashboard").removeClass('active');
                $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                $("#affaires_client").empty();
                $("#affaires_client").append(response.html);
                $("#affaires_client").addClass('active');
               /* } else if(genre == 2) {
                    $("#affaires_fournisseur").empty();
                    $("#affaires_fournisseur").append(response.html);
                    $("#affaires_fournisseur").addClass('active');
                }*/
              
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
                $('.sidebar-nav a[href="#tab-produit-image"]').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                
                $(".loadBody").css('display', 'none');
                setTimeout(function() {
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
                 hideSpinner();
             }

         });
 }

 function showTabDashboard() {
    var anchorName = document.location.hash.substring(1);
    //showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin',
             //data: { anchorName: anchorName },
             success: function (response) {
                 $("#tab-dashboard").empty();
                 $("#tab-dashboard").append(response.html);
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
                 setTimeout(function() {
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
                hideSpinner();
             }

         });
 }


 function showTabCategorie() {
    showSpinner();
    
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#liste-table-categorie').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
                 hideSpinner();
             }

         });
 }


 function showTabProduitCategorie() {
    showSpinner();
    
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');


                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#liste-table-produi-categorie').DataTable({
                        responsive: true,
                        language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                    },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                    });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabProduitType() {
    showSpinner();
    
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
                 setTimeout(function() {
                    $('#liste-table-type').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);

             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabImportProduit() {
    showSpinner();
    
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');    
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    hideSpinner();
                }, 2000);

             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }


function financier(id = null) {
    showSpinner();
    
    $.ajax({
            type: 'get',
            url: '/admin/affaires/financier/'+id,
            //data: {},
            success: function (response) {
                $("#tab-financier-affaire").empty();
                $("#tab-financier-affaire").append(response.html);
                $("#tab-financier-affaire").addClass('active');
                $("#tab-financier-affaire").css('display', 'block');
                $("#tab-info-affaire").css('display', 'none');
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
                $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                $(".loadBody").css('display', 'none');
                setTimeout(function() {
                    hideSpinner();
                }, 2000);
            },
            error: function () {
                // $(".loadBody").css('display', 'none');
                $(".chargementError").css('display', 'block');
                hideSpinner();
            }

        });
}

function ficheClient(id = null, genre = 1) {
    showSpinner();
    
    $.ajax({
            type: 'get',
            url: '/admin/affaires/fiche/'+id,
            //data: {},
            success: function (response) {
                if(genre == 1) {
                    $("#tab-fiche-client").empty();
                    $("#tab-fiche-client").append(response.html);
                    $("#tab-fiche-client").addClass('active');
                } else if(genre == 2) {
                    $("#tab-fiche-fournisseur").empty();
                    $("#tab-fiche-fournisseur").append(response.html);
                    $("#tab-fiche-fournisseur").addClass('active');
                }
               
                $("#tab-info-affaire").css('display', 'none');
             
                
                console.log("fice");
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
                $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                $("#entete-affaire").addClass('d-none');
               
                $(".loadBody").css('display', 'none');
                $(".loadBody").css('display', 'none');
                setTimeout(function() {
                    hideSpinner();
                }, 2000);
            },
            error: function () {
                // $(".loadBody").css('display', 'none');
                $(".chargementError").css('display', 'block');
                hideSpinner();
            }

        });
}

function listAffaireByCompte(id = null, genre = 1) {
    //var anchorName = document.location.hash.substring(1);
    showSpinner();
    
    $.ajax({
            type: 'post',
            url: '/admin/affaires/'+id,
            data: {
                id: id,
                genre: genre
            },
            success: function (response) {
                if(genre == 1){
                    $("#affaires_client").empty();
                    $("#affaires_client").append(response.html);
                    $("#affaires_client").addClass('active');
                } else if(genre == 2) {
                    $("#affaires_fournisseur").empty();
                    $("#affaires_fournisseur").append(response.html);
                    $("#affaires_fournisseur").addClass('active');
                }
               
                $("#tab-financier-affaire").css('display', 'none');
                $("#fiche-client").css('display', 'none');
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
                $('.sidebar-nav a[href="#tab-produit-image"]').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                
                $(".loadBody").css('display', 'none');

                $(".loadBody").css('display', 'none');
                setTimeout(function() {
                    hideSpinner();
                }, 2000);

            },
            error: function () {
                // $(".loadBody").css('display', 'none');
                $(".chargementError").css('display', 'block');
                hideSpinner();
            }

        });
}

function showTabFacture() {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/facture/',
             //data: {},
             success: function (response) {
                 $("#tab-facture").empty();
                 $("#tab-facture").append(response.html);
                 $('.sidebar-nav a[href="#tab-facture"]').tab('show');
                 $("#tab-facture").addClass('active');
                 $('.sidebar-nav a[href="#tab-facture"]').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
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
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');


                 $(".loadBody").css('display', 'none');
                 setTimeout(function() {
                    $('.table-facture').DataTable({
                        responsive: true,
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                        },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                    });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabFactureAffaire(id = null) {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/affaires/facture/'+id,
             //data: {},
             success: function (response) {
                 $("#tab-facture-affaire").empty();
                 $("#tab-facture-affaire").append(response.html);
                 $('.sidebar-nav a[href="#tab-facture-affaire"]').tab('show');
                 $("#tab-facture-affaire").addClass('active');
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');


                 $(".loadBody").css('display', 'none');
                 setTimeout(function() {
                    $('#table-facture-affaire').DataTable({
                        responsive: true,
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                        },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                    });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
                 hideSpinner();
             }

         });
 }
 

 function listStock(id = null) {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/stock/'+id,
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
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    var table = $('#liste-table-stock').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function updateIsView(id = null) {
    $.ajax({
             type: 'post',
             url: '/admin/notification/update-is-view/'+id,
             //data: {},
             success: function (response) {
               
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function handleNotificationClick(idProduit = null, idNotification = null) {
    updateIsView(idNotification);
    listStock(idProduit).reload();
}


function listImage(id = null) {
    showSpinner();
    
    $.ajax({
            type: 'post',
            url: '/admin/produit/image/'+id,
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
                $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                $(".loadBody").css('display', 'none');

                setTimeout(function() {
                    $('#liste-table-produit-image').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
                
            },
            error: function () {
                // $(".loadBody").css('display', 'none');
                $(".chargementError").css('display', 'block');
            }

        });
}

function listTransfert(id = null) {
    showSpinner();
    $.ajax({
             type: 'post',
             url: '/admin/transfert/'+id,
             //data: {},
             success: function (response) {
                 $("#tab-transfert-produit").empty();
                 $("#tab-transfert-produit").append(response.html);
                 $("#tab-transfert-produit").addClass('active');
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
                 $('.sidebar-nav a[href="#tab-stock"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#liste-table-transfert').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
                 hideSpinner();
             }

         });
 }

 function showTabQttVendu(id = null) {
    showSpinner();
    $.ajax({
             type: 'post',
             url: '/admin/produit/categorie/quantite/vendu/'+id,
             //data: {},
             success: function (response) {
                 $("#tab-quantite-vendu").empty();
                 $("#tab-quantite-vendu").append(response.html);
                 $("#tab-quantite-vendu").addClass('active');
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
                 $('.sidebar-nav a[href="#tab-stock"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-transfert-produit"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#liste-table-qtt-vendu').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                      });
                    hideSpinner();
                }, 2000);
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
                 hideSpinner();
             }

         });
 }

 function openModalNewStockByNotification(id = null) {
    var anchorName = document.location.hash.substring(1);
    
    $.ajax({
        url: '/admin/notification/new/stock/'+id,
        type: 'POST',
        //data: {isNew: isNew},
        success: function (response) {
            $("#blocModalNotificationStockEmpty").empty();
            $("#blocModalNotificationStockEmpty").append(response.html);
            $('#modalNotificationNewStock').modal('show');
            if (anchorName) {
                window.location.hash = anchorName;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Gérer l'erreur (par exemple, afficher un message d'erreur)
            alert('Erreur lors de l\'ajout de stock.');
        }
    });
}

function showTabNotification() {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/notification/',
             //data: {},
             success: function (response) {
                 $("#tab-notification").empty();
                 $("#tab-notification").append(response.html);
                 $("#tab-notification").css('display', 'block');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-notification"]').removeClass('collapsed');
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');   
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    hideSpinner();
                }, 2000);

             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabHistoriqueProduit() {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/historique/produit',
             //data: {},
             success: function (response) {
                 $("#tab-historique-produit").empty();
                 $("#tab-historique-produit").append(response.html);
                 $("#tab-historique-produit").css('display', 'block');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').addClass('active');
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');                
                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#table-historique-produit').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                        order: [[0, 'desc']],
                      });
                    hideSpinner();
                }, 2000);

             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }

 function showTabHistoriqueAffaire() {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/historique/affaire',
             //data: {},
             success: function (response) {
                 $("#tab-historique-affaire").empty();
                 $("#tab-historique-affaire").append(response.html);
                 $("#tab-historique-affaire").css('display', 'block');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-historique-affaire"]').addClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');
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
                 $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');                
                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    $('#table-historique-affaire').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                        order: [[0, 'desc']],
                      });
                    hideSpinner();
                }, 2000);

             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }


 function showSpinner() {
    document.getElementById('spinner').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
  }
  
  function hideSpinner() {
    document.getElementById('spinner').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
  }

