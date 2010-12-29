<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInputFileEditable represents an upload HTML input tag with the possibility
 * to remove a previously uploaded file.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormInputFileEditable.class.php 28709 2010-03-23 13:57:27Z Kris.Wallsmith $
 */
class sfWidgetFormImage extends sfWidgetFormInputFile
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * path:         The current image web source path (required)
   *  * with_delete:  Whether to add a delete checkbox or not
   *  * delete_label: The delete label used by the template
   *  * template:     The HTML template to use to render this widget when in edit mode
   *                  The available placeholders are:
   *                    * %input% (the image upload widget)
   *                    * %delete% (the delete checkbox)
   *                    * %delete_label% (the delete label text)
   *                    * %image% (the image tag)
   *
   * If with_delete is enabled, this widget renders an additional widget named after the
   * upload widget with a "_delete" suffix. So, when creating a form,
   * don't forget to add a validator for this additional field.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInputFile
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->setOption('type', 'file');
    $this->setOption('needs_multipart', true);

    $this->addRequiredOption('path');
    $this->addOption('with_delete', false);
    $this->addOption('delete_label', 'remove the current file');
    $this->addOption('template', '%image%<br />%input%<br />%delete% %delete_label%');
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $input = parent::render($name, $value, $attributes, $errors);

    if ($this->getOption('with_delete'))
    {
      $deleteName = ']' == substr($name, -1) ? substr($name, 0, -1).'_delete]' : $name.'_delete';

      $delete = $this->renderTag('input', array_merge(array('type' => 'checkbox', 'name' => $deleteName), $attributes));
      $deleteLabel = $this->translate($this->getOption('delete_label'));
      $deleteLabel = $this->renderContentTag('label', $deleteLabel, array_merge(array('for' => $this->generateId($deleteName))));
    }
    else
    {
      $delete = '';
      $deleteLabel = '';
    }

    return strtr($this->getOption('template'), array('%input%' => $input, '%delete%' => $delete, '%delete_label%' => $deleteLabel, '%image%' => $this->getImageTag($value, $attributes)));
  }

  protected function getImageTag($value, $attributes)
  {
    sfProjectConfiguration::getActive()->loadHelpers(array('Asset'));

    return $value ? $this->renderTag('img', array_merge(array(
      'src' => image_path($this->getOption('path').'/'.$value)), $attributes)) : '';
  }
}
