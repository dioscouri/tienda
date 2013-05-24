if (typeof(Tienda) === 'undefined') {
    var Tienda = {};
}

Tienda.setupPaoFilters = function() {
    tiendaJQ('.tienda-paofilter-buttons .go').on('click', function(event){
        event.preventDefault();
        tiendaJQ('#paofilters-form').submit();
    });
    
    tiendaJQ('.tienda-paofilter').on('click', function(event){
        el = tiendaJQ(this);
        event.preventDefault();
        
        name = el.attr('data-name');        
        tiendaJQ(".tienda-paofilter-options-wrapper").hide();
        tiendaJQ(".tienda-paofilter-options-wrapper[data-name='" + name + "']").show();
        
    });

    tiendaJQ('.tienda-paofilter-option').on('click', function(event){
        el = tiendaJQ(this);
        event.preventDefault();
        if (el.hasClass('selected')) {
            el.removeClass('selected');
            Tienda.removePaoFilters(el.attr('data-ids'), el.attr('data-group'));
        } else {
            el.addClass('selected');
            Tienda.addPaoFilters(el.attr('data-ids'), el.attr('data-group'));
        }
    });
    
    tiendaJQ('.show-all a').on('click', function(event){
        el = tiendaJQ(this);
        event.preventDefault();
        group = el.attr('data-group');
        
        options = tiendaJQ('.'+group+' .tienda-paofilter-option');
        options.each(function(){
            opt = tiendaJQ(this);
            opt.removeClass('selected');
            Tienda.removePaoFilters(opt.attr('data-ids'), opt.attr('data-group'));
        });
        
        if (!tiendaJQ('#filter_pao_id-all-'+group).length) {
            tiendaJQ('<input id="filter_pao_id-all-'+group+'" name="filter_pao_id_groups['+group+'][]" value="" type="hidden" class="filter_pao_id" />').appendTo('#paofilters-form');
        }
    });
}

Tienda.removePaoFilters = function(ids_json, group) {
    var ids = tiendaJQ.parseJSON( ids_json );
    tiendaJQ.each(ids, function(index, value){
        tiendaJQ('#filter_pao_id-'+value).remove();
    });
}

Tienda.addPaoFilters = function(ids_json, group) {
    var ids = tiendaJQ.parseJSON( ids_json );
    tiendaJQ('#filter_pao_id-all-'+group).remove();
    tiendaJQ.each(ids, function(index, value){
        if (!tiendaJQ('#filter_pao_id-'+value).length) {
            tiendaJQ('<input id="filter_pao_id-'+value+'" name="filter_pao_id_groups['+group+'][]" value="'+value+'" type="hidden" class="filter_pao_id" />').appendTo('#paofilters-form');
        }
    });    
}