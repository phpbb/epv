Extension Pre-Validator
=======================

[![Build Status](https://travis-ci.org/phpbb/epv.png?branch=master)](https://travis-ci.org/phpbb/epv)

This repository contains the extension pre-validator, used for pre validating extensions when submittion to the database at phpBB.com.

Using EPV
---------

1. Clone the repository
1. Run:

    $ php composer.phar install

1. You can run EPV on three different methods from the CLI:
	* php src/EPV.php run --git="GIT_REPO"
	* php src/EPV.php run --github="GITHUB_NAME"
	* php src/EPV.php run --dir="LOCAL DIRECTORY"
1. You can use the --debug option to get some debug information.
1. You can also use EPV from soon [phpBB.com](https://www.phpbb.com/extensions/epv/)
1. Titania will check when submitting to phpBB.com using EPV as well.

License
-------
[GNU GPL v2](http://opensource.org/licenses/gpl-2.0)

By contributing you agree to assign copyright of your code to phpBB Limited.

See `LICENSE` for the full license.

Maintenance and contributing
----------------------------

To contribute fork the repo, make your changes in a feature branch and send a pull request.

The site is maintained by the [phpBB Extensions Team](https://www.phpbb.com/community/memberlist.php?mode=group&g=7331)

Should you wish to report a bug report it at [Issue tracker](https://github.com/phpbb/epv/issues)
