# Native for phpVMS 7

Welcome to Native, a native implementation of the smartCARS 3 API directly within phpVMS 7 via a module.

This module was created to address proper phpVMS 7 best practices for handling data, which are not present in the 1st
party solution, located at https://github.com/invernyx/smartcars-3-public-api.

With this module installed, you will gain additional functionality not present in the 1st party solution, including:

* Discord/Email Notifications
* Auto-Accepting PIREP Rules Followed
* Fully Working Live Map

## Requirements

* php 8.2 or greater
* phpVMS 7 latest development branch
* "phpVMS 7 Native Flight Center" plugin, available on smartCARS Central, installed to your VA, replacing the default flight-center.

## Installation
Move this folder into your `modules` folder and verify that the folder is named `SmartCARSNative`.

Then, using the Admin Interface, go to Modules and Enable the module.

Finally, enter the following url for your community in smartCARS Central:
```text
https://yourdomainhere.com/api/smartcarsnative/
```

While you're in smartCARS Central, install the "phpVMS 7 Native Flight Center", which replaces the included flight center solution.
## Future Goals
Right now we're keeping the scope to phpVMS 7's base feature set and to make sure smartCARS works with all the base features. Additional
features developed by 3rd parties will be outside the scope.

Support: https://discord.gg/BvPbQby9Mq
