<?php

require __DIR__."/../vendor/autoload.php";

$yamlParser = new \Symfony\Component\Yaml\Parser();

$globalConfigurationFilePath = __DIR__."/../storage/config.yml";

$storagedir = dirname($globalConfigurationFilePath);

$globalConfiguration = $yamlParser->parse(file_get_contents($globalConfigurationFilePath));

$fileconfiguration = $storagedir."/".$globalConfiguration["fsdir"]."/demo.html.fs.yml";

$configuration = $yamlParser->parse(file_get_contents($fileconfiguration)); // or require(__DIR__."/test/demo.html.fs.php");

$html = file_get_contents($storagedir."/".$globalConfiguration["staticdir"]."/".$configuration["template"]);

$htmlObject = \Artack\DOMQuery\DOMQuery::create($html);

$containerObject = $htmlObject->find($configuration["container"]);

$renderManager = new Render();

$edit = empty($_GET["edit"]) ? false : true;

$renderManager->render($containerObject, $configuration["dom"], $edit);

if(!$edit)
{
    file_put_contents($storagedir."/".$globalConfiguration["outputdir"]."/".$configuration["output"], $htmlObject->getHtml());

    $loader = new Twig_Loader_Array(array(
        "test" => $htmlObject->getHtml()
    ));

    $twig = new Twig_Environment($loader);

    echo $twig->render("test");
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
