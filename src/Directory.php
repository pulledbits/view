<?php


namespace pulledbits\View;


class Directory
{
    private $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }


    public function load(string $templateIdentifier)
    {
        return new \pulledbits\View\File\Template($this->directory . DIRECTORY_SEPARATOR . $templateIdentifier . '.php', $this->directory . DIRECTORY_SEPARATOR . 'layouts');
    }
}