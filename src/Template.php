<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 9-3-17
 * Time: 9:11
 */

namespace pulledbits\View;


interface Template
{
    public function render(array $variables);

}