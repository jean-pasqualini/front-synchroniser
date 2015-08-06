<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 29/07/2015
 * Time: 16:18
 */

namespace FrontSynchroniserBundle\Service;

use Symfony\Component\Yaml\Exception\ParseException;

use FrontSynchroniser\Render as FrontSynchroniserRender;

class FrontSynchroniserManager {

    protected $configuration;

    protected $yamlParser;

    protected $pathResolver;

    public function __construct($configuration, $pathResolver)
    {
        $this->configuration = $configuration;

        $this->yamlParser = new \Symfony\Component\Yaml\Parser();

        $this->pathResolver = $pathResolver;
    }

    public function compile($sourcePath)
    {
        $sourcePath = $this->pathResolver->locate($sourcePath);

        $compiledPath = $this->getCompiledPath($sourcePath);

        $compiledSource = $this->render($sourcePath);

        file_put_contents($compiledPath, $compiledSource);

        return $compiledPath;
    }

    public function getCompiledPath($sourcePath)
    {
        return $this->configuration["outputdir"].DIRECTORY_SEPARATOR.md5($sourcePath).".raw";
    }

    protected function getStaticSource($configuration)
    {
        return file_get_contents($this->configuration["staticdir"].DIRECTORY_SEPARATOR.$configuration["template"]);
    }

    public function getMetadataFromPath($sourcePath)
    {
        $source = file_get_contents($sourcePath);

        try {
            $configuration = $this->yamlParser->parse($source); // or require(__DIR__."/test/demo.html.fs.php");
        }
        catch (ParseException $e)
        {
            return null;
        }
        
        return $configuration;
    }
    
    public function getErrors()
    {
        return array();
    }

    public function render($sourcePath, $edit = false, $js = false)
    {
        $configuration = $this->getMetadataFromPath($sourcePath);

        if($configuration === null) return "[ERROR COMPILATED]";

        $html = $this->getStaticSource($configuration);

        $htmlObject = \Artack\DOMQuery\DOMQuery::create($html);

        $containerObject = $htmlObject->find($configuration["container"]);

        $renderManager = new FrontSynchroniserRender($configuration);

        $renderManager->render($containerObject, $configuration["dom"], $edit);

        $output = $containerObject->getHtml();
        
        if($edit) $output = "<pre><code class='html'>".htmlspecialchars($output)."</code></pre>";
        
        if(!$js) $output = $renderManager->postRender($output, $edit);
        
        return $output;

    }
}