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
    public function capture(string $templateIdentifier, array $parameters);

    /**
     * @param array $variables
     */
    public function render(string $templateIdentifier, array $parameters): void;

    /**
     * @param string $identifier
     * @param callable $callback
     */
    public function registerHelper(string $identifier, callable $callback) : void;
}