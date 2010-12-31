<?php

/**
* 
*/
class sfImageCachePluginConfiguration extends sfPluginConfiguration
{
  protected $_imageCacheService;

  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'listenToContextLoadFactories'));
    $this->dispatcher->connect('command.post_command', array($this, 'observeCommandPostCommand'));
    $this->dispatcher->connect('request.filter_parameters', array($this, 'filterRequestParametersEvent'));
  }
  
  /**
   * Returns the image cache service, which acts like a singleton
   * within the current configuration instance.
   *
   * @return ioImageCacheService
   */
  public function getImageCacheService()
  {
    return $this->_imageCacheService;
  }

  /**
   * Automatic plugin modules and helper loading
   *
   * @param  sfEvent  $event
   */
  public function listenToContextLoadFactories(sfEvent $event)
  {
    // create the image cache service
    $this->_imageCacheService = $this->_createImageCacheService();
    
    // create symlinks if necessary
    if (!is_writable($this->getImageCacheService()->getImageCacheWebDir())) 
    {
      $this->createSymlinks();
    }
    
    // Ensure plugins are enabled
    $transformPlugin = sfConfig::get('app_imagecache_transformer', 'sfImageTransformPlugin');
    
    if (!in_array($transformPlugin, $this->configuration->getPlugins()) && substr($transformPlugin, -6) == 'Plugin') 
    {
      throw new sfException(sprintf('Please enabled plugin "%s" or specify a different plugin for imagecache transformation using sfConfig app_imagecache_transformorer', $transformPlugin));
    }
    
    if ($transformPlugin == 'sfImageTransformPlugin') 
    {
      // Force autodetect
      $settings = sfConfig::get('app_sfImageTransformPlugin_mime_type');
      $settings['auto_detect'] = true;
      sfConfig::set('app_sfImageTransformPlugin_mime_type', $settings);
    }
  }

  /**
   * Creates the image cache service class.
   *
   * @param sfUser $user
   * @return ioImageCacheService
   */
  protected function _createImageCacheService()
  {
    $class = sfConfig::get('app_imagecache_service_class', 'sfImageCacheService');

    return new $class($this->dispatcher);
  }
  
  /**
   * observe the command post_command event
   * 
   * Add a symlink to the cache directory where your images are cached
   * set app_imagecache_cache_dir to false to disable this
   */
  public function observeCommandPostCommand(sfEvent $event)
  {
    $task = $event->getSubject();

    if ($task->getName() == 'publish-assets')
    {
      $this->createSymlinks($task->getFilesystem());
    }
  }
  
  public function createSymlinks($filesystem = null)
  {
    if (!$filesystem) 
    {
      $filesystem = new sfFilesystem($this->dispatcher);
    }
    
    if (!$this->_imageCacheService) 
    {
      $this->_imageCacheService = $this->_createImageCacheService();
    }
    
    umask(0000);
    
    $cachePath = $this->getImageCacheService()->getCacheSymlinkDir();
    $webPath   = $this->getImageCacheService()->getWebSymlinkDir();
    $filesystem->mkdirs($cachePath.DIRECTORY_SEPARATOR.'cached_images', 0777);
    $filesystem->relativeSymlink($cachePath, $webPath);
  }
  
  /**
   * listen to the request filter_parameters event
   * Add "Web Root" to settings if it doesn't exist
   */
  public function filterRequestParametersEvent(sfEvent $event, $parameters)
  {
    if (!sfConfig::get('sf_web_root'))
    {
      $context = $event->getParameters();

      // Set the root URL of the website in sfConfig
      $root = 'http'.(isset($context['is_secure']) && $context['is_secure'] ? 's' : '').'://'.$context['host'];
      sfConfig::set('sf_web_root', $root);
    }
    
    return $parameters;
  }
}
