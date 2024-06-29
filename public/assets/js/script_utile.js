// JavaScript

$(document).ready(function() {
    $('.chosen-select').chosen({width: "100%"});
   /* $('.nav-link').click(function(event) {
        var menu = $(this).data('menu'); 

        $.ajax({
            url: '/admin/liste',
            method: 'GET',
            data: { menu: menu },
            success: function(response) {
                $('#main').html(response); 
                // Mettre à jour l'URL avec le paramètre menu
                window.history.pushState({menu: menu}, '', '/admin/liste#' + menu);
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors du chargement de la page:', error);
            }
        });
    });*/
});

//ckeditor
/*document.querySelectorAll('.ckeditor').forEach(editor => {
ClassicEditor.create(editor);
})

//PASSER LES VALEURS SAISIS SUR LE PARAGRAPHE DE CKEDITOR DANS LE VALEUR DE TEXTEAREA
$(document).ready(function() {
const description = $('div.ck.ck-reset.ck-editor.ck-rounded-corners div.ck.ck-editor__main div.ck.ck-content.ck-editor__editable.ck-rounded-corners.ck-editor__editable_inline.ck-blurred p').html();
$('#category_permission_description').val(description);
$('#permission_description').val(description);
$('#privilege_basic_description').val(description);

})*/

$(document).ready(function() {
    // Initialiser CKEditor
    document.querySelectorAll('.ckeditor').forEach(editor => {
        ClassicEditor.create(editor).then(editorInstance => {
            editorInstance.model.document.on('change:data', () => {
                editor.closest('textarea').value = editorInstance.getData();
            });
        }).catch(error => {
            console.error(error);
        });
    });
  
    // Synchroniser le contenu de CKEditor avant la soumission du formulaire
    $('form').on('submit', function() {
        $('.ckeditor').each(function() {
            var editorInstance = $(this).data('ckeditorInstance');
            if (editorInstance) {
                var editorData = editorInstance.getData();
                $(this).closest('textarea').val(editorData);
            }
        });
    });
  });






