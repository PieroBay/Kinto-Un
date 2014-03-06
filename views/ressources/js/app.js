$(function() {
	/*$("form").submit(function(){
		var tara = {};

		$(this).children('.field').children('.cont').each(function(){
			var name = $(this).attr('name');
			var value = $(this).val();
			tara[name] =  value ;
		});
		$.ajax({
			type: "POST",
			url: "./ajout",
	        data: {d:'poooo'},
	        
			success: function(data){
				responseData = jQuery.parseJSON(data);
				if(responseData.status == "error"){
					alert('error');
				}else{
					alert('okk');
				}
			}
		})
		return false;
	});*/
});