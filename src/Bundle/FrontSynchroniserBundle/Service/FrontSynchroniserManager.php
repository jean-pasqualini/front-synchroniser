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
use FrontSynchroniserBundle\Editeur\CoucheContennu;
use FrontSynchroniserBundle\Editeur\CoucheDispatcher;
use FrontSynchroniserBundle\Editeur\CoucheVisuel;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\Yaml\Exception\ParseException;

use FrontSynchroniser\Render as FrontSynchroniserRender;
use Symfony\Component\Yaml\Yaml;

/**
 * Responsable de :
 * - Le rendu des templates statiques
 * - La dynamisation des templates statiques
 * - La récuperation des métadata d'un template (.fs)
 *
 * Class FrontSynchroniserManager
 * @package FrontSynchroniserBundle\Service
 */
class FrontSynchroniserManager {

    /**
     * @var array Configuration de l'outils
     */
    protected $configuration;

    /**
     * @var object YamlParser
     */
    protected $yamlParser;

    /**
     * @var object Resolver de chemin de template (différe selon le framework)
     */
    protected $pathResolver;

    /**
     * @param $configuration
     * @param $pathResolver
     */
    public function __construct($configuration, $pathResolver)
    {
        $this->configuration = $configuration;

        $this->yamlParser = new \Symfony\Component\Yaml\Parser();

        $this->pathResolver = $pathResolver;
    }

    /**
     * @param $sourcePath
     * @return string
     */
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

    /**
     * @param $sourcePath
     * @return string
     */
    public function getCompiledPath($sourcePath)
    {
        return $this->configuration["outputdir"].DIRECTORY_SEPARATOR.md5($sourcePath).".raw";
    }

    /**
     * La version static non dynamiser
     *
     * @param $configuration
     * @return string
     */
    protected function getStaticSource($configuration)
    {
        return file_get_contents($this->configuration["staticdir"].DIRECTORY_SEPARATOR.$configuration["template"]);
    }

    /**
     * Retourn les métadata
     *
     * @param $sourcePath
     * @return null
     */
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

    /**
     * Retourne les érreurs rencontré pendant la génération
     *
     * @return array
     */
    public function getErrors()
    {
        return array();
    }

    /**
     * Récupere le css
     *
     * @param \DOMNodeList $children
     * @param $xpath
     * @param array $css
     * @param int $level
     * @return null|string
     */
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

    /**
     * Sauvegarde les modifications effectuer dans le template statique sous forme de .fs pour la dynamisation
     *
     * @param $path
     * @param array $data
     */
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

    /**
     * Retourne le contenu d'un élement dom par son xpath
     *
     * @param array $metadata
     * @param $xpath
     * @return null
     */
    public function getContentByXpath(array $metadata, $xpath)
    {
        foreach($metadata["dom"] as $dom)
        {
            if($dom["selector"] == $xpath)
            {
                return $metadata["content"][$dom["content"]];
            }
        }

        return null;
    }

    /**
     * Construit l'editeur
     *
     * @param $sourcePath
     * @return array|string
     */
    public function buildEditor($sourcePath)
    {
        // La configuration du template
        $configuration = $this->getMetadataFromPath($sourcePath);

        // Si la configuration n'est pas disponible on retourne une erreur
        if($configuration === null) return "[ERROR COMPILATED]";

        // La version static non dynamiser
        $html = $this->getStaticSource($configuration);

        // On charge la version static dans FluentDom pour la manipuler
        $htmlObject = new \FluentDOM\Document;
        $htmlObject->loadHTML($html);

        // Si on dynamise tout le template
        if($configuration["container"] === null)
        {
            // Alors le container html est le template
            $containerHtml = $htmlObject->saveHTML();
        }
        else
        {
            // Si on dynamise une partie du template, alors le container html est une partie du template
            $containerObject = $htmlObject->find($configuration["container"]);
            $containerObject->contentType = "text/html";
            $containerHtml = $containerObject->html();
        }

        // On instancie la couche de contenu
        $coucheContenu = new CoucheContennu($configuration, $this);

        // On instancie la couche visuel
        $coucheVisuel = new CoucheVisuel($containerHtml, $configuration, $this);

        $coucheDispatcher = new CoucheDispatcher($configuration, $this);

        $coucheDispatcher->addCoucheListener($coucheContenu);

        $coucheDispatcher->addCoucheListener($coucheVisuel);

        // On instancie la couche de code
        $coucheCode = new CoucheCode($containerHtml, $coucheDispatcher);

        // On retourn les différentes couches qui constitue l'éditeur
        return array(
            "coucheCode" => $coucheCode,
            "coucheDispatcher" => $coucheDispatcher
        );
    }

    /**
     * Rend le template dynamiser à partir de :
     * - Le template static
     * - La définition des contenu à injecter sous forme de fichier de configuration (.fs.yml)
     *
     * @param $sourcePath
     * @param bool|false $edit
     * @param bool|false $js
     * @return mixed|string
     */
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