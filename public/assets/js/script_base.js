// JavaScript

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

    if (anchorName === "tab-compte_1") {
        showTabCompte(1);
    }

});

function showTabCompte(genre = 1) {
    $.ajax({
             type: 'post',
             url: '/admin/comptes/',
             //data: {},
             success: function (response) {
                 $('#tab-compte_'+genre+'').empty();
                 $('#tab-compte_'+genre+'').append(response.html);
                 $('.sidebar-nav a[href="#tab-compte_'+ genre +' " ]').tab('show');
                 $('#tab-compte_'+genre+'').addClass('active');
                 $('.sidebar-nav a[href="#tab-dashboard"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-compte_'+ genre + ']').removeClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie-permission"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-application"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-privilege"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-categorie"]').addClass('collapsed');
                 $('.sidebar-nav a[href="#tab-produit-categorie"]').addClass('collapsed');
                 
                 $('.tab-compte_1').addClass('active');
                 $('.tab-user').removeClass('active');
                $('.tab-permission').removeClass('active');
                $('.tab-privilege').removeClass('active');
                $('.tab-cat-permission').removeClass('active');
                $('#tab-dashboard').removeClass('active');
                $('#tab-profile').removeClass('active');
                $('#tab-utilisateur').removeClass('active');
                $('#tab-permission').removeClass('active');
                $('#tab-privilege').removeClass('active');
                $('#categorie-permission').removeClass('active');
                $('#tab-application').removeClass('active');
                $('.tab-categorie').removeClass('active');
                $('#tab-produit-categorie').removeClass('active');
                $('.tab-produit-categorie').removeClass('active');
                $('#tab-categorie').removeClass('active');  

                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
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
                 $(".loadBody").css('display', 'none');
             },
             error: function () {
                // $(".loadBody").css('display', 'none');
                 $(".chargementError").css('display', 'block');
             }

         });
 }



