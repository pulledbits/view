<?php
/**
 * User: hameijer
 * Date: 9-3-17
 * Time: 15:08
 */

namespace File;


use pulledbits\View\File\Template;


class TemplateTest extends \PHPUnit_Framework_TestCase
{

    public function testRender_When_ExistingTemplateWithNoVariables_Expect_ContentsOutputted()
    {
        $variable = microtime();
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html>BlaBla</html>' . $variable);

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>BlaBla</html>' . $variable);
        $object->render([]);

        unlink($templatePath);
    }

    public function testCapture_When_ExistingTemplateWithNoVariables_Expect_ContentsOutputted()
    {
        $variable = microtime();
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html>BlaBla</html>' . $variable);

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->assertEquals('<html>BlaBla</html>' . $variable, stream_get_contents($object->capture([])));

        unlink($templatePath);
    }


    public function testRender_When_TemplateUsingLayout_Expect_ContentsOutputted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath . '.php', '<html>BlaBla</html>');

        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<?php $layout = $this->layout(\'' . basename($layoutPath) . '\'); ?>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>BlaBla</html>');
        $object->render([]);

        unlink($templatePath);
        unlink($layoutPath . '.php');
    }


    public function testRender_When_ExistingTemplateWithSubTemplate_Expect_ContentsOutputted()
    {
        $subtemplatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($subtemplatePath . '.php', '<html>BlaBla</html>');
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<?php $this->sub(\'' . basename($subtemplatePath) . '\')->render([]); ?>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>BlaBla</html>');
        $object->render([]);

        unlink($templatePath);
        unlink($subtemplatePath . '.php');
    }

    public function testRender_When_ExistingTemplateWithVariables_Expect_ContentsOutputted()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$foo?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>barBlaBla</html>');
        $object->render([
            'foo' => 'bar'
        ]);

        unlink($templatePath);
    }


    public function testRender_When_ExistingTemplateWithHTMLUnsafeVariables_Expect_ContentsOutputted()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$foo;?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>&lt;bar&gt;BlaBla</html>');
        $object->render([
            'foo' => '<bar>'
        ]);

        unlink($templatePath);
    }

    public function test__call_When_NonExistingHelper_Expect_EmptyString()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());
        $this->assertEquals('', $object->nonExistingHelper());
    }

    public function testRender_When_HelperRegistered_Expect_ContentsWithHelperOutput()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());
        $object->registerHelper('url', function(string $path): string {
            return 'https://example.com' . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $object->render([]);

        unlink($templatePath);
    }

    public function testRender_When_HelperRegisteredWhichReturnsNULLAndOutputsDirectly_Expect_OBContentsWithHelperOutput()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());
        $object->registerHelper('url', function(string $path) : void {
            print 'https://example.com' . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $object->render([]);

        unlink($templatePath);
    }

    public function testRender_When_HelperRegisteredNoReturnType_Expect_OBContentsWithHelperOutput()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());
        $object->registerHelper('url', function(string $path) {
            print 'https://example.com' . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $object->render([]);

        unlink($templatePath);
    }

    public function testRender_When_HelperRegisteredOtherReturnType_Expect_EmptyString()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());
        $object->registerHelper('url', function(string $path) : int {
            print 'https://example.com' . $path;
            return 0;
        });

        $this->expectOutputString('<html>BlaBla</html>');
        $object->render([]);

        unlink($templatePath);
    }

    public function testRender_When_HelperRegistered_Expect_ContentsWithHelperOutputInSubTemplate()
    {
        $subtemplatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($subtemplatePath . '.php', '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<?php $this->sub(\'' . basename($subtemplatePath) . '\')->render([]); ?>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());
        $object->registerHelper('url', function(string $path): string {
            return 'https://example.com' . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $object->render([]);

        unlink($templatePath);
        unlink($subtemplatePath . '.php');
    }

    public function testRender_When_HelperUsingOtherHelper_Expect_ContentsWithHelperHelper()
    {
        $subtemplatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($subtemplatePath . '.php', '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<?php $this->sub(\'' . basename($subtemplatePath) . '\')->render([]); ?>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());
        $object->registerHelper('url', function(string $path): string {
            return 'https://' . $this->host() . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $object->render([
            'host' => function(): string {
                return 'example.com';
            }
        ]);

        unlink($templatePath);
        unlink($subtemplatePath . '.php');
    }


    public function testRender_When_HelperUsingOtherPrivateHelper_Expect_ContentsWithHelperHelper()
    {
        $subtemplatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($subtemplatePath . '.php', '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<?php $this->sub(\'' . basename($subtemplatePath) . '\')->render([]); ?>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());
        $object->registerHelper('url', function(string $path): string {
            return $this->escape('https://' . $this->host() . $path);
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $object->render([
            'host' => function(): string {
                return 'example.com';
            }
        ]);

        unlink($templatePath);
        unlink($subtemplatePath . '.php');
    }

    public function testRender_When_HelperRegisteredThroughRenderArgument_Expect_ContentsWithHelperOutputInSubTemplate()
    {
        $subtemplatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($subtemplatePath . '.php', '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<?php $this->sub(\'' . basename($subtemplatePath) . '\')->render([]); ?>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());
        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $object->render([
            'url' => function(string $path): string {
                return 'https://example.com' . $path;
            }
        ]);

        unlink($templatePath);
        unlink($subtemplatePath . '.php');
    }
}
