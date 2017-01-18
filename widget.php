<?php

if ( !defined( 'ABSPATH' ) )
	exit;

class kgr_tag_list_widget extends WP_Widget {

	const INSTANCE = [
		'title' => NULL,
		'category' => NULL,
		'tags' => [],
	];

	public function __construct() {
		$widget_ops = [
			'classname' => 'kgr-tag-list-widget-frontend',
			'description' => 'A list of links to selected tags, optionally filtered by a category.',
		];
		parent::__construct( 'kgr_tag_list_widget', 'KGR Tag List', $widget_ops );
	}

	public function widget( $args, $instance ) {
		if ( is_null( $instance ) || !is_array( $instance ) || empty( $instance ) )
			$instance = self::INSTANCE;
		echo $args['before_widget'] . "\n";
		if ( !is_null( $instance['title'] ) )
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'] . "\n";
		if ( is_null( $instance['category'] ) )
			$url = get_home_url();
		else
			$url = get_category_link( $instance['category'] );
		$tags = get_tags( [
			'hide_empty' => FALSE,
			'include' => $instance['tags'],
		] );
		echo '<ul>' . "\n";
		foreach ( $tags as $tag ) {
			$href = sprintf( '%s?tag=%s', $url, $tag->slug );
			echo '<li>' . "\n";
			echo sprintf( '<a href="%s">%s</a>', $href, esc_html( $tag->name ) );
			if ( is_null( $instance['category'] ) ) {
				$count = $tag->count;
			} else {
				$posts = get_posts( [
					'cat' => $instance['category'],
					'tag_id' => $tag->term_id,
					'post_type' => 'post',
					'post_status' => 'publish',
					'nopaging' => TRUE,
					'fields' => 'ids',
				] );
				$count = count( $posts );
			}
			echo sprintf( '<span>(%d)</span>', $count ) . "\n";
			echo '</li>' . "\n";
		}
		echo '</ul>' . "\n";
		echo $args['after_widget'] . "\n";
	}

	public function form( $instance ) {
		if ( is_null( $instance ) || !is_array( $instance ) || empty( $instance ) )
			$instance = self::INSTANCE;
?>
<p>
	<label>
		<span>Title</span>
		<input class="widefat" id="<?= $this->get_field_id( 'title' ) ?>" name="<?= $this->get_field_name( 'title' ) ?>" type="text" value="<?= esc_attr( $instance['title'] ?? '' ) ?>" autocomplete="off" />
	</label>
</p>
<p>
	<label>
		<span>Category</span>
<?php
		wp_dropdown_categories( [
			'show_option_all' => 'any',
			'option_all_value' => 0,
			'orderby' => 'name',
			'order' => 'ASC',
			'show_count' => TRUE,
			'hide_empty' => FALSE,
			'name' => $this->get_field_name( 'category' ),
			'id' => $this->get_field_id( 'category' ),
			'class' => 'widefat',
			'selected' => is_null( $instance['category'] ) ? 0 : $instance['category'],
		] );
?>
	</label>
</p>
<p><button class="kgr-tag-list-widget-backend-toggle button" type="button">Show</button></p>
<p class="kgr-tag-list-widget-backend-container">
<?php
		$tags = get_tags( [
			'hide_empty' => FALSE,
		] );
		foreach ( $tags as $tag ) {
			$id = $this->get_field_id( 'tag-' . $tag->term_id );
			$name = $this->get_field_name( 'tags' );
			$crit = in_array( $tag->term_id, $instance['tags'] );
			$checked = checked( $crit, TRUE, FALSE );
			$style = $crit ? '' : ' style="display: none;"';
			echo sprintf( '<span%s>', $style ) . "\n";
			echo sprintf( '<input id="%s" name="%s[]" class="checkbox" type="checkbox" value="%d"%s />', $id, $name, $tag->term_id, $checked ) . "\n";
			$view = sprintf( '<a href="%s" target="_blank">%d</a>', get_tag_link( $tag->term_id ), $tag->count );
			echo sprintf( '<label for="%s">%s (%s)</label>', $id, esc_html( $tag->name ), $view ) . "\n";
			echo '</span>' . "\n";
		}
?>
</p>
<?php
	}

	public function update( $new_instance, $old_instance ) {
		if ( array_key_exists( 'title', $new_instance ) ) {
			$title = $new_instance['title'];
			if ( !is_null( $title ) && is_string( $title ) ) {
				$title = trim( preg_replace( '/\s+/', ' ', $title ) );
				if ( $title === '' ) {
					$title = NULL;
				}
			} else {
				$title = NULL;
			}
		} else {
			$title = NULL;
		}
		if ( array_key_exists( 'category', $new_instance ) ) {
			$category = intval( $new_instance['category'] );
		} else {
			$category = NULL;
		}
		if ( array_key_exists( 'tags', $new_instance ) && is_array( $new_instance['tags'] ) ) {
			$tags = array_map( 'intval', $new_instance['tags'] );
		} else {
			$tags = [];
		}
		return [
			'title' => $title,
			'category' => $category,
			'tags' => $tags,
		];
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'kgr_tag_list_widget' );
} );

add_action( 'admin_enqueue_scripts', function( string $hook ) {
	if ( $hook !== 'widgets.php' )
		return;
	wp_enqueue_style( 'kgr-tag-list-widget-backend', plugins_url( 'widget-backend.css', __FILE__ ) );
	wp_enqueue_script( 'kgr-tag-list-widget-backend', plugins_url( 'widget-backend.js', __FILE__ ), ['jquery'] );
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'kgr-tag-list-widget-frontend', plugins_url( 'widget-frontend.css', __FILE__ ) );
} );
