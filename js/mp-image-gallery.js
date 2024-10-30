;jQuery(function($){
	
	$(document).on( 'click', '.mp-more-icon.plus', function() {
		var html = '<tr>';
                    html += '<td width="5%">'+ data.title +'</td>';
                    html += '<td width="20%"><input type="text" name="mp_prod_image_title[]" placeholder="'+ data.prod_title +'"></td>';
                    html += '<td width="10%"><input class="mp-image-upload-btn" type="button" value="'+ data.upload +'"></td>';
                    html += '<td width="40%"><input class="mp-image-upload-path" style="width: 100%" type="text" name="mp_prod_image_path[]" placeholder="'+data.path+'"></td>';
                    html += '<td width="10%"><span class="mp-more-icon minus">[ - ]</span></td>';
		    html += '<td width="15"><img width="50" src=""><input type="hidden" name="mp_prod_img_id[]"></td>';
                html += '</tr>';
		
		$('.mp-gallery-meta-table').append(html);
		
	});
	
	$(document).on( 'click', '.mp-more-icon.minus', function() {
		var con = confirm(data.confirm_msg);
		if(con) $(this).closest('tr').remove();
	});
	
	
	
	var custom_uploader, obj;
	$(document).on('click', '.mp-image-upload-btn', function(e) {
		e.preventDefault();
		obj = $(this);
		//If the uploader object has already been created, reopen the dialog
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
	
		//Extend the wp.media object
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});
	
		//When a file is selected, grab the URL and set it as the text field's value
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			obj.parent()
				.next().find('.mp-image-upload-path').val(attachment.url)
				.parent().next().next().find('img').attr('src', attachment.url)
				.next().val(attachment.id);
		});
	
		//Open the uploader dialog
		custom_uploader.open();	
	});
	
	$('.mp_image_collection ul li').click(function() {
		var img = $(this).find('input[type=hidden]').val(),
		    text = $(this).find('.mp_hide').text();
		    
		$('.mp_gallery_image')
			.fadeOut('fast', function() {
				$(this).find('img').attr('src', img)
			})
			.fadeIn();
		$('.mp_gallery_title span').text(text);
		
		$('.mp_zoom').addimagezoom({
			magnifiersize: [parseInt(data.zoom_view_width), parseInt(data.zoom_view_height)],
			magnifierpos: 'right',
			cursorshade: true,
			largeimage: img
		});
	});
	
	$('.mp_zoom').addimagezoom({
		magnifiersize: [parseInt(data.zoom_view_width), parseInt(data.zoom_view_height)],
		magnifierpos: 'right',
		cursorshade: true,
		largeimage: $(this).attr('src')
	});
	
	var total_image = $('.mp_image_list ul li').length,
	    list_width = total_image * 70,
	    viewport_width = $('.mp_image_list').width();
	    level = Math.floor(viewport_width / 70),
	    end_pos = total_image - level,
	    current_pos = 0;
	
	$('.mp_image_list ul').css('width', list_width);
	$('.mp_control_right').click(function() {
		if(current_pos < end_pos) {
			current_pos++;
			$('.mp_image_list ul').animate({
				marginLeft: '-=70px'
			});
		}
	});
	$('.mp_control_left').click(function() {
		if(current_pos > 0) {
			current_pos--;
			$('.mp_image_list ul').animate({
				marginLeft: '+=70px'
			});
		}
	});
	
	
	
	
});