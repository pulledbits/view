<?php

namespace pulledbits\View;


interface Template
{
    /**
     * @param array $variables
     * @return resource
     */
    public function capture(array $variables);
    public function render(array $variables) : void;
    public function registerHelper(string $identifier, callable $callback) : void;
}