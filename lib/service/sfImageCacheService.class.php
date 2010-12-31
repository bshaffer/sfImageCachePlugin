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
    $method = sprintf('createCachedImageWith%s', ucfirst(sfConfig::get('app_imagecache_transformer', 'sfImageTransformPlugin')));
    return $this->$method($imagePath, $savePath, $width, $height);
  }
    
  protected function createCachedImageWithSfImageTransformPlugin($imagePath, $savePath, $width, $height)
  {
    $orig  = new sfImage($imagePath);
    $image = $orig->saveAs($savePath);

    if (extension_loaded('gd'))
    {
      $crop = new sfImageResizeAndCropGD($width, $height);
      $crop->execute($image);
    }
    else
    {
      $image->resize($width, $height);
    }
    
    $image->save();
  }
  
  protected function createCachedImageWithSfThumbnailPlugin($imagePath, $savePath, $width, $height)
  {
    /*
      TODO - Allow configuration of "type"
    */
    if (extension_loaded('gd'))
    {
      // The "crop" method is default.
      $adapterClass = 'sfGDAdapterResizeAndCrop';
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
