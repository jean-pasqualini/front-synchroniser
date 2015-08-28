<?php

namespace FrontSynchroniser;

use FluentDOM\Query as FluentDOMQuery;
use Symfony\Component\CssSelector\CssSelector;

class Render {

    protected $uniq;

    protected $errors = array();

    protected $varTemplate = "";
    
    protected $contents = array();

    public function __construct(array $contents = array())
    {
        $this->uniq = uniqid();

        $this->varTemplate = "__".$this->uniq."__xxxxx__".$this->uniq."__";
        
        $this->contents = $contents;
    }

    public function getVarTemplate()
    {
        return $this->varTemplate;
    }

    public function getErrors()
    {
        return $this->errors;
    }
    
    public function renderStatic($path)
    {
        return file_get_contents($path);
    }
    
    public function addWidget($raw)
    {
        return str_replace("xxxxx", $this->addContent($raw), $this->varTemplate);//"<span class='editable'>".$raw."</span>";
    }
    
    public function postRender($output, $edit)
    {
        $configuration = $this->contents;
        
            $pattern = "/".str_replace("xxxxx", "([0-9]+)", $this->varTemplate)."/i";

            $output = preg_replace_callback($pattern, function($item) use($configuration)
            {
                //exit(print_r($item, true));

                return "</code></pre><div class='editable' contenteditable='true' title='".$item[1]."'>".$this->getContent($item[1])."</div><pre><code class='html'>";
            }, $output);

            return $output;
    }
    
    public function addContent($raw)
    {
        $id = count($this->contents["dom"]) + 1;
        
        $this->contents["dom"][$id]["content"] = $raw;
        
        return $id;
    }
    
    public function getContent($id)
    {
        return $this->contents["dom"][$id]["content"];
    }

    public function render($htmlObject, $configuration, $edit)
    {
        /**
        if($edit)
        {
            $collectionDom = $htmlObject->find("*");
            
            foreach($collectionDom as $item)
            {
                $attributes = $item->getAttributes();
                
                foreach($attributes as $key => $attributeItem)
                {
                    $item->setAttribute($key, $this->addWidget($attributeItem));
                }
            }
        }
        */
        foreach($configuration as $id => $configuration)
        {
            $raw = $configuration["content"];

            if($edit === true)
            {
                $raw = str_replace("xxxxx", $id, $this->varTemplate);
            }

            try {
                /** @var \FluentDOM\Element $collection */
                $collection = $htmlObject->find($configuration["selector"]);
            }
            catch (InvalidArgumentException $e)
            {
                exit("invalid selector ".$configuration["selector"]);
            }

            if($collection->count() == 0)
            {
                $this->errors[] = "Le code injecter dans ".$configuration["selector"]." n'a pu être injecter";
            }

            foreach($collection as $item)
            {
                $item->nodeValue = $raw;
            }
        }
    }
}