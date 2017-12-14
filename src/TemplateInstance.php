<?php


namespace pulledbits\View;


class TemplateInstance implements Renderable
{
    public function __construct(Template $template, array $parameters)
    {
        $this->template = $template;
        $this->parameters = $parameters;
    }

    public function render() : void {
        $this->template->render($this->parameters);
    }
}