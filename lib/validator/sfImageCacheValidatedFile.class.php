<?php

class sfImageCacheValidatedFile extends sfValidatedFile
{
  public function __construct($originalName, $type, $tempName, $size, $path = null)
  {
    if (!$this->variants) 
    {
      throw new sfException('Please set the protected property "$variants" in the sfThumbnailValidatedFile subclass');
    }
    
    parent::__construct($originalName, $type, $tempName, $size, $path);
  }
  
  public function save($file = null, $fileMode = 0666, $create = true, $dirMode = 0777)
  {
    $savedName = parent::save($file, $fileMode, $create, $dirMode);
    $fileInfo  = pathinfo($this->getSavedName());
    
    foreach ($this->variants as $variant) 
    {
      $sizeFunc = sprintf('get%sSize', sfInflector::camelize($variant));
      
      if (!method_exists($this, $sizeFunc)) 
      {
        throw new sfException(sprintf('Please create a %s function to return the image variant width and height', $sizeFunc));
      }
      
      $params = $this->$sizeFunc();
      
      if (!isset($params['width']) || !isset($params['height'])) 
      {
        throw new InvalidArgumentException(sprintf('Please make sure both "width" and "height" are set for method "%s"', $sizeFunc));
      }
      
      $pathFunc = sprintf('get%sPath', sfInflector::camelize($variant));
      
      // Configure a custom path to upload your variant
      $savePath = method_exists($this, $pathFunc) ? $this->$pathFunc() : $this->getPath();
      
      $this->createThumbnail($params['width'], $params['height'], sprintf('%s/%s-%s', $savePath, $variant, $fileInfo['basename']));
    }

    return $savedName;
  }
  
  protected function createThumbnail($width, $height, $name)
  {
    $thumbnail = new sfThumbnail($width, $height);
    $thumbnail->loadFile($this->getSavedName());
    $thumbnail->save($name);
  }
}
