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
    public function load(string $templateIdentifier, array $variables = []) : Template
    {
        $template = new File\Template($this->directoryTemplates . DIRECTORY_SEPARATOR . $templateIdentifier . '.php', $variables);
        foreach ($this->helpers  as $helperIdentifier => $helper) {
            $template->registerHelper($helperIdentifier, $helper);
        }

        $directory = $this;
        $template->registerHelper('sub', function(TemplateInstance $templateInstance, string $templateIdentifier, array $parameters) use ($directory) : void {
            print $this->load($templateIdentifier)->prepare($parameters)->capture();
        });
        $template->registerHelper('layout', function(TemplateInstance $templateInstance, string $layoutIdentifier) use ($directory) : Layout {
            return $this->layout($layoutIdentifier);
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