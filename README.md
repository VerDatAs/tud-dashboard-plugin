# VerDatAsDsh Plugin

The dashboard ILIAS plugin for the assistance system developed as part of the VerDatAs project.

The following requirements should be met:

* ILIAS 8.0 - 8.x
* PHP >= 8.0

## Installation

``` shell
# execute the following commands from your ILIAS root
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent
cd Customizing/global/plugins/Services/COPage/PageComponent
git clone https://github.com/VerDatAs/tud-dashboard-plugin.git VerDatAsDsh
# navigate to your ILIAS root
cd /var/www/html
composer du
```

Locate to `Administration | Extending ILIAS | Plugins` and install, configure and activate `VerDatAsDsh`.

## Configuration

Define the following settings:

* TAS-Backend URL (e.g., `https://tud-tas.example.com`)
* LRS-Type (i.e., an LRS type created in `Administration | Extending ILIAS | LRS`)
* Use vAPI (i.e., whether the vimotion API should be used to retrieve the course data [currently incomplete])
* Hide from students (i.e., whether the dashboard should be hidden for students)
* Retrieve course members (i.e., whether the course members should be retrieved from ILIAS and sent to the dashboard)

## Usage

* Navigate into a ILIAS course
* Open the "Customise Page" editor
* Insert VerDatAsDsh
* *Hint: Avoid copying VerDatAsDsh, as it can only be displayed once. Copying might crash your course.*

## Development

* If you use the ILIAS docker setup described [here](https://github.com/VerDatAs/all-ilias), which is located within the
same folder such as `tud-dashboard-plugin`, you can run `sh local_development.sh` to reload your changes made.

## License

This plugin is licensed under the GPL v3 License (for further information, see [LICENSE](LICENSE)).

## Libraries used by this plugin

* Guzzle: an extensible PHP HTTP client – MIT license
* [tud-dashboard](https://github.com/VerDatAs/tud-dashboard): the frontend application of the dashboard for the assistance system – (extended) GPL v3 license – retrieve the code and license information here: [templates/main.js](templates/main.js)
  * Please find the licenses of the third-party libraries used by the VerDatAs-Dashboard here: [templates/vendor.LICENSE.txt](templates/vendor.LICENSE.txt)
