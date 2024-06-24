$(document).ready(function() {
    // Vérifiez s'il y a un élément avec la classe 'typed-text-output'
    if ($('.typed-text-output').length == 1) {
        // Récupérez le texte à taper
        var typed_strings = $('.typed-text').text();
        
        // Initialisation de Typed.js
        var typed = new Typed('.typed-text-output', {
            strings: typed_strings.split(', '), 
            typeSpeed: 100,
            backSpeed: 20,
            smartBackspace: false,
            loop: true
        });
    }
});

$(document).ready(function() {
    $('.nav-item').click(function() {
        // Supprimer la classe active de tous les éléments de menu
        $('.nav-item').removeClass('active');

        // Ajouter la classe active à l'élément de menu sur lequel l'utilisateur a cliqué
        $(this).addClass('active');
    });
});

$(document).ready(function() {
    $('.mobile-nav-toggle').click(function() {
        $('#navbarCollapse').toggleClass('show');
        
        if ($('#navbarCollapse').hasClass('show')) {
            $('.navbar').removeClass('header');
        } else {
            $('.navbar').addClass('header');
        }

        $(this).toggleClass('bi-list bi-x');
    });
});

$(document).ready(function() {
    $('.menu-link').click(function() {
        $('.menu-link').removeClass('active');
        $(this).addClass('active');

        var categoryId = $(this).data('category');
        $('.product-item').hide();

        if (categoryId === 'all') {
            $('.product-item').show();
        } else {
            $('.product-item[data-category="' + categoryId + '"]').show();
        }
    });
});

$(document).ready(function() {
    $('#dropdownMenuLink').click(function(event) {
        event.preventDefault(); // Empêche le comportement par défaut du lien

        // Toggle the 'show' class on the dropdown menu
        $(this).next('.dropdown-menu').toggleClass('show');
        
        // Toggle the aria-expanded attribute
        var isExpanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', !isExpanded);
    });

    // Optionally, close the dropdown when clicking outside of it
    $(document).click(function(event) {
        var $target = $(event.target);
        if (!$target.closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
            $('#dropdownMenuLink').attr('aria-expanded', 'false');
        }
    });
});

/*$(document).ready(function() {
    // Vérifiez si l'URL de la page se termine par "index.html" ou si vous pouvez identifier la classe ou l'ID unique de la page d'accueil
    if (window.location.pathname.endsWith("index.html") || window.location.pathname === "/") {
        // Si la condition est vraie, exécutez le code jQuery uniquement sur la page d'accueil
        $(".navbar").hide();
        $(window).scroll(function() {
            var scroll = $(window).scrollTop();
            if (scroll > 50) {
                $(".navbar").slideDown('slow'); // Animation de glissement vers le bas
            } else {
                $(".navbar").slideUp('slow'); // Animation de glissement vers le haut
            }
        });
    }
});*/


//section about

$(document).ready(function() {
    $(".long-description").hide();
    $(".btn-read-less").hide();

    $(".btn-read-more").click(function() {
        $(".short-description").hide();
        $(".long-description").show();
        $(".btn-read-less").show();
        $(this).hide();
    });

    $(".btn-read-less").click(function() {
        $(".short-description").show();
        $(".long-description").hide();
        $(".btn-read-more").show();
        $(this).hide();

    });
})

//skills

$(document).ready(function() {
    $('.skill').waypoint(function () {
        $('.progress .progress-bar').each(function () {
            $(this).css("width", $(this).attr("aria-valuenow") + '%');
        });
    }, {offset: '80%'});
});

//testimonial

$(document).ready(function() {
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        dots: true,
        loop: true,
        items: 1
    });
})

$(document).ready(function(){
    $('.dropdown').hover(function(){
        $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn(500);
    }, function(){
        $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut(500);
    });
});


