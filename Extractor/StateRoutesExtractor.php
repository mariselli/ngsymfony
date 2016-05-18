<?php

namespace Mariselli\NgSymfonyBundle\Extractor;

use Doctrine\Common\Annotations\AnnotationReader;
use Mariselli\NgSymfonyBundle\Annotation\UiRouterState;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class StateRoutesExtractor
{
    /**
     * @var ContainerInterface
     */
    private $containerInterface;

    private $ui_state;
    private $all_routes;

    /**
     * NgStateFinder constructor.
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
    }

    public function scanByConfig()
    {
        /*
        module_name: "ciao"
        constant_name: "ciao"
        file_path: "ciao"
        urls: ["/"]
         */
        $config = $this->containerInterface->getParameter('ng_symfony.config');
        if(array_key_exists('sections',$config) && is_array($config['sections']) && count($config['sections']) > 0){
            foreach($config['sections'] as $section){
                $urls = empty($section['urls']) ? null : $section['urls'];
                $this->scanRoutesAndSaveFile($urls);
                $this->saveStateFile($section['module_name'],$section['constant_name'],$section['file_path']);
            }
        }else{
            $urls = empty($config['urls']) ? null : $config['urls'];
            $this->scanRoutesAndSaveFile($urls);
            $this->saveStateFile($config['module_name'],$config['constant_name'],$config['file_path']);
        }
    }


    public function scanRoutesAndSaveFile($base_path = null)
    {
        $annotationReader = new AnnotationReader();
        $routes = $this->containerInterface->get('router')->getRouteCollection()->all();
        $this->containerInterface->set('request', new Request()); //, 'request');
        $this->ui_state = [];
        $this->all_routes = $routes;

        foreach ($routes as $route_key => $route) {
            if (!empty($base_path) && !$this->hasBasePath($route->getPath(), $base_path)) {
                continue;
            }
            $defaults = $route->getDefaults();
            if (isset($defaults['_controller'])) {
                if (strpos($defaults['_controller'], '::') === false) {
                    list($controllerService, $controllerMethod) = explode(':', $defaults['_controller']);
                    $controllerObject = $this->containerInterface->get($controllerService);
                    $reflectedMethod = new \ReflectionMethod($controllerObject, $controllerMethod);
                } else {
                    list($controllerClass, $controllerMethod) = explode('::', $defaults['_controller']);
                    $reflectedMethod = new \ReflectionMethod($controllerClass, $controllerMethod);
                }
                // the annotations
                $annotations = $annotationReader->getMethodAnnotations($reflectedMethod);
                $this->checkAnnotations($annotations, $route, $route_key);
            }
        }

        // clean path avoiding sub-path repetition
        $sub_states = $this->foldingState($this->ui_state);
        foreach ($sub_states as $s) {
            $key = $s['parent'] . '.' . $s['current'];
            if (array_key_exists($key, $this->ui_state)) {
                $this->ui_state[$key]['path'] = str_replace($this->ui_state[$s['parent']]['path'], "",
                    $this->ui_state[$key]['path']);
            }
        }
    }

    public function saveStateFile($moduleName, $constantName, $filePath)
    {
        $js_file = "(function(){ angular.module('%s',[]).constant('%s',%s); })();";
        $js_file_path = $this->containerInterface->get('kernel')->getRootDir() . $filePath;

        file_put_contents($js_file_path,
            sprintf($js_file, $moduleName, $constantName, json_encode($this->ui_state)));
    }

    private function hasBasePath($path, $base_path)
    {
        if (!is_array($base_path)) {
            $base_path = [$base_path];
        }
        $found = 0;
        foreach ($base_path as $base) {
            if (strpos($path, $base) === 0) {
                $found++;
            }
        }
        return $found > 0;
    }

    /**
     * @param array $annotations
     * @param Route $route
     * @throws \Exception
     */
    public function checkAnnotations($annotations, $route, $route_key)
    {
        $founded = 0;
        /** @var UiRouterState $uiAnnotation */
        $uiAnnotation = null;
        foreach ($annotations as $annotation) {
            if ($annotation instanceof UiRouterState) {
                $founded++;
                if (!$route->getOption('expose') || $route->getOption('expose') !== true) {
                    throw new \Exception("Missed expose=true in <" . $route->getDefault('_controller') . '>');
                }
                $uiAnnotation = $annotation;
            }
        }
        if ($founded === 0) {
            return;
        }
        if ($founded > 1) {
            throw new \Exception('Found more then one UiRouterState for the action: ' . "\n" . $route->getDefault('_controller'));
        }

        if ($uiAnnotation->getView() !== null) {
            $founded = false;
            foreach ($this->all_routes as $r_name => $r) {
                if ($r_name === $uiAnnotation->getView()) {
                    $founded = true;
                    if (!$route->getOption('expose') || $route->getOption('expose') !== true) {
                        throw new \Exception("Missed expose=true in view <" . $uiAnnotation->getView() . "> of specified state <" . $route_key . ">");
                    }
                }
            }
            if (false === $founded) {
                throw new \Exception("Not found view <" . $uiAnnotation->getView() . "> of specified state <" . $route_key . ">");
            }
        }

        $this->ui_state[$route_key] = [];
        $this->ui_state[$route_key]['state'] = $route_key;
        $this->ui_state[$route_key]['view'] = $uiAnnotation->getView($route_key);
        $this->ui_state[$route_key]['path'] = $this->getAngularUiRouterParametersPath($route->getPath());
        $this->ui_state[$route_key]['controller'] = $uiAnnotation->getController(); // $params->getOption('angularUiRouteController');
        $this->ui_state[$route_key]['controllerAs'] = $uiAnnotation->getControllerAs(); //$params->getOption('angularUiRouteControllerAs');
        $this->ui_state[$route_key]['parentState'] = $uiAnnotation->getParentState(); //$params->getOption('angularUiRouteParentState');
        $this->ui_state[$route_key]['cache'] = $uiAnnotation->getCache(); //!!$params->getOption('angularUiRouteCache');

        //$this->ui_state[] = $uiState;
    }

    private function getAngularUiRouterParametersPath($path)
    {
        $path = str_replace('}', '', $path);
        $path = str_replace('{', ':', $path);
        return $path;
    }

    private function foldingState($states)
    {
        $sub_states = [];
        foreach ($states as $state => $stateData) {
            $sub = explode(".", $state);
            if (count($sub) > 1) {
                $sub_states[] = [
                    'parent' => array_shift($sub),
                    'current' => array_shift($sub)
                ];
            }
        }
        return $sub_states;
    }
}
