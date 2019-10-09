<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;

class ArrayKeyVisitor extends NodeVisitorAbstract
{
	/**
	 * @var array
	 */
	private $keys;

	/**
	 * @param array $nodes
	 * @return void|null|Node[]
	 */
	public function beforeTraverse(array $nodes)
	{
		$this->keys = [];
	}

	/**
	 * @param Node $node
	 * @return void|null|int|Node
	 */
	public function enterNode(Node $node)
	{
        if ($node instanceof Array_)
		{
            foreach ($node->items as $item)
			{
				/** @var ArrayItem $item */
				if ($item->key instanceof String_)
				{
					$this->keys[] = $item->key->value;
				}
			}
        }
    }

	/**
	 * @return array
	 */
    public function get_array_keys()
	{
		return $this->keys;
	}
}
