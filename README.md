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

## License

This plugin is licensed under the GPL v3 License (for further information, see [LICENSE](LICENSE)).

## Libraries used

* Guzzle: an extensible PHP HTTP client – MIT license – https://github.com/guzzle/guzzle
* [tud-dashboard](https://github.com/VerDatAs/tud-dashboard): the frontend application of the dashboard for the assistance system – GPL v3 license
  * Retrieve the code and license information here: [templates/main.js](templates/main.js)
  * The following libraries are used by [tud-dashboard](https://github.com/VerDatAs/tud-dashboard):

|    Name    |   Version  |   License  |     URL    |
| ---------- | ---------- | ---------- | ---------- |
| @babel/parser | 7.22.7 | MIT | https://github.com/babel/babel |
| @bpmn-io/diagram-js-ui | 0.2.2 | MIT | https://github.com/bpmn-io/diagram-js-ui |
| @fortawesome/fontawesome-common-types | 6.4.2 | MIT | https://github.com/FortAwesome/Font-Awesome |
| @fortawesome/fontawesome-svg-core | 6.4.2 | MIT | https://github.com/FortAwesome/Font-Awesome |
| @fortawesome/free-solid-svg-icons | 6.4.2 | (CC-BY-4.0 AND MIT) | https://github.com/FortAwesome/Font-Awesome |
| @fortawesome/vue-fontawesome | 3.0.3 | MIT | https://github.com/FortAwesome/vue-fontawesome |
| @jridgewell/sourcemap-codec | 1.4.15 | MIT | https://github.com/jridgewell/sourcemap-codec |
| @types/web-bluetooth | 0.0.18 | MIT | https://github.com/DefinitelyTyped/DefinitelyTyped |
| @vue/compiler-core | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vue/compiler-dom | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vue/compiler-sfc | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vue/compiler-ssr | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vue/devtools-api | 6.5.0 | MIT | https://github.com/vuejs/vue-devtools |
| @vue/reactivity-transform | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vue/reactivity | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vue/runtime-core | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vue/runtime-dom | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vue/server-renderer | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vue/shared | 3.3.4 | MIT | https://github.com/vuejs/core |
| @vueuse/core | 10.5.0 | MIT | https://github.com/vueuse/vueuse |
| @vueuse/core | 7.7.1 | MIT | https://github.com/vueuse/vueuse |
| @vueuse/metadata | 10.5.0 | MIT | https://github.com/vueuse/vueuse |
| @vueuse/shared | 10.5.0 | MIT | https://github.com/vueuse/vueuse |
| @vueuse/shared | 7.7.1 | MIT | https://github.com/vueuse/vueuse |
| asynckit | 0.4.0 | MIT | https://github.com/alexindigo/asynckit |
| axios | 1.4.0 | MIT | https://github.com/axios/axios |
| clsx | 1.2.1 | MIT | https://github.com/lukeed/clsx |
| combined-stream | 1.0.8 | MIT | https://github.com/felixge/node-combined-stream |
| component-event | 0.2.1 | MIT | https://github.com/component/event |
| csstype | 3.1.2 | MIT | https://github.com/frenic/csstype |
| de-indent | 1.0.2 | MIT | https://github.com/yyx990803/de-indent |
| delayed-stream | 1.0.0 | MIT | https://github.com/felixge/node-delayed-stream |
| diagram-js-direct-editing | 2.0.0 | MIT | https://github.com/bpmn-io/diagram-js-direct-editing |
| diagram-js | 11.13.1 | MIT | https://github.com/bpmn-io/diagram-js |
| didi | 9.0.2 | MIT | https://github.com/nikku/didi |
| domify | 1.4.1 | MIT | https://github.com/component/domify |
| estree-walker | 2.0.2 | MIT | https://github.com/Rich-Harris/estree-walker |
| follow-redirects | 1.15.2 | MIT | https://github.com/follow-redirects/follow-redirects |
| form-data | 4.0.0 | MIT | https://github.com/form-data/form-data |
| hammerjs | 2.0.8 | MIT | https://github.com/hammerjs/hammer.js |
| he | 1.2.0 | MIT | https://github.com/mathiasbynens/he |
| htm | 3.1.1 | Apache-2.0 | https://github.com/developit/htm |
| ids | 1.0.5 | MIT | https://github.com/bpmn-io/ids |
| inherits-browser | 0.1.0 | ISC | https://github.com/nikku/inherits-browser |
| inherits | 2.0.4 | ISC | https://github.com/isaacs/inherits |
| js-base64 | 3.7.5 | BSD-3-Clause | https://github.com/dankogai/js-base64 |
| magic-string | 0.30.1 | MIT | https://github.com/rich-harris/magic-string |
| mime-db | 1.52.0 | MIT | https://github.com/jshttp/mime-db |
| mime-types | 2.1.35 | MIT | https://github.com/jshttp/mime-types |
| min-dash | 4.1.1 | MIT | https://github.com/bpmn-io/min-dash |
| min-dom | 4.1.0 | MIT | https://github.com/bpmn-io/min-dom |
| moddle-xml | 10.1.0 | MIT | https://github.com/bpmn-io/moddle-xml |
| moddle | 6.2.3 | MIT | https://github.com/bpmn-io/moddle |
| nanoid | 3.3.6 | MIT | https://github.com/ai/nanoid |
| object-refs | 0.3.0 | MIT | https://github.com/bpmn-io/object-refs |
| path-intersection | 2.2.1 | MIT | https://github.com/bpmn-io/path-intersection |
| picocolors | 1.0.0 | ISC | https://github.com/alexeyraspopov/picocolors |
| pinia-plugin-persistedstate | 3.2.0 | MIT | https://github.com/prazdevs/pinia-plugin-persistedstate |
| pinia | 2.1.6 | MIT | https://github.com/vuejs/pinia |
| postcss | 8.4.27 | MIT | https://github.com/postcss/postcss |
| postit-js-core | 1.1.0 | MIT | https://github.com/pinussilvestrus/postit-js |
| preact | 10.16.0 | MIT | https://github.com/preactjs/preact |
| proxy-from-env | 1.1.0 | MIT | https://github.com/Rob--W/proxy-from-env |
| saxen | 8.1.2 | MIT | https://github.com/nikku/saxen |
| source-map-js | 1.0.2 | BSD-3-Clause | https://github.com/7rulnik/source-map-js |
| tiny-svg | 3.0.1 | MIT | https://github.com/bpmn-io/tiny-svg |
| typescript | 4.8.4 | Apache-2.0 | https://github.com/Microsoft/TypeScript |
| vue-demi | 0.13.11 | MIT | https://github.com/antfu/vue-demi |
| vue-demi | 0.14.5 | MIT | https://github.com/antfu/vue-demi |
| vue-demi | 0.14.6 | MIT | https://github.com/antfu/vue-demi |
| vue-multiselect | 3.0.0-beta.2 | MIT | https://github.com/suadelabs/vue-multiselect |
| vue-template-compiler | 2.7.14 | MIT | https://github.com/vuejs/vue |
| vue | 3.3.4 | MIT | https://github.com/vuejs/core |
| vuejs-confirm-dialog | 0.5.1 | MIT | https://github.com/harmyderoman/vuejs-confirm-dialog |
