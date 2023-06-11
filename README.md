# smartCARS Native
Welcome to smartCARS Native, a native implementation of the smartCARS 3 API directly within phpVMS 7 via a module.

This module was created to address proper phpVMS 7 best practices for handling data, which are not present in the 1st
party solution, located at https://github.com/invernyx/smartcars-3-public-api.

With this module installed, you will gain additional functionality not present in the 1st party solution, including:

* Discord/Email Notifications
* Auto-Accepting PIREP Rules Followed
* Under the hood changes to the data handling to conform to the phpVMS 7 schema.

## Installation
Move this folder into your `modules` folder and verify that the folder is named `SmartCARSNative`.

Then, using the Admin Interface, go to Modules and Enable the module.

Finally, enter the following url for your community in smartCARS Central:
```text
https://yourdomainhere.com/api/smartcarsnative/
```
## Future Goals
Eventually, this API will be expanded with additional endpoints and features which will coincide with the release of a
custom flight center plugin.
