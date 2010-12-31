sfImageCachePlugin
===================

This plugin requires that you install either `sfThumbnailPlugin` or `sfImageTransformPlugin` for image transformation. 
`sfImageTransformPlugin` is used by default, but you can change this to `sfThumbnailPlugin` in `app.yml`

    all:
      imagecache:
        transformer:    sfThumbnailPlugin
        




