<?php

/**
* 
*/
class sfImageCacheService
{
  protected
    $dispatcher;
    
  public function __construct(sfEventDispatcher $dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }
  
  public function createCachedImage($imagePath, $savePath, $width, $height)
  {
    /*
      TODO - Allow configuration of "type"
    */
    if (extension_loaded('gd'))
    {
      // The "crop" method is default.
      $adapterClass = 'sfGDAdapterCrop';
    }
    else
    {
      $adapterClass = 'sfImageMagickAdapter';
    }
    
    $thumbnail = new sfThumbnail($width, $height, true, true, 75, $adapterClass);
    $thumbnail->loadFile($imagePath);
    $thumbnail->save($savePath);
  }
  
  public function getCacheSymlinkDir()
  {
    return sfConfig::get('sf_cache_dir').DIRECTORY_SEPARATOR.'sfImageCachePlugin';
  }

  public function getWebSymlinkDir()
  {
    return sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'sfImageCachePlugin';
  }

  public function getImageCacheCacheDir()
  {
    return $this->getCacheSymlinkDir().DIRECTORY_SEPARATOR.'cached_images';
  }
  
  public function getImageCacheWebDir()
  {
    return $this->getWebSymlinkDir().DIRECTORY_SEPARATOR.'cached_images';
  }
}
