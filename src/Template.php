<?php

namespace pulledbits\View;


/**
 * Interface Template
 * @package pulledbits\View
 */
interface Template
{
    /**
     * @param array $variables
     * @return resource
     */
    public function capture(Layout $layout, array $parameters);

    /**
     * @param array $variables
     */
    public function render(Layout $layout, array $parameters): void;

    /**
     * @param string $identifier
     * @param callable $callback
     */
    public function registerHelper(string $identifier, callable $callback) : void;
}