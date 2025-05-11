# Extension Pre-Validator

[![Build Status](https://github.com/phpbb/epv/actions/workflows/tests.yml/badge.svg)](https://github.com/phpbb/epv/actions)

This repository contains the extension pre-validator, used for pre validating extensions when submittion to the database at phpBB.com.

Please note that EPV requires at least PHP 7.2 

## Using EPV

1. Clone your fork of this repository.
2. Install composer dependencies:
    ```sh
    $ php composer.phar install
    ```
3. Run EPV on a phpBB extension from the CLI:
    ```sh
    # Run EPV on a Git repository (at any repository hosting site)
    php src/EPV.php run --git="https://github.com/repo-org/repo-name.git"

    # Run EPV on a GitHub repository
    php src/EPV.php run --github="repo-org/repo-name"

    # Run EPV on a local directory
    php src/EPV.php run --dir="/path/to/extension"
    ```

> The `--branch` option can target a specific branch of a repository.
> 
> The `--debug` option will output additional debugging information.

You can also use EPV online at [phpBB.com](https://www.phpbb.com/extensions/epv/)

phpBB's Customisation Database (Titania) will run EPV on any submissions at phpBB.com as well.

## Maintenance and contributing

To contribute fork the repo, make your changes in a feature branch and send a pull request.

The site is maintained by the [phpBB Extensions Team](https://www.phpbb.com/community/memberlist.php?mode=group&g=7331)

Should you wish to report a bug report it at [Issue tracker](https://github.com/phpbb/epv/issues)

## License
[GNU General Public License v2](https://opensource.org/licenses/GPL-2.0)

By contributing you agree to assign copyright of your code to phpBB Limited.

See `LICENSE` for the full license.
