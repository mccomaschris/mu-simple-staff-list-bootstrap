<?php

function sslp_staff_member_listing_shortcode_func($atts) {
	extract(shortcode_atts(array(
	  'single' => 'no',
	  'group' => '',
	  'wrap_class' => '',
	  'order' => 'ASC',
	), $atts));

	// Get Template and CSS

	$custom_html 				= stripslashes_deep(get_option('_staff_listing_custom_html'));
	$custom_css 				= stripslashes_deep(get_option('_staff_listing_custom_css'));
	$default_tags 				= get_option('_staff_listing_default_tags');
	$default_formatted_tags 	= get_option('_staff_listing_default_formatted_tags');
	$output						= '';
	$group						= strtolower($group);
	$order						= strtoupper($order);
	$staff                      = '';

	$use_external_css			= get_option('_staff_listing_write_external_css');

	/**
	  * Set up our WP_Query
	  */

	$args = array( 'post_type' => 'staff-member', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'post_status' => 'publish' );

	// Check user's 'order' value
	if ($order != 'ASC' && $order != 'DESC') {
		$order = 'ASC';
	}

	// Set 'order' in our query args
	$args['order'] = $order;
	$args['staff-member-group'] = $group;

	$staff = new WP_Query( $args );


	/**
	  * Set up our loop_markup
	  */

	$loop_markup = $loop_markup_reset = str_replace("[staff_loop]", "", substr($custom_html, strpos($custom_html, "[staff_loop]"), strpos($custom_html, "[/staff_loop]") - strpos($custom_html, "[staff_loop]")));


	// Doing this so I can concatenate class names for current and possibly future use.
	$staff_member_classes = $wrap_class;

	// Prepare to output styles if not using external style sheet
	if ( $use_external_css == "no" ) {
		$style_output = '<style>'.$custom_css.'</style>';
	}

	$i = 0;

	if( $staff->have_posts() ) {

		$output .= '<div class="staff-member-listing '.$group.'">';

	while( $staff->have_posts() ) : $staff->the_post();

        if ($i == ($staff->found_posts)-1) {
			$staff_member_classes .= " last";
            $closing_tag = '</div>';
		} else {
			$staff_member_classes .= " border-b border-gray-100 ";
		}

		if ($i == 0) {
			$staff_member_classes .= " mb-8 ";
		} else {
			$staff_member_classes .= " my-8  ";
		}

		if ($i % 2) {
			$output .= '<div class=" '.$staff_member_classes.'">';

		} else {
			$output .= '<div class=" '.$staff_member_classes.'">';
		}

		global $post;

		$custom 	= get_post_custom();
		$name 		= get_the_title();
		//$name_slug	= basename(get_permalink());  // using basename caused the slug to fetch the page url and not the post url
        $name_slug  = get_permalink(); //
		$title 		= $custom["_staff_member_title"][0];
		$email 		= $custom["_staff_member_email"][0];
		$phone 		= $custom["_staff_member_phone"][0];
		$bio 		= $custom["_staff_member_bio"][0];
        $office     = $custom["_staff_member_office"][0];
        $website    = $custom["_staff_member_website"][0];
		$fb_url		= $custom["_staff_member_fb"][0];
		$tw_url		= $custom["_staff_member_tw"][0];

        $phone      = format_phone($phone);  // option added back in.
        $mu_custom  = ''; // holds Website, Facebook, & Twitter links in an unformatted list only if they exist

        if($website || $fb_url || $tw_url) {
            $mu_custom = "<ul>";

            if($website) {
                $mu_custom .= '<li><a href="'.$website.'">Website</a></li>';
            }

            if($fb_url) {
                $mu_custom .= '<li><a href="'.$fb_url.'">Facebook</a></li>';
            }

		    if($tw_url) {
		        $mu_custom .= '<li><a href="'.$tw_url.'">Twitter</a></li>';
		    }
            $mu_custom .= '</ul>';
        }

        if(has_post_thumbnail()){

			$photo_url = wp_get_attachment_url( get_post_thumbnail_id() );
			$photo = '<img class="mx-auto" src="'.$photo_url.'" alt = "'.$title.'">';
		}else{
			$photo_url = '';
			$photo = '';
		}

		if ($bio) {
			if (function_exists('wpautop')) {

				// $bio_format  = '<p><div class="" type="button" x-on:click="showBio = !showBio">Read Bio</div></p>';
				$bio_format = '<div id="' . str_replace(" ", "", $group) . $i . '"><!-- asdf -->' . wpautop($bio) . '</div>';
			}
		} else {
			$bio_format = "";
		}


		$email_mailto = '<div class="flex items-center"><svg class="h-4 w-4 mr-2 fill-current" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path d="M28.002 24.457V12.466c-.334.375-.693.718-1.077 1.03-2.789 2.145-5.007 3.904-6.652 5.277-.53.448-.962.796-1.296 1.046s-.784.502-1.35.757c-.568.255-1.1.382-1.6.382h-.03c-.5 0-1.034-.127-1.6-.382-.568-.255-1.018-.507-1.35-.757-.334-.25-.764-.598-1.296-1.046-1.645-1.373-3.862-3.134-6.652-5.277-.386-.313-.745-.655-1.077-1.03v11.991c0 .136.05.252.148.352.098.098.216.148.352.148h22.984c.136 0 .252-.05.352-.148.095-.098.145-.216.145-.352zm0-16.409v-.382l-.007-.204-.046-.195-.086-.141-.141-.118-.218-.039H4.518c-.136 0-.252.05-.352.148-.098.098-.148.216-.148.352 0 1.748.764 3.227 2.295 4.434 2.009 1.582 4.096 3.232 6.261 4.95.063.052.245.205.546.461s.541.45.718.586c.177.136.409.3.695.491.286.193.548.336.789.429.239.093.463.141.671.141h.03c.209 0 .432-.046.671-.141.239-.093.502-.238.789-.429.286-.193.518-.357.695-.491.177-.136.416-.33.718-.586s.484-.409.546-.461c2.164-1.718 4.252-3.368 6.261-4.95.563-.448 1.086-1.048 1.57-1.804.486-.754.729-1.438.729-2.052zM30 7.47v16.988c0 .688-.245 1.275-.734 1.764s-1.077.734-1.764.734H4.518c-.688 0-1.275-.245-1.764-.734s-.734-1.077-.734-1.764V7.47c0-.688.245-1.275.734-1.764s1.077-.734 1.764-.734h22.984c.688 0 1.275.245 1.764.734S30 6.783 30 7.47z" /></svg><a class="staff-member-email" href="mailto:'.antispambot( $email ).'" title="Email '.$name.'">'.antispambot( $email ).'</a></div>';
		$email_nolink = antispambot( $email );



		$accepted_single_tags = $default_tags;
		$replace_single_values = array($name, $name_slug, $photo_url, $title, $email_nolink, $phone, $bio, $office, $fb_url, $tw_url);

		$accepted_formatted_tags = $default_formatted_tags;
		$replace_formatted_values = array('<h3 class="">'.$name.'</h3>', '<h4 class="mb-2">'.$title.'</h4>', $photo, $email_mailto, $bio_format, $mu_custom );

		$loop_markup = str_replace($accepted_single_tags, $replace_single_values, $loop_markup);
		$loop_markup = str_replace($accepted_formatted_tags, $replace_formatted_values, $loop_markup);

		$output .= $loop_markup;

		$loop_markup = $loop_markup_reset;

		$output .="</div>";
		$i += 1;

	endwhile;

	$output .= "</div> <!-- Close staff-member-listing -->";
	}

	wp_reset_query();

	$output = $style_output.$output;

	return do_shortcode($output);
}
add_shortcode('simple-staff-list', 'sslp_staff_member_listing_shortcode_func');

?>