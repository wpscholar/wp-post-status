<?php

namespace wpscholar\WordPress;

/**
 * Class PostStatus
 *
 * @package wpscholar\WordPress
 */
class PostStatus {

	/**
	 * Internal name for the post status.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Arguments for the post status.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Post status object (after registration).
	 *
	 * @var object
	 */
	protected $status;

	/**
	 * Register a new post status
	 *
	 * @param string $name Post status name
	 * @param array $args Post status args
	 *
	 * @return object Post status object
	 */
	public static function register( $name, array $args = [] ) {
		$instance = new self( $name, $args );
		$instance->initialize();

		return $instance->status;
	}

	/**
     * Get label for a post status
     *
	 * @param string $status
	 * @param string $label_name
	 *
	 * @return string
	 */
	public static function getLabel( $status, $label_name ) {
		$label = '';

		$statusObj = get_post_status_object( $status );
		if ( $statusObj ) {
			$label = $statusObj->label;
			if ( isset( $statusObj->labels, $statusObj->labels[ $label_name ] ) ) {
				$label = $statusObj->labels[ $label_name ];
			}
		}

		return $label;
	}

	/**
	 * PostStatus constructor.
	 *
	 * @param string $name Post status name
	 * @param array $args Post status args
	 */
	protected function __construct( $name, array $args = [] ) {

		// Custom default args
		$defaults = [
			'labels'     => null,
			'post_types' => [],
		];

		// Setup custom labels
		if ( isset( $args['labels'] ) ) {

			$default_labels = [
				'singular_name' => null,
				'plural_name'   => null,
				'past_tense'    => null,
			];

			$defaults['labels'] = array_merge( $default_labels, $args['labels'] );
			unset( $args['labels'] );
		}

		// Set class properties
		$this->name = $name;
		$this->args = array_merge( $defaults, $args );

	}

	/**
	 * Setup action hooks
	 */
	public function initialize() {

		// Register post status after all post types exist, but before any queries are run
		$this->register_post_status();

		// Append post status to dropdown on post edit screens
		add_action( 'admin_footer-post.php', array( $this, 'append_to_post_status_dropdown' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'append_to_post_status_dropdown' ) );

		// Append post status to dropdown on inline edit
		add_action( 'admin_footer-edit.php', array( $this, 'append_to_inline_status_dropdown' ) );

		// Display post status on the post edit list
		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );
	}

	/**
	 * Register the post status in WordPress
	 */
	public function register_post_status() {
		$this->status = register_post_status( $this->name, $this->args );
	}

	/**
	 * Get a label by name
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function get_label( $name ) {
		$label = $this->status->label;
		if ( isset( $this->status->labels, $this->status->labels[ $name ] ) ) {
			$label = $this->status->labels[ $name ];
		}

		return $label;
	}

	/**
	 * Append post status to the dropdown on the post edit screen.
	 */
	public function append_to_post_status_dropdown() {

		$post = get_post();

		if ( in_array( $post->post_type, $this->status->post_types ) ) {

			$isSelected = $this->status->name === $post->post_status;

			$option = sprintf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $this->status->name ),
				$isSelected ? ' selected="selected"' : '',
				esc_html( $this->status->label )
			);

			?>
            <script>
                jQuery(document).ready(function ($) {
                    $('select#post_status').append('<?php echo $option; ?>');
                });
            </script>
			<?php if ( $isSelected ): ?>
                <script>
                    jQuery(document).ready(function ($) {
                        $('#post-status-display').text('<?php echo esc_js( $this->status->label ) ?>');
                    });
                </script>
			<?php endif;
		}

	}

	/**
	 * Append post status to the quick edit status dropdown on the post listing page.
	 */
	public function append_to_inline_status_dropdown() {

		$post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );

		if ( in_array( $post_type, $this->status->post_types ) ) {

			$option = sprintf(
				'<option value="%s">%s</option>',
				esc_attr( $this->status->name ),
				esc_html( $this->status->label )
			);

			?>
            <script>
                jQuery(document).ready(function ($) {
                    $('.inline-edit-status select').append('<?php echo $option; ?>');
                });
            </script>
			<?php
		}
	}

	/**
	 * Filter post states that are shown on the post listing page.
	 *
	 * @param array $states
	 * @param \WP_Post $post
	 *
	 * @return mixed
	 */
	public function display_post_states( $states, \WP_Post $post ) {
		if ( $post->post_status === $this->status->name ) {
			array_push( $states, $this->get_label( 'post_state' ) );
		}

		return $states;
	}

}