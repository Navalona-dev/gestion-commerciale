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
  $(document).ready(function() {
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
  });

  //filter stock restant
  $(document).ready(function() {
    function updateStocks(period, text) {
        var count = $('#count-stock-restant-' + period).text();
        $('#period-text-stock-restant').text('| ' + text);
        $('#stock-restant-count').text(count);
    }
  
    $('.stock-restant-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateStocks(period, text);
    });
  
    // Initial load for today
    updateStocks('today', "Aujourd'hui");
  });

  //filter stock vendu
  $(document).ready(function() {
    function updateStocks(period, text) {
        var count = $('#count-stock-vendu-' + period).text();
        $('#period-text-stock-vendu').text('| ' + text);
        $('#stock-vendu-count').text(count);
    }
  
    $('.stock-vendu-filter').on('click', function() {
        var period = $(this).data('period');
        var text = $(this).text();
        updateStocks(period, text);
    });
  
    // Initial load for today
    updateStocks('today', "Aujourd'hui");
  });