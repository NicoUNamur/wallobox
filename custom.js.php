<?php
    include('class/auth.inc.php');
    $do_gzip_compress=Header_Compress();
    Header("Content-type: text/javascript"); ?>
<!--

//$( ".jqdate" ).datepicker({ dateFormat: 'yy-mm-dd' });
$( ".jqdate" ).datetimepicker({
	format:'Y-m-d',
	mask:'9999-19-39',
	timepicker:false
});
$( ".jqtime" ).datetimepicker({
	mask:'29:59',
	format:'H:i',
	datepicker:false,
	step:30

});

$( ".jqdatetime" ).datetimepicker({
	format:'Y-m-d H:i',
	mask:'9999-19-39 29:59',
	//allowTimes:['12:00','13:00','15:00','17:00','17:05','17:20','19:00','20:00'],
	step:30
});

$('.autocompl').autocomplete({
	source : function(requete, reponse){ // les deux arguments représentent les données nécessaires au plugin
	$.ajax({
            url : 'get_suggestions.php?mode=json&'+ $(this.element).prop("id") +'='+ $(this.element).val(),
            dataType : 'json',
			type: 'POST',
            data : {
                name_startsWith : $(this.element).val(), // on donne la chaîne de caractère tapée dans le champ de recherche
                maxRows : 10
            },            
            success : function(donnee){
                reponse($.map(donnee.Liste, function(item){
						var $li = $('<li>'),
							$span = $('<a>'),
							$img = $('<img>');
						if(item.icon)
						$img.attr({
						  src: '/ape/'+item.icon,
						  alt: item.label
						});
						$li.attr('data-value', item.value);
						$li.append($span);
						if(item.icon)
							$li.find('a').append(item.label).append($img);    
						else
							$li.find('a').append(item.label);    
						return { id : item.value , label : $li.html() , value : item.label , icon : item.icon , field : donnee.Field }; //.appendTo(ul);
                }));
            }
        });
    }
	,	html: true
	,	select: function (event, ui) {
			$("#"+ui.item.field).val(ui.item.id);
			return ui.item.descr;
		} 
})
;

$('.filter_autocompl').autocomplete({
	source : function(requete, reponse){ // les deux arguments représentent les données nécessaires au plugin
	$.ajax({
            url : 'get_suggestions.php?mode=json&'+ $(this.element).prop("id") +'='+ $(this.element).val(),
            dataType : 'json',
			type: 'POST',
            data : {
                name_startsWith : $(this.element).val(), // on donne la chaîne de caractère tapée dans le champ de recherche
                maxRows : 10
            },            
            success : function(donnee){
                reponse($.map(donnee.Liste, function(item){
						var $li = $('<li>'),
							$span = $('<a>'),
							$img = $('<img>');
						if(item.icon)
						$img.attr({
						  src: '/ape/'+item.icon,
						  alt: item.label
						});
						$li.attr('data-value', item.value);
						$li.append($span);
						if(item.icon)
							$li.find('a').append(item.label).append($img);    
						else
							$li.find('a').append(item.label);    
						return { id : item.value , label : $li.html() , value : item.label , icon : item.icon , field : 'filter_'+donnee.Field }; //.appendTo(ul);
                }));
            }
        });
    }
	,	html: true
	,	select: function (event, ui) {
			$("#"+ui.item.field).val(ui.item.id);
			return ui.item.descr;
		} 
})
;

// -->
<?php    Footer_Compress($do_gzip_compress); ?>