<?php

namespace pulledbits\View;


interface Layout
{
    public function section(string $sectionIdentifier, string $content = null);
    public function render();
}