<?php
/**
 * JSON Feeder Template for displaying JSON Feed standard from jsonfeed.org.
 *
 */
$callback = trim(esc_html(get_query_var('callback')));
$charset  = get_option('blog_charset');
if(strpos($_SERVER['REQUEST_URI'], 'feed/json')!==false && isset($post->ID) && !empty($post->ID) && !is_category() && is_single()) {
		$site_description = get_bloginfo('description');

		$id = (int) $post->ID;
		$json = array(
			'version' 			=> 'https://jsonfeed.org/version/1',
			'title' 				=> $post->post_title.' > '.get_bloginfo('name'),
			'home_page_url' => get_permalink($post),
			'feed_url' 			=> get_permalink($post).'feed/json',
			'author'    		=> array(
				'name' => 'nextSTL',
				'url' => home_url()
			),
			'items' 				=> array()
		);

		if(!empty($site_description)) {
			$json['description'] = $site_description;
		}

		$single = array();

		$single['title'] = get_the_title();
		$single['date_published']	= date('Y-m-d\TH:i:s', get_post_time('U', true, $id)).'Z'; // get_the_date('Y-m-d\TH:i:s', $id).'Z',
		if(get_the_modified_time('U')>get_post_time('U', true, $id)) {
			$single['date_modified'] = date('Y-m-d\TH:i:s', get_the_modified_time('U')).'Z'; // get_the_date('Y-m-d\TH:i:s', $id).'Z',
		}
		$single['id'] = get_permalink($post);
		$single['url'] = get_permalink($post);
		$author = get_userdata($post->post_author);
		$single['author'] = array(
			'name' 		=> $author->display_name,
			'url' 		=> $author->user_url,
			'avatar'	=> get_avatar_url($post->post_author),
		);
		if(isset($post->post_excerpt) && !empty($post->post_excerpt)) {
			$single['summary'] = html_entity_decode(htmlspecialchars_decode($post->post_excerpt));
		} else {
			$single['summary'] = substr(strip_tags(html_entity_decode(htmlspecialchars_decode($post->post_content))), 0, 250).'…';
		}
		$single['content_html'] = $post->post_content;

		// thumbnail
		if (function_exists('has_post_thumbnail') && has_post_thumbnail($id)) {
			$single['image'] = get_the_post_thumbnail_url($id);
		}

		// category  -- not difference between this and tags in JSON Feed.
		$single['tags'] = array();
		$categories = get_the_category();
		$categories_arr = array();
		if ( ! empty( $categories ) ) {
			$categories_arr = wp_list_pluck( $categories, 'slug' );
		}

		// tags
		$tags = get_the_tags();
		$tags_arr = array();
		if ( ! empty( $tags) ) {
			$tags_arr = wp_list_pluck( $tags, 'name' );
		}
		if(!empty($tags_arr) || !empty($categories_arr)) {
			$single['tags'] = array_merge($tags_arr, $categories_arr);
		}

		$json['items'][] = $single;
		$json = json_encode($json);

		nocache_headers();
		header("Content-Type: application/json; charset={$charset}");
		echo $json;
		exit();
} else if(strpos($_SERVER['REQUEST_URI'], 'feed/json')!==false) {
	if(is_category()) {
		$cat = get_queried_object();
		query_posts('cat='.$cat->cat_ID.'&posts_per_page=20');
	} else {
		query_posts('posts_per_page=20');
	}
	if ( have_posts() ) {
		global $wp_query;
		$query_array = $wp_query->query;

		// Make sure query args are always in the same order
		ksort( $query_array );
		$site_description = get_bloginfo('description');

		$json = array(
			'version' 			=> 'https://jsonfeed.org/version/1',
			'title' 				=> get_bloginfo('name'),
			'home_page_url' => get_feed_link(),
			'feed_url' 			=> get_feed_link().'json',
			'author'    		=> array(
				'name' => 'nextSTL',
				'url' => home_url()
			),
			'items' 				=> array()
		);

		if(is_category()) {
			$json['title'] = $cat->name.' > '.get_bloginfo('name');
			$json['feed_url'] = get_category_link($cat->cat_ID).'feed/json';
		}

		if(!empty($site_description)) {
			$json['description'] = $site_description;
		}

		while ( have_posts() ) {
			the_post();
			$id = (int) $post->ID;

			$single = array();


			$single['title'] = get_the_title();
			$single['date_published']	= date('Y-m-d\TH:i:s', get_post_time('U', true, $id)).'Z'; // get_the_date('Y-m-d\TH:i:s', $id).'Z',
			if(get_the_modified_time('U')>get_post_time('U', true, $id)) {
				$single['date_modified'] = date('Y-m-d\TH:i:s', get_the_modified_time('U')).'Z'; // get_the_date('Y-m-d\TH:i:s', $id).'Z',
			}
			$single['id'] = get_permalink();
			$single['url'] = get_permalink();
			$single['author'] = array(
				'name' 		=> get_the_author(),
				'url' 		=> get_the_author_meta('url'),
				'avatar'	=> get_avatar_url(get_the_author_meta('ID')),
			);
			$single['summary'] = html_entity_decode(htmlspecialchars_decode(get_the_excerpt()));
			$single['content_html'] = get_the_content();

			// thumbnail
			if (function_exists('has_post_thumbnail') && has_post_thumbnail($id)) {
				$single['image'] = get_the_post_thumbnail_url($id);
			}

			// category  -- not difference between this and tags in JSON Feed.
			$single['tags'] = array();
			$categories = get_the_category();
			$categories_arr = array();
			if ( ! empty( $categories ) ) {
				$categories_arr = wp_list_pluck( $categories, 'slug' );
			}

			// tags
			$tags = get_the_tags();
			$tags_arr = array();
			if ( ! empty( $tags) ) {
				$tags_arr = wp_list_pluck( $tags, 'name' );
			}
			if(!empty($tags_arr) || !empty($categories_arr)) {
				$single['tags'] = array_merge($tags_arr, $categories_arr);
			}

			$json['items'][] = $single;
		}
		$json = json_encode($json);

		nocache_headers();
		header("Content-Type: application/json; charset={$charset}");
		echo $json;
		exit();
	} else {
		status_header('404');
		wp_die("404 Not Found");
	}
}
