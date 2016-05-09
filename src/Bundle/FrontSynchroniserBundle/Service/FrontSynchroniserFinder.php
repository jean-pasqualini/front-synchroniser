<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 03/08/15
 * Time: 06:39
 */

namespace FrontSynchroniserBundle\Service;


use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class FrontSynchroniserFinder {

    protected $configuration;

    protected $meta;

    protected $indexMeta;

    protected $kernel;

    protected $frontSynchroniserManager;

    public function __construct($configuration, KernelInterface $kernel, FrontSynchroniserManager $frontSynchroniserManager)
    {
        $this->configuration = $configuration;

        $this->kernel = $kernel;

        $this->frontSynchroniserManager = $frontSynchroniserManager;

        $this->loadMetadata();
    }

    protected function getIndexByMeta($metadata)
    {
        return array(
            $metadata["template"],
        );
    }

    protected function buildMetadata($fsData, $file)
    {
        /** @var $file \SplFileInfo */

        return array(
            "name" => $file->getFilename(),
            'id_link' => base64_encode($file->getPathname()),
            "template" => $fsData["template"]
        );
    }

    protected function storeMetadata($metadata)
    {
        $idMeta = uniqid();

        $this->meta[$idMeta] = $metadata;

        $this->buildIndexMetadata($metadata, $idMeta);

        return $idMeta;
    }

    protected function buildIndexMetadata($metadata, $idMeta)
    {
        $indexMeta = $this->getIndexByMeta($metadata);

        foreach($indexMeta as $indexMetaItem)
        {
            if(!isset($this->indexMeta[$indexMetaItem])) $this->indexMeta[$indexMetaItem] = array();
            
            $this->indexMeta[$indexMetaItem][] = $idMeta;
        }
    }
    
    public function find($index)
    {
        $idMetas = $this->indexMeta[$index];
        
        $metas = $this->meta;
        
        return array_map(function($item) use ($metas) { return $metas[$item]; }, $idMetas);
    }

    protected function loadMetadata()
    {
        $bundles = $this->kernel->getBundles();

        $finder = new Finder();

        foreach($bundles as $bundle)
        {
            $finder->in($bundle->getPath());
        }

        $finder->in($this->kernel->getRootDir());

        $finder->name("*.fs.yml");

        foreach($finder as $file)
        {
            $fsData = $this->frontSynchroniserManager->getMetadataFromPath($file->getPathname());

            $metadata = $this->buildMetadata($fsData, $file);

            $this->storeMetadata($metadata);
        }
    }

}