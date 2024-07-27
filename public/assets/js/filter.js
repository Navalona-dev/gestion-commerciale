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