// JavaScript

$(document).ready(function() {
    var anchorName = document.location.hash.substring(1);
    var idAffaire = $('.id-affaire').data('affaire');
    var idCompte = $('.id-compte').data('compte');
    var idProduit = $('.id-produit').data('produit');
    var idFacture = $('.id-facture').data('facture');
    var idBenefice = $('.id-benefice').data('benefice');
    var idComptabilite = $('#elementIdCompta').data('comptabilite');

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

    if (anchorName === "tab-compte_1") {
        showTabCompte(1);
    }
    if (anchorName === "tab-compte_2") {
        showTabCompte(2);
    }

    if (anchorName === "tab-produit-type") {
        showTabProduitType();
    }

    if (anchorName === "tab-import-produit") {
        showTabImportProduit();
    }

    if (anchorName === "tab-transfert-produit") {
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

    if (anchorName === "tab-produit-date-peremption") {
        showTabProduitDatePeremption();
    }
    if(anchorName === "tab-inventaire-produit") {
        showInventaire(idProduit);
    }

    if(anchorName === "tab-nouveau-facture") {
        showTabNewFacture(idAffaire);
    }

    if(anchorName === "tab-devis") {
        showTabDevisClient('devis');
    }

    if(anchorName === "tab-commande") {
        showTabCommandeClient('commande');
    }

    if(anchorName === "tab-echeance") {
        showTabEcheance(idFacture);
    }

    if(anchorName === "tab-depot") {
        showTabDepot(idAffaire);
    }

    if(anchorName === "tab-comptabilite") {
        showTabComptabilite();
    }

    if(anchorName === "tab-benefice") {
        showTabBenefice(idBenefice);
    }

    if(anchorName === "tab-vente") {
        showTabVente();
    }

    if(anchorName === "tab-fourchette") {
        showTabFourchette();
    }
    
    if(anchorName == "tab-comptabilite-liste") {
        showTabComptabiliteList();
    }

    if(anchorName == "tab-comptabilite-detail") {
        showTabComptabiliteDetail(idComptabilite);
    }


});

function showTabComptabiliteDetail(id = null) {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/comptabilite/detail/'+id,
             //data: {},
             success: function (response) {
                 $("#tab-comptabilite-detail").empty();
                 $("#tab-comptabilite-detail").append(response.html);
                 $('.sidebar-nav a[href="#tab-comptabilite-detail"]').tab('show');
                 $("#tab-comptabilite-detail").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-comptabilite-detail"]').removeClass('collapsed');
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

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

function showTabComptabiliteList() {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/comptabilite/liste',
             //data: {},
             success: function (response) {
                 $("#tab-comptabilite-liste").empty();
                 $("#tab-comptabilite-liste").append(response.html);
                 $('.sidebar-nav a[href="#tab-comptabilite-liste"]').tab('show');
                 $("#tab-comptabilite-liste").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-comptabilite-liste"]').removeClass('collapsed');
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#table-comptabilite-liste')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#table-comptabilite-liste').DataTable().clear().destroy();
                    }
                    $('#table-comptabilite-liste').DataTable({
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

function newFourchette() {
    var anchorName = document.location.hash.substring(1);

    $.ajax({
        url: '/admin/fourchette/new',
        type: 'POST',
        //data: {isNew: isNew},
        success: function (response) {
            $("#blocModalNewFourchetteEmpty").empty();
            $("#blocModalNewFourchetteEmpty").append(response.html);
            $('#modalNewFourchette').modal('show');
            if (anchorName) {
                window.location.hash = anchorName;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Gérer l'erreur (par exemple, afficher un message d'erreur)
            alert('Erreur lors de l\'ajout de la catégorie.');
        }
    });
}

function newDepense() {
    var anchorName = document.location.hash.substring(1);
          $.ajax({
              url: '/admin/depense/new',
              type: 'POST',
              //data: {isNew: isNew},
              success: function (response) {
                  $("#blocModalDepenseEmpty").empty();
                  $("#blocModalDepenseEmpty").append(response.html);
                  $('#modalNewDepense').modal('show');
                  if (anchorName) {
                      window.location.hash = anchorName;
                  }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  // Gérer l'erreur (par exemple, afficher un message d'erreur)
                  alert('Erreur lors l\'ajout de depense.');
              }
          });
  }

  function updateDepense(id = null) {
    var anchorName = document.location.hash.substring(1);
          $.ajax({
              url: '/admin/depense/edit/'+id,
              type: 'POST',
              //data: {isNew: isNew},
              success: function (response) {
                  $("#blocModalDepenseEmpty").empty();
                  $("#blocModalDepenseEmpty").append(response.html);
                  $('#modalUpdateDepense').modal('show');
                  if (anchorName) {
                      window.location.hash = anchorName;
                  }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  // Gérer l'erreur (par exemple, afficher un message d'erreur)
                  alert('Erreur lors de mise à jour de depense.');
              }
          });
  }
  

  function deleteDepense(id = null) {
      var anchorName = document.location.hash.substring(1);

      if (confirm('Voulez vous vraiment supprimer ce depense?')) {
          $.ajax({
              url: '/admin/depense/delete/'+id,
              type: 'POST',
              //data: {depense: id},
              success: function (response) {
                  var nextLink = $('#sidebar').find('li#depense').find('a');
                  setTimeout(function () {
                      toastr.options = {
                          closeButton: true,
                          progressBar: true,
                          showMethod: 'slideDown',
                          timeOut: 1000
                      };
                      toastr.success('Avec succèss', 'Suppression effectuée');

                      if (anchorName) {
                          window.location.hash = anchorName;
                      }
                      showTabComptabilite();

                  }, 800);
                  
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  // Gérer l'erreur (par exemple, afficher un message d'erreur)
                  alert('Erreur lors de la suppression de depense.');
              }
          });
      }
  }



function showTabFourchette() {
    showSpinner();

    $.ajax({
             type: 'post',
             url: '/admin/fourchette/',
             //data: {},
             success: function (response) {
                 $("#tab-fourchette").empty();
                 $("#tab-fourchette").append(response.html);
                 $('.sidebar-nav a[href="#tab-fourchette"]').tab('show');
                 $("#tab-fourchette").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-fourchette"]').removeClass('collapsed');
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
                    if ($.fn.dataTable.isDataTable('#liste-table-fourchette')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-fourchette').DataTable().clear().destroy();
                    }
                    $('#liste-table-fourchette').DataTable({
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

function showTabVente() {
    showSpinner();

    $.ajax({
             type: 'post',
             url: '/admin/benefice/',
             //data: {},
             success: function (response) {
                 $("#tab-vente").empty();
                 $("#tab-vente").append(response.html);
                 $('.sidebar-nav a[href="#tab-vente"]').tab('show');
                 $("#tab-vente").addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-utilisateur"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-vente"]').removeClass('collapsed');
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
                    if ($.fn.dataTable.isDataTable('#liste-table-vente')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-vente').DataTable().clear().destroy();
                    }
                    $('#liste-table-vente').DataTable({
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

function showTabBenefice(id = null) {
    showSpinner();
    $.ajax({
            type: 'get',
            url: '/admin/benefice/detail/'+id,
            //data: {id: id},
            success: function (response) {
                $("#tab-benefice").empty();
                $("#tab-benefice").append(response.html);
                $("#tab-benefice").addClass('active');
                $("#tab-benefice").css('display', 'block');
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

function showTabComptabilite() {
    showSpinner();
    $.ajax({
            type: 'get',
            url: '/admin/comptabilite',
            //data: {id: id},
            success: function (response) {
                $("#tab-comptabilite").empty();
                $("#tab-comptabilite").append(response.html);
                $("#tab-comptabilite").addClass('active');
                $("#tab-comptabilite").css('display', 'block');
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

function showTabDepot(id = null) {
    showSpinner();
    
    $.ajax({
            type: 'post',
            url: '/admin/affaires/depot/'+id,
            data: {
            },
            success: function (response) {
              
                $("#tab-depot").empty();
                $("#tab-depot").append(response.html);
                $("#tab-depot").addClass('active');
                $('.sidebar-nav a[href="#tab-depot"]').removeClass('collapsed');
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
                $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');

                
                $(".loadBody").css('display', 'none');
                // Réinitialiser le DataTable avec un léger retard
                setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#depot-table')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#depot-table').DataTable().clear().destroy();
                    }
                    $('#depot-table').DataTable({
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

function showTabEcheance(id = null) {
    showSpinner();
    
    $.ajax({
            type: 'post',
            url: '/admin/facture/echeance/liste/'+id,
            data: {
            },
            success: function (response) {
              
                $("#tab-echeance").empty();
                $("#tab-echeance").append(response.html);
                $("#tab-echeance").addClass('active');
                $('.sidebar-nav a[href="#tab-echeance"]').removeClass('collapsed');
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
                $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');

                
                $(".loadBody").css('display', 'none');
                // Réinitialiser le DataTable avec un léger retard
                setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#table-date-echeance')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#table-date-echeance').DataTable().clear().destroy();
                    }
                    $('#table-date-echeance').DataTable({
                        responsive: true,
                        language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                    },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                        order: []
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

function showTabDevisClient(statut = 'devis') {
    showSpinner();
    
    $.ajax({
            type: 'post',
            url: '/admin/affaires/',
            data: {
                statut: statut
            },
            success: function (response) {
              
                $("#tab-devis").empty();
                $("#tab-devis").append(response.html);
                $("#tab-devis").addClass('active');
                $('.sidebar-nav a[href="#tab-devis"]').removeClass('collapsed');
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
                $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');

                
                $(".loadBody").css('display', 'none');
                // Réinitialiser le DataTable avec un léger retard
                setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-devis')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-devis').DataTable().clear().destroy();
                    }
                    $('#liste-table-devis').DataTable({
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

function showTabCommandeClient(statut = 'commande') {
    showSpinner();
    
    $.ajax({
            type: 'post',
            url: '/admin/affaires/',
            data: {
                statut: statut
            },
            success: function (response) {
              
                $("#tab-commande").empty();
                $("#tab-commande").append(response.html);
                $("#tab-commande").addClass('active');
                $('.sidebar-nav a[href="#tab-commande"]').removeClass('collapsed');
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
                 $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
                $('.sidebar-nav #historique a').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-facture"]').addClass('collapsed');

                
                $(".loadBody").css('display', 'none');
                // Réinitialiser le DataTable avec un léger retard
                setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-commande')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-commande').DataTable().clear().destroy();
                    }
                    $('#liste-table-commande').DataTable({
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

function showTabNewFacture(id = null) {
    showSpinner();
    $.ajax({
            type: 'post',
            url: '/admin/facture/new/'+id,
            data: {id: id},
            success: function (response) {
                $("#tab-nouveau-facture").empty();
                $("#tab-nouveau-facture").append(response.html);
                $("#tab-nouveau-facture").addClass('active');
                $("#tab-nouveau-facture").css('display', 'block');
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
                 $('.sidebar-nav a[href="#tab-info-affaire"]').removeClass('active');    
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
                    if ($.fn.dataTable.isDataTable('#table-produit-fournisseur')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#table-produit-fournisseur').DataTable().clear().destroy();
                    }
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
                    if ($.fn.dataTable.isDataTable('#liste-table-transfert')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-transfert').DataTable().clear().destroy();
                    }
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
            $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-historique-affaire"]').removeClass('active');
            $('.sidebar-nav a[href="#tab-historique-produit"]').removeClass('active');    
            $('.sidebar-nav #historique a').addClass('collapsed');


            // Hide all compte tabs
            $('[id^="tab-compte_"]').removeClass('active').empty();

            // Show the selected compte tab
            $('#tab-compte_' + genre).append(response.html).addClass('active');

            $('.sidebar-nav a[href^="#tab-compte_"]').addClass('collapsed');
            $('.sidebar-nav a[href="#tab-compte_' + genre + '"]').removeClass('collapsed').tab('show');

            // Handle sidebar navigation
          
            $(".loadBody").css('display', 'none');


            if (genre == 1) {
                $('.compte-title').text("clients");
                $('.option-compte').text('Nom du client');
            } else if (genre == 2) {
                $('.compte-title').text("fournisseurs");
                $('.option-compte').text('Nom du fournisseur');

            }
            setTimeout(function() {
                if ($.fn.dataTable.isDataTable('#liste-table-compte')) {
                    // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                    $('#liste-table-compte').DataTable().clear().destroy();
                }
            
                $('#liste-table-compte').DataTable({
                    responsive: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                    },
                    border: false,
                    scrollX: '100%',
                    pageLength: 10,
                    scrollCollapse: false,
                    order: [[ 2, 'desc' ]]
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
                    if ($.fn.dataTable.isDataTable('#liste-table-application')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-application').DataTable().clear().destroy();
                    }
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
                    if ($.fn.dataTable.isDataTable('#liste-table-utilisateur')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-utilisateur').DataTable().clear().destroy();
                    }
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
                    if ($.fn.dataTable.isDataTable('#liste-table-privilege')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-privilege').DataTable().clear().destroy();
                    }
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
                if ($.fn.dataTable.isDataTable('#liste-table-permission')) {
                    // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                    $('#liste-table-permission').DataTable().clear().destroy();
                }
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
                    if ($.fn.dataTable.isDataTable('#liste-table-category-permission')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-category-permission').DataTable().clear().destroy();
                    }
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
                 $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-categorie')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-categorie').DataTable().clear().destroy();
                    }
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-produi-categorie')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-produi-categorie').DataTable().clear().destroy();
                    }
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');
                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-type')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-type').DataTable().clear().destroy();
                    }
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
    $('#modalListProduitCategorie').modal('hide');
    $('.modal-backdrop.fade.show').hide();
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');


                 $(".loadBody").css('display', 'none');
                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('.table-facture')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('.table-facture').DataTable().clear().destroy();
                    }
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');


                 $(".loadBody").css('display', 'none');
                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#table-facture-affaire')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#table-facture-affaire').DataTable().clear().destroy();
                    }
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-stock')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-stock').DataTable().clear().destroy();
                    }
                    $('#liste-table-stock').DataTable({
                        responsive: true,
                        language: {
                          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                      },
                        border: false,
                        scrollX: '100%',
                        pageLength: 10,
                        scrollCollapse: false,
                        order: [[4, 'asc']],

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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                $(".loadBody").css('display', 'none');

                setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-produit-image')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-produit-image').DataTable().clear().destroy();
                    }
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-transfert')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-transfert').DataTable().clear().destroy();
                    }
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-qtt-vendu')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-qtt-vendu').DataTable().clear().destroy();
                    }
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

 function showInventaire(id = null) {
    showSpinner();
    $.ajax({
             type: 'post',
             url: '/admin/produit/categorie/inventaire/'+id,
             //data: {},
             success: function (response) {
                 $("#tab-inventaire-produit").empty();
                 $("#tab-inventaire-produit").append(response.html);
                 $("#tab-inventaire-produit").addClass('active');
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-inventaire-produit')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-inventaire-produit').DataTable().clear().destroy();
                    }
                    $('#liste-table-inventaire-produit').DataTable({
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
                $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');

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
                 $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');              
                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#table-historique-produit')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#table-historique-produit').DataTable().clear().destroy();
                    }
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
                 $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');               
                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#table-historique-affaire')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#table-historique-affaire').DataTable().clear().destroy();
                    }
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

 function showTabProduitDatePeremption() {
    showSpinner();
    
    $.ajax({
             type: 'post',
             url: '/admin/produit/categorie/date/peremption/proche',
             //data: {},
             success: function (response) {
                 $("#tab-produit-date-peremption").empty();
                 $("#tab-produit-date-peremption").append(response.html);
                 $("#tab-produit-date-peremption").css('display', 'block');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-date-peremption"]').addClass('active');
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
                 $('.sidebar-nav a[href="#tab-devis"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-commande"]').addClass('collapsed');          
                 $(".loadBody").css('display', 'none');

                 setTimeout(function() {
                    if ($.fn.dataTable.isDataTable('#liste-table-produit-categorie-date-peremption')) {
                        // Si déjà initialisé, détruire puis réinitialiser pour éviter les réinitialisations multiples
                        $('#liste-table-produit-categorie-date-peremption').DataTable().clear().destroy();
                    }
                    $('#liste-table-produit-categorie-date-peremption').DataTable({
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

 function deleteFacture(id = null) {
    var anchorName = document.location.hash.substring(1);
    var idAffaire = $('.id-affaire').data('affaire');
    
    if (confirm('Voulez vous vraiment supprimer cette facture?')) {
        $.ajax({
            url: '/admin/facture/delete/'+id,
            type: 'POST',
            data: {facture: id},
            success: function (response) {
                var nextLink = $('#sidebar').find('li#facture').find('a');
                setTimeout(function () {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 3000
                    };
                    if(response.status == 'success') {
                        toastr.success(response.message);
                    }else {
                        toastr.error(response.message);
                    }

                }, 800);
                if (anchorName) {
                    window.location.hash = anchorName;
                }
                showTabFactureAffaire(idAffaire);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Gérer l'erreur (par exemple, afficher un message d'erreur)
                alert('Erreur lors de la suppression de l\'application.');
            }
        });
    }
}

 function showSpinner() {
    document.getElementById('spinner').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
  }
  
  function hideSpinner() {
    document.getElementById('spinner').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
  }



// Tableau pour stocker les IDs des affaires pour lesquelles une notification a déjà été affichée
const displayedAffaireIds = new Set();
const notifiedAffaireIds = new Set();

let newAffaires = $('#newAffaire').data('affaire');
let countAffaires = $('#countAffaire').data('affaire');

function validerCommande(id = null) {
    if (confirm('Voulez-vous vraiment valider cette commande ?')) {
        $.ajax({
            url: '/admin/affaires/valider/' + id,
            method: 'POST',
            data: { affaireId: id },
            success: function(response) {
                if (response.status === 'success') {
                    // Notifiez le caissier directement ici si besoin
                    notifyCashier(response.affaireId, countAffaires, newAffaires);
                    financier(id);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error validating order:", error);
            }
        });
    }
}

function notifyCashier(affaireId, countAffaires, newAffaires) {
    if (displayedAffaireIds.has(affaireId) || notifiedAffaireIds.has(affaireId)) {
        return; // Si une notification pour cette affaire a déjà été affichée, arrêtez ici
    }

    notifiedAffaireIds.add(affaireId); // Marque l'affaire comme notifiée
    displayedAffaireIds.add(affaireId); // Marque l'affaire comme affichée

    // Mettre à jour le conteneur de notification
    const notificationContainer = $('#new-commande');
    notificationContainer.removeClass('hide-content').addClass('show-content');

    // Mettre à jour le compteur d'affaires
    $('.badge-commande').text(countAffaires).removeClass('badge-animation');
    if (countAffaires > 0) {
        $('.badge-commande').addClass('badge-animation');
    }

    $('#count-commande').text(`Vous avez ${countAffaires} Commandes`);

    // Ajouter la nouvelle affaire à la liste
    const notificationList = notificationContainer.find('.notification-commande');
    const affaire = newAffaires.find(a => a.id === affaireId); // Trouver l'affaire spécifique à notifier

    if (affaire) {
        const totalNotifications = notificationList.children('.notification-item').length;
        
        notificationList.append(`
            <a href="#tab-financier-affaire" onclick="return financier(${affaireId})">
                <li><hr class="dropdown-divider"></li>
                <li class="notification-item item-commande">
                    <i class="bi bi-exclamation-circle text-danger"></i>
                    <div>
                        <h4 class="text-black">Commande validée</h4>
                        <p>${affaire.nom}</p>
                        ${affaire.dateValidation ? `<p>${affaire.dateValidation}</p>` : ''}
                    </div>
                </li>
            </a>
            
        `);

    }
}



function toggleNotification(button) {
    const notification = button.parentElement;
    notification.classList.toggle('collapsed');
    const span = notification.querySelector('span');
    if (notification.classList.contains('collapsed')) {
        span.style.display = 'none'; // Masquer le texte
        button.textContent = 'Show'; // Changer le texte du bouton
    } else {
        span.style.display = 'block'; // Afficher le texte
        button.textContent = 'Hide'; // Changer le texte
    }
}

function removeNotification(button) {
    const notification = button.parentElement;
    notification.remove(); // Supprimer la notification
}

// Vérifier régulièrement les nouvelles commandes
function checkForNewOrders() {
    $.ajax({
        url: '/admin/affaires/verifier/new/affaire',
        method: 'GET',
        success: function(response) {
            const newAffaires = response.affaires; // Récupérer les nouvelles affaires
            const countAffaires = newAffaires.length; // Compte des nouvelles affaires

            newAffaires.forEach(affaire => {
                // Vérifie si l'affaire a déjà été notifiée
                if (!notifiedAffaireIds.has(affaire.id)) {
                    notifyCashier(affaire.id, countAffaires, newAffaires);
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error checking for new orders:", error);
        }
    });
}

// Fonction pour initialiser les notifications
function initNotifications() {
    $.ajax({
        url: '/admin/affaires/verifier/new/affaire',
        method: 'GET',
        success: function(response) {
            const affaires = response.affaires; // Récupérer toutes les affaires
            const countAffaires = affaires.length; // Compte des affaires

            // Mettre à jour le compteur d'affaires
            $('#count-commande').text(`Vous avez ${countAffaires} Commandes`);
            $('.badge-commande').text(countAffaires).removeClass('badge-animation');
            if (countAffaires > 0) {
                $('.badge-commande').addClass('badge-animation');
            }

            affaires.forEach(affaire => {
                // Vérifie si l'affaire a déjà été notifiée
                if (!notifiedAffaireIds.has(affaire.id)) {
                    notifyCashier(affaire.id, countAffaires, affaires);
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error initializing notifications:", error);
        }
    });
}

// Appel initial pour charger les notifications
initNotifications();


// Vérifiez les nouvelles commandes toutes les 3 secondes
setInterval(checkForNewOrders, 3000);

function newMethodePaiement(id = null) {
    var anchorName = document.location.hash.substring(1);
          $.ajax({
              url: '/admin/comptabilite/nouveau/methode/paiement/'+id,
              type: 'GET',
              //data: {isNew: isNew},
              success: function (response) {
                  $("#blockMethodePaiement").empty();
                  $("#blockMethodePaiement").append(response.html);
                  $('#modalNewMethodePaiement').modal('show');
                  if (anchorName) {
                      window.location.hash = anchorName;
                  }
                  showTabComptabilite();
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  // Gérer l'erreur (par exemple, afficher un message d'erreur)
                  alert('Erreur lors de l\'ajout de methode de paiement.');
              }
          });
  }
  

  function detailMethodePaiement(id = null) {
    var anchorName = document.location.hash.substring(1);
          $.ajax({
              url: '/admin/comptabilite/detail/paiement/'+id,
              type: 'GET',
              //data: {isNew: isNew},
              success: function (response) {
                  $("#blocModalDetailPaiementEmpty").empty();
                  $("#blocModalDetailPaiementEmpty").append(response.html);
                  $('#modalDetailPaiement').modal('show');
                  if (anchorName) {
                      window.location.hash = anchorName;
                  }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  // Gérer l'erreur (par exemple, afficher un message d'erreur)
                  alert('Erreur lors de l\'affichage de détail de paiement.');
              }
          });
  }

  function updateMethodePaiement(id = null) {
    var anchorName = document.location.hash.substring(1);
          $.ajax({
              url: '/admin/comptabilite/methode/paiement/edit/'+id,
              type: 'GET',
              //data: {isNew: isNew},
              success: function (response) {
                  $("#blocModalDetailPaiementEmpty").empty();
                  $("#blocModalDetailPaiementEmpty").append(response.html);
                  $('#modalUpdateMethodePaiement').modal('show');
                  if (anchorName) {
                      window.location.hash = anchorName;
                  }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  // Gérer l'erreur (par exemple, afficher un message d'erreur)
                  alert('Erreur lors de la mise à jour de methode de paiement.');
              }
          });
  }
  
  function deleteMethodePaiement(id = null) {
      var anchorName = document.location.hash.substring(1);

      if (confirm('Voulez vous vraiment supprimer cette méthode de paiement?')) {
          $.ajax({
              url: '/admin/comptabilite/methode/paiement/delete/'+id,
              type: 'POST',
              //data: {category: id},
              success: function (response) {
                  
                  var nextLink = $('#sidebar').find('li#comptabilite').find('a');
                  setTimeout(function () {
                      toastr.options = {
                          closeButton: true,
                          progressBar: true,
                          showMethod: 'slideDown',
                          timeOut: 1000
                      };
                      toastr.success('Avec succèss', 'Suppression effectuée');
                      //location.reload();
                      
                      //$(".loadBody").css('display', 'none');
                      if (anchorName) {
                          window.location.hash = anchorName;
                      }

                      showTabComptabilite();

                  }, 800);
                 
                
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  // Gérer l'erreur (par exemple, afficher un message d'erreur)
                  alert('Erreur lors de la suppression de méthode de paiement.');
              }
          });
      }
  }

  function annuleFacture(id = null) {
    if (confirm('Voulez vous vraiment annuler ce paiement?')) {
        setTimeout(function() {
            $.ajax({
            url: '/admin/affaires/paiement/annule/'+id,
            type: 'POST',
            data: {id: id},
            success: function (response) {
                setTimeout(function () {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 1000
                    };
                    toastr.success('Avec succèss', 'Annulation fait');
                    
                    financier(id);

                    if (response.pdfUrl) {
                        window.open(response.pdfUrl, '_blank');
                    }
                }, 800);
                
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Gérer l'erreur (par exemple, afficher un message d'erreur)
                alert('Erreur lors de l\'annulation de paiement.');
            }
        });
        
        }, 500);
       
    }
}



 
  
