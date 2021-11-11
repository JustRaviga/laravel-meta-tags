<?php

namespace Butschster\Head\MetaTags;

use Butschster\Head\Contracts\MetaTags\Entities\TagInterface;
use Butschster\Head\Contracts\MetaTags\MetaInterface;
use Butschster\Head\MetaTags\Exceptions\DuplicateMetaRouteException;
use Butschster\Head\MetaTags\Exceptions\InvalidMetaTagException;
use Butschster\Head\MetaTags\Exceptions\UnnamedRouteException;
use Butschster\Head\Packages\Manager;
use Illuminate\Config\Repository;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Throwable;

class Meta implements MetaInterface
{
    use Macroable,
        Concerns\ManageTitle,
        Concerns\ManageMetaTags,
        Concerns\ManageLinksTags,
        Concerns\ManagePlacements,
        Concerns\ManagePackages,
        Concerns\ManageAssets,
        Concerns\InitializeDefaults;

    const PLACEMENT_HEAD = 'head';
    const PLACEMENT_FOOTER = 'footer';

    /**
     * @var Manager
     */
    protected $packageManager;

    /**
     * @var Repository|null
     */
    private $config;

    protected $callbacks = [];

    /**
     * @var array|null The current route name and parameters.
     * @var Router
     */
    protected $router;

    /**
     * @var array|null The current route name and parameters.
     */
    protected $route;

    /**
     * @param Manager $packageManager
     * @param Router $router
     * @param Repository|null $config
     */
    public function __construct(Manager $packageManager, Router $router, Repository $config = null)
    {
        $this->config = $config;
        $this->packageManager = $packageManager;
        $this->router = $router;

        $this->initPlacements();
    }

    /**
     * @inheritdoc
     */
    public function getTag(string $name): ?TagInterface
    {
        foreach ($this->getPlacements() as $placement) {
            if ($placement->has($name)) {
                return $placement->get($name);
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function addTag(string $name, TagInterface $tag, ?string $placement = null)
    {
        $this->placement($placement ?: $tag->getPlacement())->put($name, $tag);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function registerTags(TagsCollection $tags, ?string $placement = null)
    {
        foreach ($tags as $name => $tag) {
            $this->addTag($name, $tag, $placement);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTag(string $name)
    {
        foreach ($this->getPlacements() as $placement) {
            $placement->forget($name);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        foreach ($this->getPlacements() as $placement) {
            $placement->reset();
        }

        return $this;
    }

    /**
     * Get content as a string of HTML.
     * @param string|null $name
     * @param mixed ...$params
     * @return string
     * @throws InvalidMetaTagException
     * @throws Throwable
     * @throws UnnamedRouteException
     */
    public function toHtml(string $name = null, ...$params): string
    {
        $this->generate($name, ...$params);

        return $this->head()->toHtml();
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * Remove HTML tags
     *
     * @param string $string
     *
     * @return string
     */
    protected function cleanString(?string $string): string
    {
        return e(strip_tags($string));
    }

    /**
     * Get value from config repository
     * If config repository is not set, it returns default value
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    protected function config(string $key, $default = null)
    {
        if (!$this->config) {
            return $default;
        }

        return $this->config->get('meta_tags.' . $key, $default);
    }

    public function toArray()
    {
        return $this->placements->toArray();
    }

    /**
     * Register meta details by route name
     * @throws Throwable
     */
    public function for(string $name, callable $callback)
    {
        throw_if(isset($this->callbacks[$name]), new DuplicateMetaRouteException($name));

        $this->callbacks[$name] = $callback;
    }

    /**
     * Return current route: two-element array consisting of the route name (string) and any parameters (array).
     * @return array
     * @throws Throwable
     */
    protected function getCurrentRoute(): ?array
    {
        if ($this->route) {
            return $this->route;
        }

        $route = $this->router->current();

        if ($route === null) {
            return ['errors.404', []];
        }

        $name = $route->getName();

        if (config('meta_tags.route_has_language_prefix')) {
            $name = Str::replace(app()->getLocale() . ".", '', $name);
        }

        throw_if(is_null($name), new UnnamedRouteException($route));

        $params = array_values($route->parameters());

        return [$name, $params];
    }

    /**
     * Generate a set of meta tags for a page.
     * @param string|null $name
     * @param mixed ...$params
     * @throws InvalidMetaTagException
     * @throws Throwable
     * @throws UnnamedRouteException
     */
    public function generate(string $name = null, ...$params)
    {
        if (empty($name)) {
            try {
                [$name, $params] = $this->getCurrentRoute();
            } catch (UnnamedRouteException $e) {
                if (config('breadcrumbs.unnamed-route-exception')) {
                    throw $e;
                }
            }
        }

        try {
            $this->call($name, $params);
        } catch (InvalidMetaTagException $exception) {
            if ($this->config('meta_tags.invalid-named-meta-exception')) {
                throw $exception;
            }
        }
    }

    /**
     * Call the closure to generate meta tag for a page.
     * @param string $name
     * @param array $params
     * @throws Throwable
     */
    protected function call(string $name, array $params)
    {
        throw_if(!isset($this->callbacks[$name]), new InvalidMetaTagException($name));

        $this->callbacks[$name]($this, ...$params);
    }

}
