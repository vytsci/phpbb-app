<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\install\controller;

use phpbb\install\helper\config;
use phpbb\install\helper\navigation\navigation_provider;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use phpbb\install\helper\iohandler\factory;
use phpbb\install\controller\helper;
use phpbb\template\template;
use phpbb\request\request_interface;
use phpbb\install\installer;
use phpbb\language\language;

/**
 * Controller for installing phpBB
 */
class install
{
	/**
	 * @var helper
	 */
	protected $controller_helper;

	/**
	 * @var config
	 */
	protected $installer_config;

	/**
	 * @var factory
	 */
	protected $iohandler_factory;

	/**
	 * @var navigation_provider
	 */
	protected $menu_provider;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var template
	 */
	protected $template;

	/**
	 * @var request_interface
	 */
	protected $request;

	/**
	 * @var installer
	 */
	protected $installer;

	/**
	 * Constructor
	 *
	 * @param helper 				$helper
	 * @param config				$install_config
	 * @param factory 				$factory
	 * @param navigation_provider	$nav_provider
	 * @param language				$language
	 * @param request_interface		$request
	 * @param installer				$installer
	 */
	public function __construct(helper $helper, config $install_config, factory $factory, navigation_provider $nav_provider, language $language, template $template, request_interface $request, installer $installer)
	{
		$this->controller_helper	= $helper;
		$this->installer_config		= $install_config;
		$this->iohandler_factory	= $factory;
		$this->menu_provider		= $nav_provider;
		$this->language				= $language;
		$this->template				= $template;
		$this->request				= $request;
		$this->installer			= $installer;
	}

	/**
	 * Controller logic
	 *
	 * @return Response|StreamedResponse
	 */
	public function handle()
	{
		// @todo check that phpBB is not already installed

		$this->template->assign_vars(array(
			'U_ACTION' => $this->controller_helper->route('phpbb_installer_install'),
		));

		// Set up input-output handler
		if ($this->request->is_ajax())
		{
			$this->iohandler_factory->set_environment('ajax');
		}
		else
		{
			$this->iohandler_factory->set_environment('nojs');
		}

		// Set the appropriate input-output handler
		$this->installer->set_iohandler($this->iohandler_factory->get());

		// Set up navigation
		$nav_data = $this->installer_config->get_navigation_data();
		/** @var \phpbb\install\helper\iohandler\iohandler_interface $iohandler */
		$iohandler = $this->iohandler_factory->get();

		// Set active navigation stage
		if (isset($nav_data['active']) && is_array($nav_data['active']))
		{
			$iohandler->set_active_stage_menu($nav_data['active']);
			$this->menu_provider->set_nav_property($nav_data['active'], array(
				'selected'	=> true,
				'completed'	=> false,
			));
		}

		// Set finished navigation stages
		if (isset($nav_data['finished']) && is_array($nav_data['finished']))
		{
			foreach ($nav_data['finished'] as $finished_stage)
			{
				$iohandler->set_finished_stage_menu($finished_stage);
				$this->menu_provider->set_nav_property($finished_stage, array(
					'selected'	=> false,
					'completed'	=> true,
				));
			}
		}

		if ($this->request->is_ajax())
		{
			$installer = $this->installer;
			$response = new StreamedResponse();
			$response->setCallback(function() use ($installer) {
				$installer->run();
			});

			return $response;
		}
		else
		{
			// Determine whether the installation was started or not
			if (true)
			{
				// Set active stage
				$this->menu_provider->set_nav_property(
					array('install', 0, 'introduction'),
					array(
						'selected'	=> true,
						'completed'	=> false,
					)
				);

				// If not, let's render the welcome page
				$this->template->assign_vars(array(
					'SHOW_INSTALL_START_FORM'	=> true,
					'TITLE'						=> $this->language->lang('INSTALL_INTRO'),
					'CONTENT'					=> $this->language->lang('INSTALL_INTRO_BODY'),
				));
				return $this->controller_helper->render('installer_install.html', 'INSTALL');
			}

			// @todo: implement no js controller logic
		}
	}
}
