<?php
class ImageField_Javascript {

    public function create($parent) {
        $nfile = __('Need to specify an image URL','cct');
        $added  = __('The image has been added','cct');
        $deleted = __('The image has been removed','cct');

    $var = <<< EOF
    <script language="javascript">

    if (typeof imageField == 'undefined')
        var imageField = {};
    else
        imageField = imageField||{};

    imageField.__=function(s) {
        var msg={
            'nfile': '$nfile',
            'added': '$added',
            'deleted': '$deleted',
            };
        if(msg[s]){
          return msg[s];
        } else {
          if(window.console && window.console.log){
            window.console.log('Missing translation for: '+s);
          }
          return s;
        }
    };
</script>
EOF;
    echo $var;
    }
}
?>