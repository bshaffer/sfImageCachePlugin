<?php

/**
* Class to allow for cropping of images to fill entire space of maxWidth and maxHeight
*/
class sfImageResizeAndCropGD extends sfImageTransformAbstract
{

  /**
   * Cropped area width.
  */
  protected $width;

  /**
   * Cropped area height
  */
  protected $height;
  
  
  /**
   * Construct an sfImageCropAndResize object.
   *
   * @param integer
   * @param integer
   */
  public function __construct($width, $height)
  {
    $this->setWidth($width);
    $this->setHeight($height);
  }

  /**
   * set the width.
   *
   * @param integer
   */
  public function setWidth($width)
  {
    if (is_numeric($width))
    {
      $this->width = (int)$width;

      return true;
    }

    return false;
  }

  /**
   * returns the width of the thumbnail
   *
   * @return integer
   */
  public function getWidth()
  {
    return $this->width;
  }

  /**
   * set the height.
   *
   * @param integer
   */
  public function setHeight($height)
  {
    if (is_numeric($height))
    {
      $this->height = (int)$height;

      return true;
    }

    return false;
  }

  /**
   * returns the height of the thumbnail
   *
   * @return integer
   */
  public function getHeight()
  {
    return $this->height;
  }

  protected function calculateProportions($sourceWidth, $sourceHeight, $maxWidth, $maxHeight)
  {
    $proportions = array();
    if ($sourceWidth / $maxWidth > $sourceHeight / $maxHeight)
    {
      // Broad format
      $proportions['width']  = ($sourceWidth * $maxHeight) / $sourceHeight;
      $proportions['height'] = $maxHeight;
    }
    else
    {
      // High format
      $proportions['width']  = $maxWidth;
      $proportions['height'] = ($sourceHeight * $maxWidth) / $sourceWidth;
    }
    
    return $proportions;
  }
  
  protected function transform(sfImage $image)
  {
    if ($image->getWidth() == $this->getWidth() && $image->getHeight() == $this->getHeight())
    {
      return $image;
    }

    $proportions = $this->calculateProportions($image->getWidth(), $image->getHeight(), $this->getWidth(), $this->getHeight());

    $resize = new sfImageResizeSimpleGD($proportions['width'], $proportions['height']);
    $resize->execute($image);
    
    $left = - ($this->getWidth() - $proportions['width']) / 2;
    $top  = - ($this->getHeight() - $proportions['height']) / 2;

    $crop = new sfImageCropGD($left, $top, $this->getWidth(), $this->getHeight());
    $crop->execute($image);
    
    return $image;
  }
}
