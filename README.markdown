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

    <!-- output image tag for imagecache-->
    <?php echo imagecache_tag($user['photo'], 'profile_photo', array('alt' => $user->getName())) ?>

    <!-- output src attribute for imagecache -->
    <img src="<?php echo imagecache_path($user['photo'], 'profile_photo) ?>" />

Some options included in the imagecache profile are the following

  * `width`: The width of the cached image.  Leave this empty if you want it to be set based on the height
  * `height`: The height of the cached image.  Leave this empty if you want it to be set based on the width
  * `asynchronous`:  When set to true, a web action is called to render the cached image.  This is useful 
  when you have a whole lot of cached images on a page.  Without this option enabled, the server will try 
  to create all these cached images at once, and may time out.  You must enable the "imagecache" module to
  use this.

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


TODO
----

* Profiles have subfolders
* Allow the passing of dimensions to the imagecache function (instead of profile)
** Incorporate .htaccess in profile directory to call php script to create file
* Allow for transform attributes - for extendibility
* Add "remote" option to pull remote images
* Hook into cache system to allow use of cache drivers, lifetimes and sfNoCache
* Behavior to clear cache upon image field update, generate cache using a task or on save


