//filter commande
$(document).ready(function() {
    function updateOrders(period, text) {
        var count = $('#count-' + period).text();
        $('#period-text').text('| ' + text);
        $('#order-count').text(count);
    }
  
    $('.order-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateOrders(period, text);
    });
  
    // Initial load for today
    updateOrders('today', "Aujourd'hui");
  });

  //filter produit
  $(document).ready(function() {
    function updateProduits(period, text) {
        var count = $('#count-produit-' + period).text();
        $('#period-text-produit').text('| ' + text);
        $('#produit-count').text(count);
    }
  
    $('.produit-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateProduits(period, text);
    });
  
    // Initial load for today
    updateProduits('today', "Aujourd'hui");
  });

  //filter stock
  /*$(document).ready(function() {
    function updateStocks(period, text) {
        var count = $('#count-stock-' + period).text();
        $('#period-text-stock').text('| ' + text);
        $('#stock-count').text(count);
    }
  
    $('.stock-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateStocks(period, text);
    });
  
    // Initial load for today
    updateStocks('today', "Aujourd'hui");
  });*/

  //filter stock restant
  /*$(document).ready(function() {
    function updateStocksRestant(period, text) {
        var count = $('#count-stock-restant-' + period).text();
        $('#period-text-stock-restant').text('| ' + text);
        $('#stock-restant-count').text(count);
    }
  
    $('.stock-restant-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateStocksRestant(period, text);
    });
  
    // Initial load for today
    updateStocksRestant('today', "Aujourd'hui");
  });*/

  //filter stock vendu
  $(document).ready(function() {
    function updateStocksVendu(period, text) {
        var count = $('#count-stock-vendu-' + period).text();
        $('#period-text-stock-vendu').text('| ' + text);
        $('#stock-vendu-count').text(count);
    }
  
    $('.stock-vendu-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateStocksVendu(period, text);
    });
  
    // Initial load for today
    updateStocksVendu('today', "Aujourd'hui");
  });

  //filter client
  $(document).ready(function() {
    function updateClients(period, text) {
        var count = $('#count-client-' + period).text();
        $('#period-text-client').text('| ' + text);
        $('#client-count').text(count);
    }
  
    $('.client-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateClients(period, text);
    });
  
    // Initial load for today
    updateClients('today', "Aujourd'hui");
  });

  //filter fournisseur
  $(document).ready(function() {
    function updateFournisseurs(period, text) {
        var count = $('#count-fournisseur-' + period).text();
        $('#period-text-fournisseur').text('| ' + text);
        $('#fournisseur-count').text(count);
    }
  
    $('.fournisseur-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateFournisseurs(period, text);
    });
  
    // Initial load for today
    updateFournisseurs('today', "Aujourd'hui");
  });

  //filter transfert
  $(document).ready(function() {
    function updateTransferts(period, text) {
        var count = $('#count-transfert-' + period).text();
        $('#period-text-transfert').text('| ' + text);
        $('#transfert-count').text(count);
    }
  
    $('.transfert-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateTransferts(period, text);
    });
  
    // Initial load for today
    updateTransferts('today', "Aujourd'hui");
  });

  //best order filter

$(document).ready(function() {
    $('#best-order-table-yesterday').hide();
    $('#best-order-table-this-week').hide();
    $('#best-order-table-last-week').hide();
    $('#best-order-table-this-month').hide();
    $('#best-order-table-last-month').hide();
    $('#best-order-table-this-year').hide();
    $('#best-order-table-last-year').hide();
  
    $('#best-order-filter-today').click(function() {
      var text = $(this).text();
      $('.best-order-filter-text').text(text);
      $('#best-order-table-today').show();
      $('#best-order-table-yesterday').hide();
      $('#best-order-table-this-week').hide();
      $('#best-order-table-last-week').hide();
      $('#best-order-table-this-month').hide();
      $('#best-order-table-last-month').hide();
      $('#best-order-table-this-year').hide();
      $('#best-order-table-last-year').hide();
    });
  
    $('#best-order-filter-yesterday').click(function() {
      var text = $(this).text();
      $('.best-order-filter-text').text(text);
      $('#best-order-table-yesterday').show();
      $('#best-order-table-today').hide();
      $('#best-order-table-this-week').hide();
      $('#best-order-table-last-week').hide();
      $('#best-order-table-this-month').hide();
      $('#best-order-table-last-month').hide();
      $('#best-order-table-this-year').hide();
      $('#best-order-table-last-year').hide();
    });
  
    $('#best-order-filter-this-week').click(function() {
      var text = $(this).text();
      $('.best-order-filter-text').text(text);
      $('#best-order-table-yesterday').hide();
      $('#best-order-table-today').hide();
      $('#best-order-table-this-week').show();
      $('#best-order-table-last-week').hide();
      $('#best-order-table-this-month').hide();
      $('#best-order-table-last-month').hide();
      $('#best-order-table-this-year').hide();
      $('#best-order-table-last-year').hide();
    });
  
    $('#best-order-filter-last-week').click(function() {
      var text = $(this).text();
      $('.best-order-filter-text').text(text);
      $('#best-order-table-yesterday').hide();
      $('#best-order-table-today').hide();
      $('#best-order-table-this-week').hide();
      $('#best-order-table-last-week').show();
      $('#best-order-table-this-month').hide();
      $('#best-order-table-last-month').hide();
      $('#best-order-table-this-year').hide();
      $('#best-order-table-last-year').hide();
    });
  
    $('#best-order-filter-this-month').click(function() {
      var text = $(this).text();
      $('.best-order-filter-text').text(text);
      $('#best-order-table-yesterday').hide();
      $('#best-order-table-today').hide();
      $('#best-order-table-this-week').hide();
      $('#best-order-table-last-week').hide();
      $('#best-order-table-this-month').show();
      $('#best-order-table-last-month').hide();
      $('#best-order-table-this-year').hide();
      $('#best-order-table-last-year').hide();
    });
  
    $('#best-order-filter-last-month').click(function() {
      var text = $(this).text();
      $('.best-order-filter-text').text(text);
      $('#best-order-table-yesterday').hide();
      $('#best-order-table-today').hide();
      $('#best-order-table-this-week').hide();
      $('#best-order-table-last-week').hide();
      $('#best-order-table-this-month').hide();
      $('#best-order-table-last-month').show();
      $('#best-order-table-this-year').hide();
      $('#best-order-table-last-year').hide();
    });
  
    $('#best-order-filter-this-year').click(function() {
      var text = $(this).text();
      $('.best-order-filter-text').text(text);
      $('#best-order-table-yesterday').hide();
      $('#best-order-table-today').hide();
      $('#best-order-table-this-week').hide();
      $('#best-order-table-last-week').hide();
      $('#best-order-table-this-month').hide();
      $('#best-order-table-last-month').hide();
      $('#best-order-table-this-year').show();
      $('#best-order-table-last-year').hide();
    });
  
    $('#best-order-filter-last-year').click(function() {
      var text = $(this).text();
      $('.best-order-filter-text').text(text);
      $('#best-order-table-yesterday').hide();
      $('#best-order-table-today').hide();
      $('#best-order-table-this-week').hide();
      $('#best-order-table-last-week').hide();
      $('#best-order-table-this-month').hide();
      $('#best-order-table-last-month').hide();
      $('#best-order-table-this-year').hide();
      $('#best-order-table-last-year').show();
    });
  
  
  })