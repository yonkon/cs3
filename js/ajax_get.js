function ajax_get_options(url, post_data, target_select) {
    var $tagret = $(target_select);
    $.ajax({
        type: "POST",
        url: url,
        data: post_data
})

.success(function( msg ) {
    if(typeof msg != 'undefined') {
        var data = JSON.parse(msg);
        if(typeof data.status != 'undefined' && data.status == 'OK') {
            $(target_select+':not(:first)').remove();
            for(var i=0; i<data.data.length; i++) {
                var option = data.data[i];
                var opt = document.createElement('option');
                $tagret.append(opt);
                var $opt = $(opt);
                $opt.val(option.value);
                $opt.text(option.text);
                for(var j=0; j<option.data.length; j++) {
                    var o_data = option.data;
                    $opt.data(o_data.key, o_data.value);
                }
            }
        } else {
            alert('JSON malformat error');
        }
    }
})

    .error(function( msg ) {
            alert('Server error');
    });
}

function ajax_get_data(url, post_data, callback){

    $.ajax({
        type: "POST",
        url: url,
        data: post_data
    })

        .success(function( msg ) {
            if(typeof msg != 'undefined') {
                var data = JSON.parse(msg);
                if(typeof data.status != 'undefined' && data.status == 'OK') {
                    callback(data.data);
                } else {
                    alert('JSON malformat error');
                }
            }
        })

        .error(function( msg ) {
            alert('Server error');
        });
}