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

```
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
```
ng_symfony:
    module_name: "ngSymfony.states"
    constant_name: "$ngStates"
    file_path: "/Resources/ui-states.js"
```

It's also possible to filter what kind of url have to be scanned:
```
ng_symfony:
    module_name: "ngSymfony.states"
    constant_name: "$ngStates"
    file_path: "/Resources/ui-states.js"
    urls: ['/contact','/profile']
```

Sometime happen that we want to separate state base on path.
In this case is possible to declare `sections` property.
NOTE: the root property `module_name, module_name, file_path, urls` will be ignored even if are mandatory.
```
ng_symfony:
    module_name: "ngSymfony.states"
    module_name: "$ngStates"
    file_path: "/Resources/ui-states.js"
    urls: ['/contact','/profile']
    sections:
        - { module_name: "ngSymfony.frontStates", constant_name: "$ngStatesFrontend", file_path: "/Resources/ui-states-frontend.js", urls: ["/app"] }
        - { module_name: "ngSymfony.backendStates", constant_name: "$ngStatesBackend", file_path: "/Resources/ui-states-backend.js", urls: ["/admin", "/staff"] }
```