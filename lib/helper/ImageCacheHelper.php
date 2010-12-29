<?php

function imagecache_path($source, $cacheName, $absolute = false)
{
  $file       = basename($source);
  $sourcePath = canonicalize_path(sprintf('%s/%s', sfConfig::get('sf_web_dir'), image_path($source)));
  $cachePath  = sprintf('%s/%s-%s', get_imagecache_service()->getImageCacheWebDir(), $cacheName, $file);
  $webPath    = sprintf('%s/%s', sfConfig::get('app_imagecache_web_path'), basename($cachePath));

  if (!is_readable($cachePath) && is_writable(dirname($cachePath)) && is_readable($sourcePath))
  {
    $dimensions = array_merge(array('width' => '', 'height' => ''), sfConfig::get('app_imagecache_'.$cacheName));
    get_imagecache_service()->createCachedImage($sourcePath, $cachePath, $dimensions['width'], $dimensions['height']);

    return image_path($webPath, $absolute);
  }
  
  return image_path($source, $absolute);
}

function imagecache_tag($source, $cacheName, $options = array())
{
  $path       = imagecache_path($source, $cacheName);
  $dimensions = sfConfig::get('app_imagecache_'.$cacheName, array());
  
  return image_tag($path, array_merge($options, $dimensions));
}

function get_imagecache_service()
{
  return sfApplicationConfiguration::getActive()
    ->getPluginConfiguration('sfImageCachePlugin')
    ->getImageCacheService();
}

function canonicalize_path($path)
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