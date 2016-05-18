<?php

namespace Mariselli\NgSymfonyBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonUiRouterState extends JsonResponse{

    /**
     * Constructor.
     * @param mixed $state Name of the state
     * @param array $parameters Parameters required from the state
     */
    public function __construct($state,$parameters = [])
    {
        parent::__construct([
            'type'=>'AngularUiRouterState',
            'state'=>$state,
            'parameters'=>$parameters
        ]);
    }
}