"use strict";
 
wp.domReady( 
    function(){
        jQuery(document).ready(function($){
            jQuery(document).on('change', 'input#number', function(){
                getCampoDataForBlockelements('personAll', 'select#campoid');
                getCampoDataForBlockelements('lectureByDepartment', 'select#id');
            });
            // jQuery(document).on('change', 'select#campoid', function(){
            //     jQuery(document).ready(function($){
            //         if (jQuery('select#id') == 'undefined'){
            //             var task = 'mitarbeiter-' + (jQuery('select#campoid').val() == '' ? 'alle' : 'einzeln');
            //             jQuery('select#task').val(task);
            //         }
            //     });
            // });
            // jQuery(document).on('change', 'select#id', function(){
            //     jQuery(document).ready(function($){
            //         // var task = 'lectures-' + (jQuery('select#id').val() == '' ? 'alle' : 'einzeln');
            //         jQuery('select#task').val(task).trigger('change');
            //     });
            // });
        });
    });

function getCampoDataForBlockelements($dataType, $output) {
    var $campoOrgID = jQuery('input#number').val();
    var $output = jQuery($output);

    if ($campoOrgID){
        $output.html('<option value="">loading... </option>');
    
        jQuery.post(campo_ajax.ajax_url, { 
            _ajax_nonce: campo_ajax.nonce,
            action: 'GetCampoDataForBlockelements',
            data: {'campoOrgID':$campoOrgID, 'dataType':$dataType},               
        }, function(result) {
            $output.html(result);
        });
    }
}
