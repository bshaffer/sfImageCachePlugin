<?php

/**
* Class to allow for cropping of images to fill entire space of maxWidth and maxHeight
*/
class sfGDAdapterCrop extends sfGDAdapter
{
  public function loadData($thumbnail, $image, $mime)
  {
    if (in_array($mime,$this->imgTypes))
    {
      $this->source = imagecreatefromstring($image);
      $this->sourceWidth = imagesx($this->source);
      $this->sourceHeight = imagesy($this->source);
      $this->sourceMime = $mime;

      $this->calculateProportions();
      $this->doCreateImage();

      return true;
    }
    else
    {
      throw new Exception(sprintf('Image MIME type %s not supported', $mime));
    }
  }
  
  public function loadFile($thumbnail, $image)
  {
    $imgData = @GetImageSize($image);

    if (!$imgData)
    {
      throw new Exception(sprintf('Could not load image %s', $image));
    }

    if (in_array($imgData['mime'], $this->imgTypes))
    {
      $loader = $this->imgLoaders[$imgData['mime']];
      if(!function_exists($loader))
      {
        throw new Exception(sprintf('Function %s not available. Please enable the GD extension.', $loader));
      }

      $this->source = $loader($image);
      $this->sourceWidth = $imgData[0];
      $this->sourceHeight = $imgData[1];
      $this->sourceMime = $imgData['mime'];

      $this->calculateProportions();
      $this->doCreateImage($imgData);
      
      return true;
    }
    else
    {
      throw new Exception(sprintf('Image MIME type %s not supported', $imgData['mime']));
    }
  }
  
  public function calculateProportions()
  {
    if ($this->sourceWidth / $this->maxWidth > $this->sourceHeight / $this->maxHeight)
    {
      // Broad format
      $this->thumbWidth = ($this->sourceWidth * $this->maxHeight) / $this->sourceHeight;
      $this->thumbHeight = $this->maxHeight;
    }
    else
    {
      // High format
      $this->thumbWidth = $this->maxWidth;
      $this->thumbHeight = ($this->sourceHeight * $this->maxWidth) / $this->sourceWidth;
    }
  }
  
  protected function doCreateImage($imgData)
  {
    $this->thumb = imagecreatetruecolor($this->maxWidth, $this->maxHeight);

    // Allocate for colors / transparency
    imagealphablending($this->thumb, false);
    $color = imagecolortransparent($this->thumb, imagecolorallocatealpha($this->thumb, 0, 0, 0, 127));
    imagefill($this->thumb, 0, 0, $color);
    imagesavealpha($this->thumb, true);
    
    
    if ($imgData[0] == $this->maxWidth && $imgData[1] == $this->maxHeight)
    {
      $this->thumb = $this->source;
    }
    else
    {
      imagecopyresampled($this->thumb, $this->source, - ($this->thumbWidth-$this->maxWidth) / 2, - ($this->thumbHeight - $this->maxHeight) / 2, 0, 0, $this->thumbWidth, $this->thumbHeight, $this->sourceWidth, $this->sourceHeight);
    }
  }
}
