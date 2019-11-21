<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests\Tests;

use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\TestRunner;

class epv_test_validate_directory_structure extends BaseTest
{
	private $strict = false;

	const LICENSE_SIMILARITY_THRESHOLD = 0.99;

	const LICENSE_CLOSING_WORDS = 'END OF TERMS AND CONDITIONS';

	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania, $opendir)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania, $opendir);

        $this->directory = true;
    }

	public function validateDirectory(array $dirList, $validateLicenseContents = true)
	{
		$files = array(
			'license'  => false,
			'composer' => false,
		);
		foreach ($dirList as $dir)
		{
			switch (strtolower(basename($dir)))
			{
				case 'license.txt':
					$files['license'] = true;

					if (basename($dir) != strtolower(basename($dir)))
					{
						$this->output->addMessage(Output::WARNING, 'The name of license.txt should be completely lowercase.');
					}

					if ($validateLicenseContents)
					{
						$licenseSimilarity = $this->licenseSimilarity(TestRunner::getResource('gpl-2.0.txt'), $dir);

						if ($licenseSimilarity !== false && $licenseSimilarity < self::LICENSE_SIMILARITY_THRESHOLD)
						{
							$msg = 'Similarity of the license.txt to the GPL-2.0 is too low. Expected is %s%% or above but got %s%%';
							$expectedPercent = self::LICENSE_SIMILARITY_THRESHOLD * 100;
							// Truncate after 2nd decimal
							$actualPercent = floor($licenseSimilarity * 10000) / 100;

							$this->output->addMessage(Output::WARNING, sprintf($msg, $expectedPercent, $actualPercent));
						}
					}

					// Do not check license.txt location. Will give false positives in case packaging is wrong directory wise.
					break;

				case 'composer.json':
					$files['composer'] = true;

					if (basename($dir) != strtolower(basename($dir)))
					{
						$this->output->addMessage(Output::WARNING, 'The name of composer.json should be completely lowercase.');
					}
					$sp    = str_replace('\\', '/', $dir);
					$sp    = str_replace(str_replace('\\', '/', dirname(dirname($this->opendir))), '', $sp);
					$sp    = str_replace('/composer.json', '', $sp);

					if (!empty($sp) && $sp[0] == '/')
					{
						// for some reason, there is a extra / on at least OS X
						$sp = substr($sp, 1, strlen($sp));
					}

					if ($this->namespace != $sp)
					{
						$this->output->addMessage(Output::ERROR,
							sprintf("Packaging structure doesn't meet the extension DB policies.\nExpected: %s\nGot: %s",
							$this->namespace, $sp));
					}
					break;
			}
		}

		if (!$files['license'])
		{
			$this->output->addMessage(Output::ERROR, 'Missing required license.txt file');
		}
	}

	public function testName()
	{
		return "Validate directory structure";
	}

	/**
	 * @param string $expectedLicenseFile Path to the file of the expected license
	 * @param string $extLicenseFile      Path to the extension's license.txt file
	 * @return bool|float
	 */
	public function licenseSimilarity($expectedLicenseFile, $extLicenseFile)
	{
		$expectedLicense = @file_get_contents($expectedLicenseFile);

		if ($expectedLicense === false)
		{
			$this->output->addMessage(Output::WARNING, 'Failed to load expected license file from ' . $expectedLicenseFile);
			return false;
		}

		$extLicense = @file_get_contents($extLicenseFile);

		if ($extLicense === false)
		{
			$this->output->addMessage(Output::WARNING, 'Failed to load extension license file from ' . $extLicense);
			return false;
		}

		// Remove everything after the closing words
		if (($closingWordsPos = strripos($extLicense, self::LICENSE_CLOSING_WORDS)) !== false)
		{
			$extLicense = substr($extLicense, 0, $closingWordsPos + strlen(self::LICENSE_CLOSING_WORDS));
		}

		// Remove all whitespaces
		$extLicense = preg_replace('/\s+/', '', $extLicense);
		$expectedLicense = preg_replace('/\s+/', '', $expectedLicense);

		return $this->diceCoefficient($expectedLicense, $extLicense);
	}

	/**
	 * Sørensen–Dice coefficient, case sensitive
	 *
	 * @param string $str1
	 * @param string $str2
	 * @return float a value between 0 and 1, 1 being exact match
	 */
	protected function diceCoefficient($str1, $str2)
	{
		$str1 = (string) $str1;
		$str2 = (string) $str2;

		if ($str1 === $str2)
		{
			return 1;
		}

		if (!strlen($str1) || !strlen($str2))
		{
			return 0;
		}

		$bi1 = $this->bigrams($str1);
		$bi2 = $this->bigrams($str2);

		sort($bi1);
		sort($bi2);

		$i = 0;
		$j = 0;
		$matches = 0;
		$len1 = sizeof($bi1);
		$len2 = sizeof($bi2);

		while ($i < $len1 && $j < $len2)
		{
			$cmp = strcmp($bi1[$i], $bi2[$j]);

			if ($cmp == 0)
			{
				$matches += 2;
				$i++;
				$j++;
			}
			else if ($cmp < 0)
			{
				$i++;
			}
			else
			{
				$j++;
			}
		}

		return $matches / ($len1 + $len2);
	}

	/**
	 * @param $str
	 * @return array
	 */
	protected function bigrams($str)
	{
		$bigrams = [];
		$len = strlen($str);

		for ($i = 0; $i < $len - 1; $i++)
		{
			$bigrams[] = $str[$i] . $str[$i + 1];
		}

		return $bigrams;
	}
}
