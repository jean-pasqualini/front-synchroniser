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

    protected function render($sourcePath)
    {
        $source = file_get_contents($sourcePath);

        try {
            $configuration = $this->yamlParser->parse($source); // or require(__DIR__."/test/demo.html.fs.php");
        }
        catch (ParseException $e)
        {
            return $source;
        }

        $html = $this->getStaticSource($configuration);

        $htmlObject = \Artack\DOMQuery\DOMQuery::create($html);

        $containerObject = $htmlObject->find($configuration["container"]);

        $renderManager = new FrontSynchroniserRender();

        $edit = empty($_GET["edit"]) ? false : true;

        $renderManager->render($containerObject, $configuration["dom"], $edit);

        if(!$edit)
        {
            return $htmlObject->getHtml();
        }
        else
        {
            ?>
            <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/styles/default.min.css">
            <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/highlight.min.js"></script>
            <style>
                pre { margin: 0px; }
                html, body { background: #f0f0f0; padding: 0px; }
            </style>
            <?php

            $output = "<pre><code class='html'>".htmlspecialchars($containerObject->getHtml())."</code></pre>";

            $pattern = "/".str_replace("xxxxx", "([0-9]+)", $renderManager->getVarTemplate())."/i";

            $output = preg_replace_callback($pattern, function($item) use($configuration)
            {
                //exit(print_r($item, true));

                return "</code></pre><div style='background: red; color: white; font-weight: bold; display: inline;' contenteditable='true' title='".$item[1]."'>".$configuration["dom"][$item[1]]["content"]."</div><pre><code class='html'>";
            }, $output);

            echo $output;
        }

        $errors = $renderManager->getErrors();

        ?>

        <script>hljs.initHighlightingOnLoad();</script>

        <div style="width: 100%; height: 100px; position: fixed; bottom: 0px; left: 0px; right: 0px; background: lightgrey;">
            <ul>
                <?php foreach($errors as $error) { ?>
                    <li><?php echo $error; ?></li>
                <?php } ?>
            </ul>
        </div>

        <?php

    }
}