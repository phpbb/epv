<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Files\Type;

use Phpbb\Epv\Files\BaseFile;
use Phpbb\Epv\Tests\Type;

class MigrationFile extends BaseFile implements MigrationFileInterface
{
	/**
	 * Get the file type for the specific file.
	 * @return int
	 */
	function getFileType()
	{
		return Type::TYPE_PHP | Type::TYPE_MIGRATION;
	}
}
