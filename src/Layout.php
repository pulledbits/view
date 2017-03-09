<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 9-3-17
 * Time: 9:12
 */

namespace pulledbits\View;


interface Layout
{
    public function section(string $sectionIdentifier, string $content = null);
    public function render();
}