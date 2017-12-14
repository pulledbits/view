<?php


namespace pulledbits\View;


class Directory
{
    private $directoryTemplates;
    private $directoryLayouts;

    public function __construct(string $directoryTemplates, string $directoryLayouts)
    {
        $this->directoryTemplates = $directoryTemplates;
        $this->directoryLayouts = $directoryLayouts;
    }

    /**
     * @param string $templateIdentifier
     * @return Template
     */
    public function load(string $templateIdentifier) : Template
    {
        return new File\Template($this->directoryTemplates . DIRECTORY_SEPARATOR . $templateIdentifier . '.php');
    }

    /**
     * @param string $layoutIdentifier
     * @return Layout
     */
    public function layout(string $layoutIdentifier): Layout
    {
        return \pulledbits\View\File\Layout::load($this->directoryLayouts, $layoutIdentifier);
    }
}