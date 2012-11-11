<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
* Controller helper class, contains methods that do things for controllers
* @package phpBB3
*/
class phpbb_controller_helper
{
	/**
	* Container
	* @var ContainerBuilder
	*/
	protected $container;

	/**
	* Template object
	* @var phpbb_template
	*/
	protected $template;

	/**
	* phpBB Root Path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* PHP Extension
	* @var string
	*/
	protected $php_ext;

	/**
	* Base URL
	* @var array
	*/
	protected $url_base;

	/**
	* Constructor
	*
	* @param ContainerBuilder $container DI Container
	*/
	public function __construct(ContainerBuilder $container)
	{
		$this->container = $container;

		$this->template = $this->container->get('template');
		$this->phpbb_root_path = $this->container->getParameter('core.root_path');
		$this->php_ext = $this->container->getParameter('core.php_ext');
	}

	/**
	* Automate setting up the page and creating the response object.
	*
	* @param string $handle The template handle to render
	* @param string $page_title The title of the page to output
	* @param int $status_code The status code to be sent to the page header
	* @return Response object containing rendered page
	*/
	public function render($template_file, $page_title = '', $status_code = 200)
	{
		page_header($page_title);

		$this->template->set_filenames(array(
			'body'	=> $template_file,
		));

		page_footer(true, false, false);

		return new Response($this->template->return_display('body'), $status_code);
	}

	/**
	* Easily generate a URL
	*
	* @param array $url_parts Each array element is a 'folder'
	* 		i.e. array('my', 'ext') maps to ./app.php/my/ext
	* @param mixed $query The Query string, passed directly into the second
	*		argument of append_sid()
	* @return string A URL that has already been run through append_sid()
	*/
	public function url(array $url_parts, $query = '')
	{
		return append_sid($this->phpbb_root_path . $this->url_base . implode('/', $url_parts), $query);
	}

	/**
	* Set base to prepend to urls generated by url()
	* This allows extensions to have a certain 'directory' under which
	* all their pages are served, but not have to type it every time
	*
	* @param array $url_parts Each array element is a 'folder'
	*		i.e. array('my', 'ext') maps to ./app.php/my/ext
	* @return null
	*/
	public function set_url_base(array $url_parts)
	{
		$this->url_base = !empty($url_parts) ? implode('/', $url_parts) . '/' : '';
	}

	/**
	* Output an error, effectively the same thing as trigger_error
	*
	* @param string $code The error code (e.g. 404, 500, 503, etc.)
	* @param string $message The error message
	* @return Response A Reponse instance
	*/
	public function error($code = 500, $message = '')
	{
		$this->template->assign_vars(array(
			'MESSAGE_TEXT'	=> $message,
			'MESSAGE_TITLE'	=> $this->container->get('user')->lang('INFORMATION'),
		));

		return $this->render('message_body.html', $this->container->get('user')->lang('INFORMATION'), $code);
	}
}
