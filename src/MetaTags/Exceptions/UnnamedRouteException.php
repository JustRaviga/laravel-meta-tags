<?php

namespace Butschster\Head\MetaTags\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;

/**
 * Exception class the user attempt to render meta route for the current route but the current route
 * doesn't have a name.
 */
class UnnamedRouteException extends MetaTagsException implements ProvidesSolution
{
    /**
     * @var Route
     */
    private $route;

    public function __construct(Route $route)
    {
        $uri = Arr::first($route->methods()) . ' /' . ltrim($route->uri(), '/');

        parent::__construct("The current route ($uri) is not named");

        $this->route = $route;
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create('Give the route a name');
    }
}
