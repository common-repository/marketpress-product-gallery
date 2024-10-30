<?php
	$html = '';
	$html .= '<div class="mp_gallery_wrap">';
		$html .= '<div class="mp_gallery_viewport">';
			$html .= '<div class="mp_gallery_image">';
				$large = wp_get_attachment_image_src($mp_prod_img_id[0], 'mp-large-view');
				$html .= '<img class="mp_zoom" src="'. $large[0] .'">';
			$html .= '</div>';
			$html .= '<div class="mp_gallery_title"><span>';
				$html .= $mp_prod_image_title[0];
			$html .= '</span></div>';
		$html .= '</div>';
		$html .= '<div class="mp_image_collection">';
			$html .= '<div class="mp_control_left"><img class="mp-icon" src="'. plugins_url() .'/marketpress-product-gallery/img/left.png"></div>';
			$html .= '<div class="mp_control_right"><img class="mp-icon" src="'. plugins_url() .'/marketpress-product-gallery/img/right.png"></div>';
			$html .= '<div class="mp_image_list"><ul>';
				$i = 0;
				foreach( $mp_prod_image_paths as $mp_prod_image_path ) {
					$html .= '<li>';
						$thumb = wp_get_attachment_image_src($mp_prod_img_id[$i], 'mp-thumb');
						$large = wp_get_attachment_image_src($mp_prod_img_id[$i], 'mp-large-view');
						$html .= '<img src="'. $thumb[0] .'">';
						$html .= '<input type="hidden" value="'.$large[0].'">';
						$html .= '<div class="mp_hide">' . $mp_prod_image_title[$i++] . '</div>';
					$html .= '</li>';
				}
			$html .= '</ul></div>';
		$html .= '</div>';
	$html .= '</div>';