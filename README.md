## Commission Calculator

#### Dependencies
  * PHP >= 7.2
  * Composer
#### Libraries included
  * [brick/money](https://github.com/brick/money)
  * [nesbot/carbon](https://github.com/briannesbitt/Carbon)
  * [sebastianbergmann/phpunit](https://github.com/sebastianbergmann/phpunit)
  * [squizlabs/php_codesniffer](https://github.com/squizlabs/php_codesniffer)
### Instructions
  * Run `composer install` to install all dependencies and map the class autoloading.
  * Run `./vendor/bin/phpcs --standard=PSR2 src/` to validate coding style.
  * Run `./vendor/bin/phpunit` to execute the available test cases.
  * Run `php main.php data/entries.csv` to execute the main script.