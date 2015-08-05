<?php

namespace FrontSynchroniser;

class Render {

    protected $uniq;

    protected $errors = array();

    protected $varTemplate = "";

    public function __construct()
    {
        $this->uniq = uniqid();

        $this->varTemplate = "__".$this->uniq."__xxxxx__".$this->uniq."__";
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

    public function render($htmlObject, $configuration, $edit)
    {
        foreach($configuration as $id => $configuration)
        {
            $raw = $configuration["content"];

            if($edit === true)
            {
                $raw = str_replace("xxxxx", $id, $this->varTemplate);
            }

            $collection = $htmlObject->find($configuration["selector"]);

            if($collection->count() == 0)
            {
                $this->errors[] = "Le code injecter dans ".$configuration["selector"]." n'a pu être injecter";
            }

            $collection->replaceInner($raw);
        }
    }
}