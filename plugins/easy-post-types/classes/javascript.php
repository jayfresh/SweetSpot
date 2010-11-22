<?php

class CustomType_Javascript {

    public function create($parent) {
        $cmsg = __('Cancel','cct');
        $dmsg = __('Delete All','cct');
        $mmsg = __('Move all to Posts','cct');
        $updmsg = __('Update the category?','cct');
        $lupd = __('Labels updated','cct');
        $vupd = __('Visibility updated','cct');
        $pupd = __('Permissions updated','cct');
        $fupd = __('Fields updated','cct');
        $aupd = __('Admin Interface updated','cct');
        $confmsg = __('Are you sure you want to delete all the selected post types?', 'cct');
        $confmsgfield = __('Are you sure you want to delete this field?', 'cct');

        $label_list = $parent->prepare_labels();
        $permissions = $parent->prepare_permissions();

        $throbber = admin_url( 'images/wpspin_light.gif' );

    $var = <<< EOF
    <script language="javascript">

    if (typeof contexflow == 'undefined')
        var contexflow = contexflow||{};
    else
        contexflow = contexflow||{};

    contexflow.__=function(s) {
        var msg={
            'cmsg':     '$cmsg',
            'dmsg':     '$dmsg',
            'mmsg':     '$mmsg',
            'updmsg':   '$updmsg',
            'lupd':     '$lupd',
            'vupd':     '$vupd',
            'pupd':     '$pupd',
            'aupd':     '$aupd',
            'fupd':     '$fupd',
            'confmsg':  '$confmsg',
            'cnfmsgfield': '$confmsgfield'
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

    contexflow.permissions=function(data) {
        $permissions
    };

    contexflow.label_list=function(data) {
        $label_list
    };

    contexflow.wpUrls = {
      'throbber': '$throbber'
    };

</script>
EOF;
    echo $var;
    }
}
?>