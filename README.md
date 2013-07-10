Cinder
======

Cinder is a Mozilla Licensed PHP ORM Layer Written By [Chareice](http://weibo.com/chareice).

Installing Cinder using Composer
-----
1. Add `"cinder/cinder"` as dependency in your project's `composer.json` file.

    ```json
    {
        "require": {
            "cinder/cinder": "dev-master"
        }
    }
    ```
1. Download and install Composer.

    `curl -s "http://getcomposer.org/installer" | php`

1. Install your dependencies.

    `php composer.phar install`

1. Require Composer's autoloader by adding the following line to your code's bootstrap process.

    `require '/path/to/vendor/autoload.php';`

Usage
-----
1. At first, User must configure the connection to Database using PDO Style:

  ```php
  <?php
  require "vendor/autoload.php";
  
  Cinder\Cinder::Config("mysql:host=localhost;dbname=app_test","root","pass");
  ```
  
1. Then User Can manipulate Cinder By feed it a `$options` variable like this:

  ```php
  $options = array(
        "table"   => "article",
        "primary" => "id",
        "value"   => "1"
  );
  $article = Cinder\Cinder::getInstance($options);
  ```
  Now `$article` variable is the ORM Object that mapping to your article table row which id equal 1.
  
