# Ng-Symfony
A pretty nice way to expose Angular UI Router states based on your Symfony2 routing system.

---

NOTE: 
this documentation is underconstruction, 
the english used is quide bad and will be fixed as soon as possible. Fell free to help :D


## Introduction
This bundle want to use the power of annotations for generate the state configuration fon [Angualr UI Router](https://github.com/angular-ui/ui-router).

## The concept behind
The purpose of this bundle is to connect angular states with symfony routes.
The difference from an angular-ui state and a symfony route is that a route have an URL and name when the state has
a name, a controller associated and a template (string or URL).

__The solutions is this:__
Every state should carry an information.
The information is provided by a symfony action executed on server side. 

So basicaly a symofny action could be connected to an angular ui state.
The __route name__ of the action will be the __state name__, this action can specify the name of the __route__ 
that will return the template as an html string and  specify a name of an angular controller that will handle the view.

So far is clear that this route will define a state with template and controller, but the return itself of this action
who will handle?
This is something that have to be handle by a developer.
In the follows partI will explain better this concepts.

## Installation

Open your console, go in your project directory and execute the
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

__Note:__ Here I'm assuming that you know how it works Angular UI router

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
  
Scroll down for [annotation reference](#annotation-reference)
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


## Annotation Reference

```php
use Mariselli\NgSymfonyBundle\Annotation\UiRouterState;
// [...]

class DefaultController extends Controller
{
    // [...]
    
    /**
     * @Route("/demo", name="demo", options={"expose"=true})
     * @UiRouterState(view="demo_view", controller="DemoCtrl", controllerAs="cont", parentState="home", cache="false")
     */
    public function demoAction()
    {
        return JsonResponse::create(['name'=>'Bob']);
    }
    
    /**
     * @Route("/view/demo", name="demo_view", options={"expose"=true})
     */
    public function demoViewAction()
    {
        return $this->render('default/demo.html.twig');
    }
    
    // [...]
}
```