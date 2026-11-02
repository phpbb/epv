<?php
/**
 * @package phpBB Extension - Test
 */

namespace epv\test;

class dynamic_method_call
{
	public function run($methods, $step)
	{
		$removed = $this->store->{$methods[$step]}(self::DELETE_STEP);
		$other = $this->store->{$methods['a']}();
		$named = $this->store->delete_step();
		return $removed;
	}
}
