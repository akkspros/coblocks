<?php
/**
 * Render form block fields
 *
 * @package   @@pkg.title
 * @author    @@pkg.author
 * @link      @@pkg.author_uri
 * @license   @@pkg.license
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main @@pkg.title Class
 *
 * @since 2.0.0
 */
class CoBlocks_Form {

	/**
	 * Email content
	 *
	 * @var string
	 */
	private $email_content;

	/**
	 * Form hash
	 *
	 * @var string
	 */
	private $form_hash;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		$this->register_form_blocks();

	}

	/**
	 * Register the form blocks.
	 */
	public function register_form_blocks() {

		register_block_type(
			'coblocks/form',
			[
				'render_callback' => [ $this, 'render_form' ],
			]
		);

		register_block_type(
			'coblocks/field-name',
			[
				'parent'          => [ 'coblocks/form' ],
				'render_callback' => [ $this, 'render_field_name' ],
			]
		);

		register_block_type(
			'coblocks/field-email',
			[
				'parent'          => [ 'coblocks/form' ],
				'render_callback' => [ $this, 'render_field_email' ],
			]
		);

		register_block_type(
			'coblocks/field-textarea',
			[
				'parent'          => [ 'coblocks/form' ],
				'render_callback' => [ $this, 'render_field_textarea' ],
			]
		);

		/**
		 * Fires when the coblocks/form block and sub-blocks are registered
		 */
		do_action( 'coblocks_register_form_blocks' );

	}

	/**
	 * Render the form
	 *
	 * @param  array $atts    Block attributes.
	 * @param  mixed $content Block content.
	 *
	 * @return mixed Form markup or success message when form submits successfully.
	 */
	public function render_form( $atts, $content ) {

		$this->form_hash = sha1( json_encode( $atts ) . $content );
		$submitted_hash  = filter_input( INPUT_POST, 'form-hash', FILTER_SANITIZE_STRING );

		if ( $submitted_hash === $this->form_hash ) {

			$submit_form = $this->process_form_submission( $atts );

			if ( $submit_form ) {

				return $this->success_message();

			}
		}

		ob_start();

		?>

		<div class="coblocks-form <?php echo esc_attr( get_the_ID() ); ?>">
			<form action="<?php echo esc_url( set_url_scheme( get_the_permalink() ) ); ?>" method="post">
				<?php echo do_blocks( $content ); ?>
				<p class="form-submit">
					<?php $this->render_submit_button( $atts ); ?>
					<?php wp_nonce_field( 'coblocks-form-submit', 'form-submit' ); ?>
					<input type="hidden" name="action" value="coblocks-form-submit">
					<input type="hidden" name="form-hash" value="<?php echo esc_attr( sha1( json_encode( $atts ) . $content ) ); ?>">
				</p>
			</form>
		</div>

		<?php

		return ob_get_clean();

	}

	/**
	 * Render the name field
	 *
	 * @param  array $atts    Block attributes.
	 * @param  mixed $content Block content.
	 *
	 * @return mixed Markup for the name field.
	 */
	public function render_field_name( $atts, $content ) {

		$label         = isset( $atts['label'] ) ? $atts['label'] : __( 'Name', 'coblocks' );
		$label_slug    = sanitize_title( $label );
		$required_attr = ( isset( $atts['required'] ) && $atts['required'] ) ? 'required' : '';

		ob_start();

		$this->render_field_label( $atts, $label );

		?>

		<input type="text" id="<?php echo esc_attr( sanitize_title( $label ) ); ?>" name="field-<?php echo esc_attr( $label_slug ); ?>[value]" <?php echo esc_attr( $required_attr ); ?> />

		<?php

		return ob_get_clean();

	}

	/**
	 * Render the email field
	 *
	 * @param  array $atts    Block attributes.
	 * @param  mixed $content Block content.
	 *
	 * @return mixed Markup for the email field.
	 */
	public function render_field_email( $atts, $content ) {

		$label         = isset( $atts['label'] ) ? $atts['label'] : __( 'Email', 'coblocks' );
		$label_slug    = sanitize_title( $label );
		$required_attr = ( isset( $atts['required'] ) && $atts['required'] ) ? 'required' : '';

		ob_start();

		$this->render_field_label( $atts, $label );

		?>

		<input type="email" id="<?php echo esc_attr( $label_slug ); ?>" name="field-<?php echo esc_attr( $label_slug ); ?>[value]" <?php echo esc_attr( $required_attr ); ?> />

		<?php

		return ob_get_clean();

	}

	/**
	 * Render the textarea field
	 *
	 * @param  array $atts    Block attributes.
	 * @param  mixed $content Block content.
	 *
	 * @return mixed Markup for the textarea field.
	 */
	public function render_field_textarea( $atts, $content ) {

		$label         = isset( $atts['label'] ) ? $atts['label'] : __( 'Message', 'coblocks' );
		$label_slug    = sanitize_title( $label );
		$required_attr = ( isset( $is_required ) && $is_required ) ? 'required' : '';

		ob_start();

		$this->render_field_label( $atts, $label );

		?>

		<textarea name="field-<?php echo esc_attr( $label_slug ); ?>[value]" id="<?php echo esc_attr( $label_slug ); ?>" rows="20"></textarea>

		<?php

		return ob_get_clean();

	}

	/**
	 * Generate the form field label.
	 *
	 * @param  array $atts Block attributes.
	 *
	 * @return mixed Form field label markup.
	 */
	private function render_field_label( $atts, $field_label ) {

		$label      = isset( $atts['label'] ) ? $atts['label'] : $field_label;
		$label_slug = sanitize_title( $label );

		/**
		 * Filter the required text in the field label.
		 *
		 * @param string $field_label Form field label text.
		 */
		$required_text  = apply_filters( 'coblocks_form_label_required_text', __( '(required)', 'coblocks' ), $field_label );
		$required_attr  = ( isset( $atts['required'] ) && $atts['required'] ) ? 'required' : '';
		$required_label = empty( $required_attr ) ? '' : sprintf( ' <span class="required"><small>%s</small></span>', $required_text );

		?>

		<label for="<?php echo esc_attr( $label_slug ); ?>"><?php echo esc_html( $label ); ?><?php echo $required_label; ?></label>
		<input type="hidden" name="field-<?php echo esc_attr( $label_slug ); ?>[label]" value="<?php echo esc_html( $label ); ?>">

		<?php

	}

	/**
	 * Render the form submit button.
	 *
	 * @param  array $atts Block attributes.
	 *
	 * @return mixed Form submit button markup.
	 */
	private function render_submit_button( $atts ) {

		$btn_text  = isset( $atts['submitButtonText'] ) ? $atts['submitButtonText'] : __( 'Submit', 'coblocks' );
		$btn_class = isset( $atts['submitButtonClasses'] ) ? $atts['submitButtonClasses'] : '';
		$styles    = '';

		if ( isset( $atts['customBackgroundButtonColor'] ) ) {

			$styles .= "background-color: {$atts['customBackgroundButtonColor']};";

		}

		if ( isset( $atts['customTextButtonColor'] ) ) {

			$styles .= "color: {$atts['customTextButtonColor']};";

		}

		if ( ! empty( $styles ) ) {

			$styles = " style='{$styles}'";

		}

		?>

		<button type="submit" class="<?php echo esc_attr( $btn_class ); ?>"<?php echo $styles; ?>><?php echo esc_html( $btn_text ); ?></button>

		<?php

	}

	/**
	 * Process the form submission
	 *
	 * @return null
	 */
	public function process_form_submission( $atts ) {

		$form_submission = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( ! $form_submission || 'coblocks-form-submit' !== $form_submission ) {

			return;

		}

		$nonce = filter_input( INPUT_POST, 'form-submit', FILTER_SANITIZE_STRING );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'coblocks-form-submit' ) ) {

			return;

		}

		$post_id    = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$post_title = get_bloginfo( 'name' ) . ( ( false === $post_id ) ? '' : sprintf( ' - %s', get_the_title( $post_id ) ) );

		$to      = isset( $atts['to'] ) ? sanitize_email( $atts['to'] ) : get_option( 'admin_email' );
		$subject = isset( $atts['subject'] ) ? sanitize_text_field( $atts['subject'] ) : $post_title;

		unset( $_POST['form-submit'], $_POST['_wp_http_referer'], $_POST['action'], $_POST['form-hash'] );

		$this->email_content = '<ul>';

		foreach ( $_POST as $key => $data ) {

			$this->email_content .= '<li>' . sanitize_text_field( $data['label'] ) . ': ' . sanitize_text_field( $data['value'] ) . '</li>';

		}

		$this->email_content .= '</ul>';

		/**
		 * Filter the email to
		 *
		 * @param string  $to      Email to.
		 * @param array   $_POST   Submitted form data.
		 * @param integer $post_id Current post ID.
		 */
		$to = apply_filters( 'coblocks_form_email_to', $to, $_POST, $post_id );

		/**
		 * Filter the email subject
		 *
		 * @param string  $subject Email subject.
		 * @param array   $_POST   Submitted form data.
		 * @param integer $post_id Current post ID.
		 */
		$subject = apply_filters( 'coblocks_form_email_subject', $subject, $_POST, $post_id );

		/**
		 * Filter the form email content.
		 *
		 * @param string  $this->email_content Email content.
		 * @param array   $_POST               Submitted form data.
		 * @param integer $post_id             Current post ID.
		 */
		$email_content = apply_filters( 'coblocks_form_email_content', $this->email_content, $_POST, $post_id );

		add_filter( 'wp_mail_content_type', [ $this, 'enable_html_email' ] );

		$email = wp_mail( $to, $subject, $email_content );

		remove_filter( 'wp_mail_content_type', [ $this, 'enable_html_email' ] );

		/**
		 * Fires when a form is submitted.
		 *
		 * @param array   $_POST User submitted form data.
		 * @param array   $atts  Form block attributes.
		 * @param boolean $email True when email sends, else false.
		 */
		do_action( 'coblocks_form_submit', $_POST, $atts, $email );

		return $email;

	}

	/**
	 * Enable HTML emails
	 *
	 * @return string HTML content type header
	 */
	public function enable_html_email() {

		return 'text/html';

	}

	/**
	 * Display the form success data
	 *
	 * @return mixed Markup for a preview of the submitted data
	 */
	public function success_message() {

		/**
		 * Filter the success message after a form submission
		 *
		 * @param mixed   Success message markup.
		 * @param integer Current post ID.
		 */
		$success_message = apply_filters(
			'coblocks_form_email_content',
			sprintf(
				'<blockquote>%s</blockquote>',
				wp_kses_post( $this->email_content )
			),
			get_the_ID()
		);

		ob_start();

		echo wp_kses_post( $success_message );

		// Prevent a page refresh form resubmitting the form
		?>

		<script type="text/javascript">
		if ( window.history.replaceState ) {
			window.history.replaceState( null, null, window.location.href );
		}
		</script>

		<?php

		return ob_get_clean();

	}

}

new CoBlocks_Form();