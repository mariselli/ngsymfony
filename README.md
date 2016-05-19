# Ng-Symfony
A pretty nice way to expose Angular UI Router states based on your Symfony2 routing system.

---

## Introduction
This bundle want to use the power of annotation for generate the state configuration fon [Angualr UI Router](https://github.com/angular-ui/ui-router).

## Installation

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:
```
composer require mariselli/ngsymfony
```

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
 <?php
    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                new Mariselli\NgSymfonyBundle\NgSymfonyBundle(),
            );

            // ...
        }

        // ...
    }
```

### Configure the FOSUserBundle

The next step is to configure the bundle to work with the specific needs of your application.

Add the following configuration to your config.yml file.

This is the basic configuration:
```yml
ng_symfony:
    module_name: "ngSymfony.states"
    constant_name: "$ngStates"
    file_path: "/Resources/ui-states.js"
```

It's also possible to filter what kind of url have to be scanned:
```yml
ng_symfony:
    module_name: "ngSymfony.states"
    constant_name: "$ngStates"
    file_path: "/Resources/ui-states.js"
    urls: ['/contact','/profile']
```

Sometime happen that we want to separate state base on path.
In this case is possible to declare `sections` property.
NOTE: the root properties `module_name, module_name, file_path, urls` will be ignored even if are mandatory.
```yml
ng_symfony:
    module_name: "ngSymfony.states"
    module_name: "$ngStates"
    file_path: "/Resources/ui-states.js"
    urls: ['/contact','/profile']
    sections:
        - { module_name: "ngSymfony.frontStates", constant_name: "$ngStatesFrontend", file_path: "/Resources/ui-states-frontend.js", urls: ["/app"] }
        - { module_name: "ngSymfony.backendStates", constant_name: "$ngStatesBackend", file_path: "/Resources/ui-states-backend.js", urls: ["/admin", "/staff"] }
```


## How to define state with Annotations

We have a page with this div:
```html
<div id="content-pane" ui-view></div>
```

Creating a state that simply represent a page:
```php
use Mariselli\NgSymfonyBundle\Annotation\UiRouterState;
// [...]

class DefaultController extends Controller
{
    // [...]
    
    /**
     * @Route("/start", name="start", options={"expose"=true})
     * @UiRouterState(view="start")
     */
    public function startAction()
    {
        return $this->render('default/start.html.twig');
    }
    
    // [...]
}
```

In this way we have create a state named `start`, with a link like that
```html
<a ui-sref="start">Go to start page</a>
```
is possible to show inside `div#content-pane` the output of action startAction.
  

## How to generate state configuration

This plugin generate a js file with an array of objects that contains the states configurations
By a function called `$stateConfigurator` is possible to use this array for setup all the states.

The state is not automatically update but we have to run a console command:
```
$ php bin/console mariselli:ng-symfony:states
```
In this way the file defined in configuration will be updated with the new states generate from the new Annotations.

This operation could be done automatically with Gulp or Grunt.

## Setup angular UI router

Here we are suppose that all dependencies are already available
```js
angular.module('demoApp', ['ngSymfony.states', 'ui.router'])
        .config(['$stateProvider', '$urlRouterProvider', '$ngStates', '$httpProvider', function ($stateProvider, $urlRouterProvider, $ngStates, $httpProvider) {
            $urlRouterProvider.otherwise('/start');
            
            $stateConfigurator($stateProvider, $ngStates);
            
            // http settings
            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

        }]);
```

The function `$stateConfigurator` is defined in `stateConfigurator.js` provided by bundle.
The constant `$ngStates` is provided by module `ngSymfony.states` defined in the exported file.
