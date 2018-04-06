# ICONIFY
To make any windows local folder, iconify!

The point of writing this class is to make an icon inside of any local windows folder you wish.
ICONIFY gets the directory, automatically searches inside the directory for any image. Then grabs the first one and makes an standard 256*256 .ico file from the image and finally change the folder icon to the .ico file.

This Class is super easy to use!
You just create the instant of class, give the dir path and the run.



# Example
```php
require_once 'iconify.class.php';

$dir = "<any windows directory>";
  
$iconify = new ICONIFY($dir);
$iconify->run();
 ```
 
 # Thanks
 
 I want to thank [chris Jean](https://github.com/chrisbliss18) for creating [php-ico](https://github.com/chrisbliss18/php-ico)
 I use php-ico in my class. It makes my work so simple.
