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
    if ($this->isImageCacheProfileName($cacheName)) 
    {
      return sprintf('%s/%s-%s', $this->getImageCacheWebDir(), $cacheName, basename($source));
    }

    $suffix  = array();
    $info    = pathinfo($source);
    $options = $this->getImageCacheOptions($cacheName);

    // Unset options that do not effect the cache
    unset($options['asynchronous']);
    
    // Cause who doesn't like a nice-looking filename?
    if (isset($options['width']) && isset($options['height'])) 
    {
      $suffix[] = $options['width'] . 'x' . $options['height'];
      unset($options['width'], $options['height']);
    }
    
    // append addl options
    foreach ($options as $key => $value) 
    {
      $suffix[] = "$key-$value";
    }
    
    return sprintf('%s/%s_%s.%s', $this->getImageCacheWebDir(), $info['filename'], implode('_', $suffix), $info['extension']);
  }
  
  public function getImageCacheOptions($cacheName)
  {
    $baseOptions = array_merge(array('width' => '', 'height' => ''));
    
    // imagecache profile name
    if ($this->isImageCacheProfileName($cacheName)) 
    {
      $options = sfConfig::get('app_imagecache_'.$cacheName);
      
    }
    // array of options passed
    elseif (is_array($cacheName)) 
    {
      if (isset($cacheName['cache_name'])) 
      {
        $options = array_merge($cacheName, sfConfig::get('app_imagecache_'.$cacheName['cache_name']));
      }
      else
      {
        $options = $cacheName;
      }
    }
    // array string passed
    elseif(!$options = sfToolkit::stringToArray($cacheName))
    {
      throw new sfException('Unable to parse cache options.  Please pass an imagecache profile name or imagecache options in string or array format');
    }

    // profile name passed
    return array_merge($baseOptions, $options); 
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
  
  public function isImageCacheProfileName($cacheName)
  {
    return is_string($cacheName) && sfConfig::has('app_imagecache_'.$cacheName);
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
