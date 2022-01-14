jQuery(document).on("click", ".import", function() {
	var form = new FormData(jQuery('.import_form')[0]);
	
	jQuery.ajax({
		url:'',
		method:'POST',
		data:form,
		cache: false,
		processData: false,
		contentType: false,
		success:function(data){
			console.log(data);
		},
		error:function(data){
			console.log('err');
		}
	}); // End of ajax
});
