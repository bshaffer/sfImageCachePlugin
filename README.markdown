sfImageCachePlugin
===================

Installation
------------

This plugin requires that you install either `sfThumbnailPlugin` or 
`sfImageTransformPlugin` for image transformation. `sfImageTransformPlugin` 
is used by default, but you can change this to `sfThumbnailPlugin` in `app.yml`

    all:
      imagecache:
        transformer:    sfThumbnailPlugin

Run symfony's `plugin:publish-assets` task in order to symlink 
`web/sfImageCachePlugin` to `cache/sfImageCachePlugin`.  This allows your 
cached images to be cleared with the `cache:clear` task, while keeping them 
web-accessible

    $ php symfony plugin:publish-assets

Usage
-----

This plugin caches images of various sizes in your cache directory.  
Create imagecache profiles and call them using the `imagecache_tag` 
and `imagecache_path` methods in `ImageCacheHelper`.  Configure your 
imagecache profiles in `app.yml`:

    all:
      imagecache
        profile_photo
          width:  100
          height: 200

Then call this profile in your views using the helper methods

    <!-- myviewSuccess.php -->
    <?php use_helper('ImageCache') ?>
    <?php echo imagecache_tag($user['photo'], 'profile_photo', array('alt' => $user->getName())) ?>
    <img src="<?php echo imagecache_path($user['photo'], 'profile_photo) ?>" />

Configuration
-------------

Various things can be configured at the global level via `app.yml`. All
of these options are present in the `app.yml` packaged with the plugin
along with description for each.

    all:
      imagecache:
        transformer:      sfImageTransformPlugin
        service_class:    sfImageCacheService
        web_path:         /sfImageCachePlugin/cached_images

The `transformer` option specifies the plugin used to do the heavy lifting
of the image transformation.  

The `service_class` option allows you to subclass the `sfImageCacheService`
class.

The `web_path` option tells the helpers where to place the cached images.
