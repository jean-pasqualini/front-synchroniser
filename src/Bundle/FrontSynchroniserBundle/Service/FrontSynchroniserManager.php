<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 29/07/2015
 * Time: 16:18
 */

namespace FrontSynchroniserBundle\Service;

use Artack\DOMQuery\DOMQuery;
use FrontSynchroniserBundle\Editeur\CoucheCode;
use FrontSynchroniserBundle\Editeur\CoucheVisuel;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\Yaml\Exception\ParseException;

use FrontSynchroniser\Render as FrontSynchroniserRender;
use Symfony\Component\Yaml\Yaml;

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

        if(file_exists($compiledPath))
        {
            return $compiledPath;
        }

        if (!file_exists($this->configuration["outputdir"])) {
            mkdir($this->configuration["outputdir"], 0777, true);
        }

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

    public function getCss(\DOMNodeList $children, $xpath, $css = array(), $level = 1)
    {
        foreach($children as $child)
        {
            echo $child->getNodePath()." => ".$xpath."<br>";

            if($child instanceof \DOMElement)
            {
                $domQuery = DOMQuery::createFromNode($child);

                $classes = implode(".", $domQuery->getClasses());

                if($level > 3)
                {
                    $css[] = $child->nodeName.((!empty($classes)) ? ".".$classes : "");
                }

                if($child->getNodePath() == $xpath)
                {
                    return implode(" > ", $css);
                }
                else
                {
                    return $this->getCss($child->childNodes, $xpath, $css, $level + 1);
                }
            }

            if($child instanceof \DOMText)
            {
                if($child->getNodePath() == $xpath)
                {
                    return implode(" > ", $css);
                }
            }
        }

        return null;
    }

    public function saveEditor($path, array $data)
    {
        $metadata = $this->getMetadataFromPath($path);

        $html = $this->getStaticSource($metadata);

        $dom = new \DOMDocument();

        $dom->loadHTML($html);

        $xpath = new \DOMXPath($dom);

        if($metadata["container"] === null)
        {
            $containerHtml = $dom->saveHTML();
        }
        else
        {
            $result = $xpath->query($metadata["container"], $dom);

            $containerHtml = $dom->saveHTML($result->item(0));
        }

        $dom->loadHTML($containerHtml);

        $idContent = count($metadata["content"]) + 1;

        $idNode = "__".uniqid(md5($data["nodePath"]))."__";

        $metadata["content"][$idContent] = $data["content"];

        $metadata["dom"][] = array(
                "id" => $idNode,
                "selector" => $data["nodePath"],
                "content" => $idContent
        );

        file_put_contents($path, Yaml::dump($metadata));
    }

    public function buildEditor($sourcePath)
    {
///
        $configuration = $this->getMetadataFromPath($sourcePath);

        if($configuration === null) return "[ERROR COMPILATED]";

        $html = $this->getStaticSource($configuration);

        $htmlObject = new \FluentDOM\Document;

        $htmlObject->loadHTML($html);

        if($configuration["container"] === null)
        {
            $containerHtml = $htmlObject->saveHTML();
        }
        else
        {
            $containerObject = $htmlObject->find($configuration["container"]);

            $containerObject->contentType = "text/html";

            $containerHtml = $containerObject->html();
        }
        //

        $coucheCode = new CoucheCode($containerHtml);

        $coucheVisuel = new CoucheVisuel($containerHtml);

        return array(
            "coucheCode" => $coucheCode,
            "coucheVisuel" => $coucheVisuel
        );
    }

    public function render($sourcePath, $edit = false, $js = false)
    {
        $configuration = $this->getMetadataFromPath($sourcePath);

        if($configuration === null) return "[ERROR COMPILATED]";

        $html = $this->getStaticSource($configuration);

        $htmlObject = new \FluentDOM\Document;

        $htmlObject->loadHTML($html);

        if($configuration["container"] === null)
        {
            $containerHtml = $htmlObject->saveHTML();
        }
        else
        {
            $containerObject = $htmlObject->find($configuration["container"]);

            $containerObject->contentType = "text/html";

            $containerHtml = $containerObject->html();
        }

        if(strpos($sourcePath, "panel") !== false)
        {
            //return htmlentities($containerHtml);
        }

        $containerObject = new \FluentDOM\Document;

        $containerObject->loadHTML($containerHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $renderManager = new FrontSynchroniserRender($configuration);

        $renderManager->render($containerObject, $configuration, $edit);

        $output = (string) $containerObject->toHtml();
        
        if($edit) $output = "<pre><code class='html'>".htmlspecialchars($output)."</code></pre>";
        
        if(!$js) $output = $renderManager->postRender($output, $edit);
        
        return $output;

    }
}