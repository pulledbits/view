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
        $layoutIdentifier = 'master';
        if (strpos($templateIdentifier, '.') !== false) {
            $layoutIdentifier = substr($templateIdentifier, 0, strpos($templateIdentifier, '.'));
        }
        return new File\Template($this->layout($layoutIdentifier),$this->directoryTemplates . DIRECTORY_SEPARATOR . $templateIdentifier . '.php');
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