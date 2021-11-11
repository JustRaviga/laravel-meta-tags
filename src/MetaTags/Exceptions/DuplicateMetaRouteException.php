<?php

namespace Butschster\Head\MetaTags\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Support\Str;

/**
 * Exception class if the user attempts to register two meta route with the same name.
 */
class DuplicateMetaRouteException extends MetaTagsException implements ProvidesSolution
{
    /**
     * Route name
     * @var
     */
    private $name;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct("Meta route name {$name} has already been registered");

        $this->name = $name;
    }

    /**
     * Explain & return solution
     * @return Solution
     */
    public function getSolution(): Solution
    {
        $files = (array)config('meta_tags.files');

        $basePath = base_path() . DIRECTORY_SEPARATOR;

        foreach ($files as &$file) {
            $file = Str::replaceFirst($basePath, '', $file);
        }

        if (count($files) == 1) {
            $description = "Look in `{$files[0]}` for multiple meta routes named `{$this->name}`";
        } else {
            $description = "Look in the following files for multiple breadcrumbs named `{$this->name}`:\n\n- `" . implode("`\n -`", $files) . '`';
        }

        return BaseSolution::create('Remove duplicate breadcrumb')->setSolutionDescription($description);
    }
}
