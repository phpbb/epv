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


use Phpbb\Epv\Files\FileInterface;

interface YmlFileInterface extends FileInterface
{
	/**
	 * Get an array with the data in the yaml file.
	 *
	 * @return array parsed yaml file
	 */
	public function getYaml();
}
