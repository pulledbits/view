<?php

namespace pulledbits\View;


interface Template
{
    public function capture(array $variables) : string;
    public function render(array $variables) : void;
    public function registerHelper(string $identifier, callable $callback) : void;
}