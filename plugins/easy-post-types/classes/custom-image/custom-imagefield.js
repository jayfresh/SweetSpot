if (typeof imageField == 'undefined')
    var imageField = {};
else
    imageField = imageField||{};

imageField.removeImage = function(params) {
    var data = {
        action: 'imgfield_remove_image',
        index: params['index'],
        postid: params['postid']
        };
    jQuery.post(params['url'], data, function(response) {
        jQuery('#image-listing').html(response);
        alert(imageField.__('deleted'));
    });
}

imageField.addImage = function(params) {
    var imageVal=jQuery("input[name='"+params['image']+"']").val();
    if (imageVal.length==0) {
        alert(imageField.__('nfile'));
        return;
    }
    var data = {
        action: 'imgfield_add_image',
        field_name: params['image'],
        image: imageVal,
        title: jQuery("input[name='"+params['title']+"']").val(),
        alt: jQuery("input[name='"+params['alt']+"']").val(),
        postid: params['postid'],
        posttype: params['posttype'],
        extra: params['extra']
        };
    jQuery.post(params['url'], data, function(response) {
        jQuery('#image-listing').html(response);
        alert(imageField.__('added'));
    });
}
