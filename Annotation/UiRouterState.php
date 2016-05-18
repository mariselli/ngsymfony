<?php
/**
 * User: Mattia Mariselli
 * Date: 10/04/16
 * Time: 14:37
 */

namespace Mariselli\NgSymfonyBundle\Annotation;

/**
 * @Annotation
 */
class UiRouterState
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function getView($default = null)
    {
        return $this->getValue('view', $default);
    }

    private function getValue($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }
    public function getController()
    {
        return $this->getValue('controller');
    }
    public function getControllerAs()
    {
        return $this->getValue('controllerAs');
    }
    public function getParentState()
    {
        return $this->getValue('parentState');
    }
    public function getCache()
    {
        return $this->getValue('cache') === 'true' || $this->getValue('cache') === true;
    }

}