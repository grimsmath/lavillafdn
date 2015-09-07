<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, EllisLab, Inc.
 * @license		http://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 2.8
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ExpressionEngine CSRF Library
 *
 * This class handles the CSRF protection for ExpressionEngine. You should
 * not need to use this library yourself.
 *
 * @package		ExpressionEngine
 * @subpackage	Core
 * @category	Core
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class Csrf {

	private $backend;
	private $request_token;
	private $session_token;

	const TOKEN_LENGTH = 40; // the token is always the sha1 of a random string

	public function __construct()
	{
		$session_id = ee()->session->userdata('session_id');
		$backend = ($session_id === 0) ? 'cookie' : 'database';

		require_once APPPATH.'libraries/csrf/Storage_backend_interface.php';
		require_once APPPATH.'libraries/csrf/'.ucfirst($backend).'.php';

		$class = 'Csrf_'.$backend;
		$this->backend = new $class;

		assert($this->backend instanceOf Csrf_storage_backend);
	}

	/**
	 * Get the user's token
	 *
	 * This is used to insert a token into forms and ajax requests.
	 *
	 * @return String user csrf token
	 */
	public function get_user_token()
	{
		return $this->fetch_session_token();
	}

	/**
	 * Access the csrf token timeout
	 *
	 * This can sometimes be useful to know when creating pages that may be open
	 * for a very long time.
	 *
	 * @return Integer token timeout [0 = no timeout]
	 */
	public function get_expiration()
	{
		return $this->backend->get_expiration();
	}

	/**
	 * Refresh the user's token
	 *
	 * This should generally be used any time you need to create a new token
	 * for a user. Definitely call this on login and logout.
	 *
	 * @return String new token
	 */
	public function refresh_token()
	{
		$token = random_string('encrypt');

		$this->backend->delete_token();
		$this->backend->store_token($token);

		return $token;
	}

	/**
	 * Check the csrf token for this request
	 *
	 * @return bool True/False for a valid or invalid token, respectively
	 */
	public function check()
	{
		// If secure forms is off we don't need to check
		if (bool_config_item('disable_csrf_protection'))
		{
			return TRUE;
		}

		// Exempt safe html methods (@see RFC2616)
		$safe = array('GET', 'HEAD', 'OPTIONS', 'TRACE');

		if (in_array(ee()->input->server('REQUEST_METHOD'), $safe))
		{
			return TRUE;
		}

		// Fetch data, these methods enforce token time limits
		$this->fetch_session_token();
		$this->fetch_request_token();

		// Main check
		if ($this->request_token === $this->session_token)
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Fetch the current request's token.
	 *
	 * We check both headers and post for legacy and new fields. We also check
	 * the token length to further limit the attacker's options.
	 *
	 * @return void
	 */
	private function fetch_request_token()
	{
		$token = FALSE;

		// Try to find a token in POST or headers. We need to support the legacy
		// XID field and header.
		if ( ! $token) $token = ee()->input->post('csrf_token');
		if ( ! $token) $token = ee()->input->server('HTTP_X_CSRF_TOKEN');
		if ( ! $token) $token = ee()->input->post('XID');
		if ( ! $token) $token = ee()->input->server('HTTP_X_EEXID');

		// Remove csrf information from the post array to make this feature
		// completely transparent to the developer.
		unset($_POST['csrf_token']);
		unset($_POST['XID']);

		// Reject failed tokens or tokens of bogus size
		if ( ! $token || strlen($token) != self::TOKEN_LENGTH)
		{
			$token = '';
		}

		$this->request_token = $token;
	}

	/**
	 * Fetch the current session token from the storage backend.
	 *
	 * Will only return tokens that are within the valid token timeout. If
	 * no token exists it will attempt to set one
	 *
	 * @return String Current user token as returned by the storage backend
	 */
	private function fetch_session_token()
	{
		if ( ! isset($this->session_token))
		{
			$this->session_token = $this->backend->fetch_token();
		}

		if ($this->session_token == '')
		{
			$this->session_token = $this->refresh_token();
		}

		return (string) $this->session_token;
	}
}