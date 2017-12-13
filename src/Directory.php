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
        return new File\Template($this,$this->directoryTemplates . DIRECTORY_SEPARATOR . $templateIdentifier . '.php', $this->directoryTemplates . DIRECTORY_SEPARATOR . 'layouts');
    }

    /**
     * @param string $layoutIdentifier
     * @return Layout
     */
    public function layout(string $layoutIdentifier): Layout
    {
        return new File\Layout($this->directoryLayouts . DIRECTORY_SEPARATOR . $layoutIdentifier . '.php');
    }
}