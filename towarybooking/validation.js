
jQuery('#zone').change(function(){
    var selection = jQuery('#zone').val();
    jQuery('.cbx').hide();
    switch(selection){
        case '4':
            jQuery('.checkBoxZone4').show();
            break;
        case '3':
			
			jQuery('.checkBoxZone3').show();
			
            break;
        case '2':
			
			jQuery('.checkBoxZone2').show();
            
            break;
        case '1':
			jQuery('.checkBoxZone1').show();
            break;
    }
});

jQuery('#fakturaCheck').click(function() {
	
	if(!jQuery('#fakturaCheck').hasClass('checked')) {
		jQuery('#fakturaCheck').addClass('checked')
		jQuery(".daneDoFaktury").show();
	} else {
		jQuery('#fakturaCheck').removeClass('checked')
		jQuery(".daneDoFaktury").hide();
	}
	
});



function makeidUp() {
    var text = "";
    var possible = "ayebcuadefuyghijklminoypaqersatuvwxeoyzi";
    for( var i=0; i < 7; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
	return text.charAt(0).toUpperCase() + text.slice(1);
}

function makeid(num) {
    var text = "";
    var possible = "abcdefghijklmnopqrstuvwxyz";
    for( var i=0; i < num; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
	return text;
}

jQuery('#addRandomData').click(function() {

var randomName = makeidUp(7) + " " + makeidUp(7);
var randomPhone = Math.floor(Math.random()*1000000001);
var randomEmail = 'mironez@gmail.com';
var randomSite = makeid(16) + "." + makeid(3);

jQuery("#formName").val(String(randomName)); 
jQuery("#formPhone").val(String(randomPhone)); 
jQuery("#formEmail").val(String(randomEmail)); 
jQuery("#formSite").val(String(randomSite)); 
jQuery('#re-gu-la-min').attr('checked', true);
	
});



function validateForm() {

	var countUnits = $(".selectedZone input[type='checkbox']").length;
	var countUnitsSel = $(".selectedZone input[type='checkbox']:checked").length;
	
	var unitsSel = new Array();
	
	$(".selectedZone input[type='checkbox']:checked").each(function() {
		unitsSel.push(this.value);
	});
	
	var regulamin = document.getElementById("re-gu-la-min").checked;

    if (regulamin == false) {
        jQuery("#regulamin").addClass("error");
        return false;
    } else if (countUnitsSel > '4' || countUnitsSel < '1') {
        jQuery(".selectedZone").addClass("error");
        jQuery(".errorUnits").show();
        return false;
    } else {
		return true;
	}
	
}
