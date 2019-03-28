<?php

namespace Butschster\Head\Contracts\MetaTags\Entities;

interface TitleInterface extends TagInterface
{
    /**
     * Specify max length of the title
     *
     * @param int $maxLength
     *
     * @return $this
     */
    public function setMaxLength(int $maxLength);

    /**
     * Set the main title
     *
     * @param string|null $title
     *
     * @return $this
     */
    public function setTitle(?string $title);

    /**
     * Prepend next part of title
     *
     * @param string $text
     *
     * @return $this
     */
    public function prepend(string $text);

    /**
     * Determine separator among title parts
     *
     * @param string $separator
     *
     * @return $this
     */
    public function setSeparator(string $separator);
}