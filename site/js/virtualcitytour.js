			
function vote(poi_id, token){
	jImc.ajax({
		type : 'GET',
		url : 'index.php',
		datatype: 'json',
		data: 'option=com_virtualcitytour&controller=virtualcitytour&task=addVote&format=json&poi_id=' + poi_id + '&' + token + '=1',
		success: function(data){
			alert( data.msg );
			if (data.votes === undefined){
				donothing = 1;
			}
			else{
				//update the counter and flash it
				jImc(".imc-votes-counter").html(data.votes);
				jImc(".imc-flasher").effect("highlight", {color: '#60FF05'}, 1500);
			}
		}		
	});
}

function comment(){
	if(jImc("#imc-comment-area").val() == ''){
		alert(Joomla.JText._('COM_VIRTUALCITYTOUR_WRITE_COMMENT')); 
		return;
	}
	var base = window.com_virtualcitytour.base;
	var htmlStr = jImc('#imc-comment-area').val();
	jImc('#imc-comment-area').val(jImc('<div/>').text(htmlStr).html());
	jImc('#commentBtn').hide();
	jImc('#commentIndicator').append('<div id="ajaxBusy"><p><img src="'+base+'/components/com_virtualcitytour/images/ajax-loader.gif"></p></div>');
	
	jImc.ajax({
		type : 'POST',
		url : 'index.php',
		datatype: 'json',
		data: jImc('#com_virtualcitytour_comments').serialize(),
		success: function(data){
			jImc('#commentIndicator').remove();
			jImc('#commentBtn').show();
			if (data.comments === undefined){
				alert('Problem sending message (trying to send invalid characters like quotes?)');
				donothing = 1;
			}
			else{
				//create a container for the new comment
				var content = '<div class="imc-chat"><span class="imc-chat-info">'+data.comments.textual_descr+'</span><span class=\"imc-chat-desc\">'+data.comments.description+'</span><div>';
				div = jImc(content).prependTo("#imc-comments-wrapper");
				jImc("#imc-comment-area").val('');
				div.effect("highlight", {color: '#60FF05'}, 1500);
			}
		}

	});
}