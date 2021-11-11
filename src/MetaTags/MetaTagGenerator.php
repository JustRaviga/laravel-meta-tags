<?php

namespace Butschster\Head\MetaTags;

use Butschster\Head\MetaTags\Exceptions\InvalidMetaTagException;
use Illuminate\Support\Collection;
use Throwable;

class MetaTagGenerator
{
    /**
     * @var Collection
     */
    protected $metas;

    /**
     * @var array The registered meta tags callbacks
     */
    protected $callbacks;

    /**
     * Generate meta tags
     * @param array $callbacks
     * @param string $name
     * @param array $params
     * @return Collection
     * @throws Throwable
     */
    public function generate(array $callbacks, string $name, array $params): Collection
    {
        $this->metas = collect();
        $this->callbacks = $callbacks;

        $this->call($name, $params);

        return $this->metas;
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

    /**
     * @param string $title
     * @return $this
     */
    public function addTitle(string $title): MetaTagGenerator
    {
        $this->metas->put('title', $title);
        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function addDescription(string $description): MetaTagGenerator
    {
        $this->metas->put('description', $description);
        return $this;
    }

    /**
     * @param array $keyWords
     */
    public function addKeyWords(array $keyWords): void
    {
        $this->metas->put('key_words',  $keyWords);
    }
}
