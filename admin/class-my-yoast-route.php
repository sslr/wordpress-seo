<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Admin
 */

/**
 * Represents the route for MyYoast.
 */
class WPSEO_MyYoast_Route implements WPSEO_WordPress_Integration {

	/**
	 * @var string
	 */
	const PAGE_IDENTIFIER = 'wpseo_myyoast';

	/**
	 * The instance of the MyYoast client.
	 *
	 * @var WPSEO_MyYoast_Client
	 */
	protected $client;

	/**
	 * Sets the hooks when the user has enough rights and is on the right page.
	 *
	 * @return void
	 */
	public function register_hooks() {
		$route = filter_input( INPUT_GET, 'page' );
		if ( ! ( $this->is_myyoast_route( $route ) && $this->can_access_route() ) ) {
			return;
		}

		if ( ! $this->is_valid_action( $this->get_action() ) ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'register_route' ) );
		add_action( 'admin_init', array( $this, 'handle_route' ) );
	}

	/**
	 * Registers the page for the MyYoast route.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public function register_route() {
		add_dashboard_page(
			'', // Is empty because we don't render a page.
			'', // Is empty because we don't want a menu item.
			'wpseo_manage_options',
			self::PAGE_IDENTIFIER
		);
	}

	/**
	 * Abstracts the action from the URL and follows the appropriate route.
	 *
	 * @return void
	 */
	public function handle_route() {
		$action = $this->get_action();

		if ( ! method_exists( $this, $action ) ) {
			return;
		}

		// Dynamically call the method.
		$this->$action();
	}

	/**
	 * Checks if the current page is the MyYoast route.
	 *
	 * @param string $route The MyYoast route.
	 *
	 * @return bool True when url is the MyYoast route.
	 */
	protected function is_myyoast_route( $route ) {
		return ( $route === self::PAGE_IDENTIFIER );
	}

	/**
	 * Compares an action to a list of allowed actions to see if it is valid.
	 *
	 * @param string $action The action to check.
	 *
	 * @return bool True if the action is valid.
	 **/
	protected function is_valid_action( $action ) {
		$allowed_actions = array( 'connect', 'authorize' );

		return in_array( $action, $allowed_actions, true );
	}

	/**
	 * Connects to MyYoast and generates a new clientId.
	 *
	 * @return void
	 */
	protected function connect() {
		$client_id = $this->generate_uuid();

		$this->save_client_id( $client_id );

		$this->redirect(
			'https://my.yoast.com/connect',
			array(
				'url'          => WPSEO_Utils::get_home_url(),
				'client_id'    => $client_id,
				'extensions'   => $this->get_extensions(),
				'redirect_url' => admin_url( 'admin.php?page=' . WPSEO_Admin::PAGE_IDENTIFIER ),
				'credentials_url' => rest_url( 'yoast/v1/myyoast/connect' ),
			)
		);
	}

	/**
	 * Redirects the user to the oAuth authorization page.
	 *
	 * @return void.
	 */
	protected function authorize() {
		$client = $this->get_client();

		if ( ! $client->has_configuration() ) {
			return;
		}

		$this->redirect(
			$client->get_provider()->getAuthorizationUrl()
		);
	}

	/**
	 * Saves the client id.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $client_id The client id to save.
	 *
	 * @return void
	 */
	protected function save_client_id( $client_id ) {
		$this->get_client()->save_configuration(
			array(
				'clientId' => $client_id,
			)
		);
	}

	/**
	 * Creates a new MyYoast Client instance.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return WPSEO_MyYoast_Client Instance of the myyoast client.
	 */
	protected function get_client() {
		if ( ! $this->client ) {
			$this->client = new WPSEO_MyYoast_Client();
		}

		return $this->client;
	}

	/**
	 * Abstracts the action from the url.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return string The action from the url.
	 */
	protected function get_action() {
		return filter_input( INPUT_GET, 'action' );
	}

	/**
	 * Retrieves a list the activated extensions.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array The extensions.
	 */
	protected function get_extensions() {
		$addon_manager = new WPSEO_Addon_Manager();

		return array_keys( $addon_manager->get_subscriptions_for_active_addons() );

	}

	/**
	 * Generates an URL-encoded query string, redirects there.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $url        The url to redirect to.
	 * @param array  $query_args The additional arguments to build the url from.
	 *
	 * @return void
	 */
	protected function redirect( $url, $query_args = array() ) {
		if ( ! empty( $query_args ) ) {
			$url .= '?' . http_build_query( $query_args );
		}

		wp_redirect( $url );
		exit;
	}

	/**
	 * Checks if current user is allowed to access the route.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return bool True when current user has rights to manage options.
	 */
	protected function can_access_route() {
		return WPSEO_Utils::has_access_token_support() && current_user_can( 'wpseo_manage_options' );
	}

	/**
	 * Generates an unique user id.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return string The generated unique user id.
	 */
	protected function generate_uuid() {
		return wp_generate_uuid4();
	}
}
