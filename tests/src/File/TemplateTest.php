<?php

namespace pulledbits\View\File;


use GuzzleHttp\Psr7\Response;

class TemplateTest extends \PHPUnit\Framework\TestCase
{
    private $templatePath;

    /**
     * @var Template
     */
    private $object;

    protected function setUp()
    {
        $this->templatePath = tempnam(sys_get_temp_dir(), 'tt_') . '.php';

        $this->object = new Template($this->templatePath);
    }

    public function testPrepareAsResponse()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path) : int {
            print 'https://example.com' . $path;
            return 0;
        });
        $instance = $this->object->prepare();
        $this->assertEquals('<html>0BlaBla</html>', $instance->capture());
    }
}
