<?php

function imagecache_path($source, $cacheName, $absolute = false)
{
  $cachePath = get_imagecache_service()->getImageCacheSystemPath($source, $cacheName);
  $webPath   = sprintf('%s/%s', sfConfig::get('app_imagecache_web_path'), basename($cachePath));

  if (is_readable($cachePath)) 
  {
    return image_path($webPath, $absolute);
  }
  
  $sourcePath = get_imagecache_service()->getSourcePath(image_path($source));

  if (is_writable(dirname($cachePath)) && is_readable($sourcePath))
  {
    $options = get_imagecache_service()->getImageCacheOptions($cacheName);
    
    if (isset($options['asynchronous']) && $options['asynchronous'])
    {
      return url_for('@imagecache_get?' . http_build_query(array('cache_name' => $cacheName, 'source' => $source)));
    }
    
    get_imagecache_service()->createCachedImage($sourcePath, $cachePath, $options['width'], $options['height']);

    return image_path($webPath, $absolute);
  }

  return image_path($source, $absolute);
}

function imagecache_tag($source, $cacheName, $options = array())
{
  $path                = imagecache_path($source, $cacheName);
  $dimensions          = sfConfig::get('app_imagecache_'.$cacheName, array());
  $options['raw_name'] = true; // we have already calculated the src attribute via imagecache_path
  
  return image_tag($path, array_merge($options, $dimensions));
}

function get_imagecache_service()
{
  return sfApplicationConfiguration::getActive()
    ->getPluginConfiguration('sfImageCachePlugin')
    ->getImageCacheService();
}
