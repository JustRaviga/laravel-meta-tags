<?php

namespace Butschster\Head\MetaTags\Exceptions;

/**
 * Exception that is thrown if the user attempts to generate meta tags for a page that is not registered.
 */
class InvalidMetaTagException extends MetaTagsException
{
    public function __construct($name)
    {
        parent::__construct("Meta tag not found with name \"{$name}\"");
    }
}
