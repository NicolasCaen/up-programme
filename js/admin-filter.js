jQuery(document).ready(function($) {
    const filterForm = $('.wp-list-table').closest('form');
    
    // Intercepter le clic sur le bouton Filtrer
    $('#post-query-submit').on('click', function(e) {
        e.preventDefault(); // Empêcher la soumission normale du formulaire
        updateResults();
    });

    function updateResults() {
        // Créer un objet avec les données du formulaire
        const data = {
            action: 'update_admin_filter',
            nonce: adminFilterAjax.nonce
        };

        // Ajouter les valeurs des filtres
        $('.taxonomy-filter').each(function() {
            data[$(this).attr('name')] = $(this).val();
        });

        $.ajax({
            url: adminFilterAjax.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('.wp-list-table tbody').html(response.data.html);
                    $('.tablenav .displaying-num').text(response.data.total + ' éléments');
                }
            }
        });
    }
}); 