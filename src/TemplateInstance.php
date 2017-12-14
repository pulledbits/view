<?php


namespace pulledbits\View;


class TemplateInstance implements Renderable
{
    public function __construct(Template $template, array $parameters)
    {
        $this->template = $template;
        $this->parameters = $parameters;
    }

    public function __call(string $identifier, array $arguments): string
    {
        $this->template->__call(...func_get_args());
    }

    public function capture() : string {
        return $this->template->capture($this->parameters);
    }
}