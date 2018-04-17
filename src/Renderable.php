<?php


namespace pulledbits\View;

use Psr\Http\Message\StreamInterface;

interface Renderable
{
    public function capture() : string;

    public function convertToStream() : StreamInterface;
}