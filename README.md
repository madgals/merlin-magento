# merlin-magento
Magento extension that employs the Blackbird API to utilize the Merlin search engine.

This extension implements the merlin.js library, more information about it can be found at: http://www.blackbird.am/docs/

More information and signup for the Blackbird services can be found at: http://www.blackbird.am/
 
Install via Shell script
----------------
You can use the built in installer from the package, unzip and run the following command.
```bash
$ bash merlin-installer [magento_install_path]
```


Install via Composer
----------------

You can install this module using composer if you have the Magento Composer Installer configured (https://github.com/Cotya/magento-composer-installer) 

```bash
$ composer require blackbirdtech/merlin-magento
```

Install via Modman
----------------

You can install this module using [Colin Mollenhour's](https://github.com/colinmollenhour) [Modman tool](https://github.com/colinmollenhour/modman).

```bash
$ modman init
$ modman clone https://github.com/blackbirdtech/merlin-magento
```
