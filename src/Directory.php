<?php


namespace pulledbits\View;


class Directory
{
    private $directoryTemplates;
    private $directoryLayouts;

    private $helpers = [];

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
        $template = new File\Template($this->directoryTemplates . DIRECTORY_SEPARATOR . $templateIdentifier . '.php');
        foreach ($this->helpers  as $helperIdentifier => $helper) {
            $template->registerHelper($helperIdentifier, clone $helper);
        }

        $directory = $this;
        $template->registerHelper('sub', function(string $templateIdentifier, array $parameters) use ($directory) {
            $directory->load($templateIdentifier)->renderWithoutLayout($parameters);
        });

        return $template;
    }

    /**
     * @param string $layoutIdentifier
     * @return Layout
     */
    public function layout(string $layoutIdentifier): Layout
    {
        return \pulledbits\View\File\Layout::load($this->directoryLayouts, $layoutIdentifier);
    }

    public function registerHelper(string $identifier, callable $callback) : void
    {
        $this->helpers[$identifier] = $callback;
    }
}