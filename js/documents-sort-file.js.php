<?php

define('INC_FROM_CRON_SCRIPT',true);
require('../config.php');

$langs->load('processrules@processrules');

$jsText = array(
	'move'
);

$jsTextTranslate = new stdClass();
foreach ($jsText as $text){
	$jsTextTranslate->{$text} = $langs->transnoentities($text);
}

header('Content-Type: application/javascript');

?>

$(document).ready(function(){

    var sortableElement = $("#tablelines");

    sortableElement.find("tbody tr").each(function( index ) {
        let movebtn = '<span class="dragable fa fa-arrows-alt-v" style="cursor: move;" ></span> ';
		$( this ).find(".actionbuttons").prepend(movebtn);
    });

    sortableElement.sortable({
		cursor: "move",
		handle: ".dragable",
		items: 'tr:not(.nodrag,.nodrop,.noblockdrop)',
		delay: 150, //Needed to prevent accidental drag when trying to select
		opacity: 0.8,
		axis: "y", // limit y axis
		placeholder: "ui-state-highlight",
		start: function( event, ui ) {
			var colCount = ui.item.children().length;
			ui.placeholder.html('<td colspan="'+colCount+'">&nbsp;</td>');
		},
		stop: function (event, ui) {

			$.ajax({
				data: {
				    ''
					roworder: cleanSerialize($(this).sortable('serialize'))
				},
				type: 'POST',
				url: '<?php dol_buildpath('/processrules/script/interface.php', 1); ?>',
				success: function(data) {
					console.log(data);
				},
			});

		},
		update: function (event, ui) {

		}
	});
});
