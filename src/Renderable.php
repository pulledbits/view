<?php


namespace pulledbits\View;


interface Renderable
{
    public function capture() : string;
}