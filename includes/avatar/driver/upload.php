<?php
/**
*
* @package avatar
* @copyright (c) 2005, 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Handles avatars uploaded to the board
* @package avatars
*/
class phpbb_avatar_driver_upload extends phpbb_avatar_driver
{
	/**
	* Get the avatar url and dimensions
	*
	* @param $ignore_config Whether $user or global avatar visibility settings
	*        should be ignored
	* @return array Avatar data
	*/
	public function get_data($user_row, $ignore_config = false)
	{
		if ($ignore_config || $this->config['allow_avatar_upload'])
		{
			return array(
				'src' => $this->phpbb_root_path . 'download/file.' . $this->php_ext . '?avatar=' . $user_row['user_avatar'],
				'width' => $user_row['user_avatar_width'],
				'height' => $user_row['user_avatar_height'],
			);
		}
		else
		{
			return array(
				'src' => '',
				'width' => 0,
				'height' => 0,
			);
		}
	}
}
