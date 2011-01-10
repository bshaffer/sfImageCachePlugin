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
  
  public function getImageCacheSystemPath($source, $cacheName)
  {
    return sprintf('%s/%s-%s', $this->getImageCacheWebDir(), $cacheName, basename($source));
  }
  
  public function getImageCacheOptions($cacheName)
  {
    return array_merge(array('width' => '', 'height' => ''), sfConfig::get('app_imagecache_'.$cacheName));
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
  
  public function getSourcePath($source)
  {
    return $this->canonicalize_path(sprintf('%s/%s', sfConfig::get('sf_web_dir'), $source)); 
  }
  
  protected function canonicalize_path($path)
  {
    if (empty($path))
    {
      return '';
    }

    $out = array();
    foreach (explode(DIRECTORY_SEPARATOR, $path) as $i => $fold)
    {
      if ('' == $fold || '.' == $fold)
      {
        continue;
      }

      if ('..' == $fold && $i > 0 && '..' != end($out))
      {
        array_pop($out);
      }
      else
      {
        $out[] = $fold;
      }
    }

    $result  = DIRECTORY_SEPARATOR == $path[0] ? DIRECTORY_SEPARATOR : '';
    $result .= implode(DIRECTORY_SEPARATOR, $out);
    $result .= DIRECTORY_SEPARATOR == $path[strlen($path) - 1] ? DIRECTORY_SEPARATOR : '';

    return $result;
  }
}
