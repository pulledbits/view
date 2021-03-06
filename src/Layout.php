<?php

namespace pulledbits\View;


/**
 * Interface Layout
 * @package pulledbits\View
 */
interface Layout
{
    /**
     * @param string $sectionIdentifier
     * @param string|null $content
     * @return mixed
     */
    public function section(string $sectionIdentifier, string $content = null);

    public function compile() : void;
}