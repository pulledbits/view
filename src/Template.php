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
    public function capture(array $variables);

    /**
     * @param array $variables
     */
    public function render(array $variables) : void;

    /**
     * @param string $identifier
     * @param callable $callback
     */
    public function registerHelper(string $identifier, callable $callback) : void;
}