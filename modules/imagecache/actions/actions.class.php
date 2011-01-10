<?php

/**
* 
*/
class imagecacheActions extends sfActions
{
  public function executeGet(sfWebRequest $request)
  {
    $this->context->getConfiguration()->loadHelpers(array('Asset', 'ImageCache'));
    
    $content  = null;
    
    $service   = get_imagecache_service();
    $source    = $request->getParameter('source');
    $cacheName = $request->getParameter('cache_name', $request->getParameter('options'));
    
    $cachePath = get_imagecache_service()->getImageCacheSystemPath($source, $cacheName);
    $webPath   = sprintf('%s/%s', sfConfig::get('app_imagecache_web_path'), basename($cachePath));

    if (!is_readable($cachePath)) 
    {
      $sourcePath = $service->getSourcePath(image_path($source));

      if (is_writable(dirname($cachePath)) && is_readable($sourcePath))
      {
        $options = $service->getImageCacheOptions($cacheName);
  
        $service->createCachedImage($sourcePath, $cachePath, $options['width'], $options['height']);

        $content = file_get_contents($cachePath);
      }
      elseif (is_readable($sourcePath))
      {
        $cachePath = $sourcePath;
      }
      else
      {
        $this->forward404(sprintf('Source Image "%s" does not exist', $source));
      }
    }

    $this->getResponse()->setContent(file_get_contents($cachePath));
    
    return sfView::NONE;
  }
}
