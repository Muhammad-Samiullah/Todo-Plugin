function changeStatus(id, status) {
	console.log("changing status");
    var action = 'change_status';
    var form_data = {'id': id, 'status': status, 'action': action};
    jQuery(document).ready(function($) {
		$.ajax({
			url: frontend_ajax_object.ajaxurl,
		    type:"POST",
		    dataType:'text',
		    data : form_data,
			success: function( response ) {
				console.log(response);
			},
			error: function( response ) {
				console.log(response);
			}
		});
	});
}



function checkMark(element) {
	changeStatus(jQuery(element).data("id"), jQuery(element).is(":checked"));
	element = jQuery(element).parent().parent().parent();
	console.log(jQuery(element).find("input").is(":checked"));
	
    if(jQuery(element).find("input").is(":checked")) {
		jQuery(element).addClass("completed");
        jQuery(element).find(".status").html("Completed");

    }
    else {
		jQuery(element).removeClass("completed");
        jQuery(element).find(".status").html("Pending");
    }
	progressBar();
}

function addTodoContent(form_data) {
    jQuery(document).ready(function($) {
		$.ajax({
			url: frontend_ajax_object.ajaxurl,
		    type:"POST",
		    dataType:'text',
		    data : form_data,
			success: function( response ) {
				console.log(response);
                location.reload();
			},
			error: function( response ) {
				console.log(response);
			}
		});
	});
}

jQuery(document).on('click', "#todo-form-btn", function(e){
	
	let post_id = jQuery(this).attr("data-post-id");
    let user_id = jQuery(this).attr("data-user-id");
    let action = 'todo_data_handler';

  var form_data = {};
  jQuery("#todoform input[type='text']").each((i, input) => form_data[input.name.toLowerCase()] = input.value);
	form_data['post-id'] = post_id;
	form_data['user-id'] = user_id;
	form_data['action'] = 'todo_data_handler';
  console.log(JSON.stringify(form_data));

    addTodoContent(form_data);
})

function progressBar(){
	progressBar.total = 0;
	progressBar.percent = 0;
	jQuery(".to-do-list").find('input').each(function () {
		progressBar.total += 1;
		if(jQuery(this).is(":checked")){
			progressBar.percent += 1;
		}
	
	});
	var v = (progressBar.percent / progressBar.total)*100;
	jQuery('.progress-bar').attr('aria-valuenow', v).css('width', v+'%');
}

jQuery( document ).ready(function() {
    progressBar();
});
