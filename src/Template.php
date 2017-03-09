<?php

namespace pulledbits\View;


interface Template
{
    public function render(array $variables);
    public function registerHelper(string $identifier, callable $callback);
}