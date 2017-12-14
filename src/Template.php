<?php

namespace pulledbits\View;


/**
 * Interface Template
 * @package pulledbits\View
 */
interface Template
{

    /**
     * @param string $identifier
     * @param callable $callback
     */
    public function registerHelper(string $identifier, callable $callback) : void;

    public function prepare(array $parameters) : TemplateInstance;
}