(function(){
    var select = document.getElementById('bhg-results-select');
    if (!select || typeof bhgResults === 'undefined') {
        return;
    }
    select.addEventListener('change', function(){
        var val = this.value.split('-');
        if (val.length < 2) {
            return;
        }
        var type = val[0];
        var id = val[1];
        window.location = bhgResults.base_url + '&type=' + type + '&id=' + id;
    });
})();
