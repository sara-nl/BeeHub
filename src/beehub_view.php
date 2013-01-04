<?php
/*************************************************************************
 * Copyright ©2007-2012 SARA b.v., Amsterdam, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package BeeHub
 */

/**
 * This class separates the view scripts from the rest of the code
 *
 * View scripts consist mainly out of HTML with just parts PHP. This class not
 * only makes it easy to call those scripts, but also runs them in their own
 * scope. Furthermore, you can use templates for general parts of the views
 * (mostly the header and footer of an HTML page) and view scripts can set
 * template variables and even force a different template to be used, or
 * specify to use no template at all.
 *
 * In config.ini you can set the option 'defaultTemplate' in the 'environment'
 * section if you want to use a default template.
 *
 * @package BeeHub
 */
class BeeHub_View {
  /**
   * @var  String  The path to the view itself
   */
  private $viewPath;
  /**
   * @var  String  The path to the template
   */
  private $templatePath = null;
  /**
   * @var  Array  All variables available in the view (keys are the variable names)
   */
  private $vars;
  /**
   * @var  Array  All variables available in the template (keys are the variable names)
   */
  private $templateVars;

  /**
   * Constructor
   *
   * @param  String  $viewPath      The path to the view
   * @param  String  $templatePath  Optional: the path to the template. If not set, the default template will be used or if this is not specified in config.ini, only the view will be parsed. Set to false if you don't even want to use the default view.
   */
  public function __construct($viewPath, $templatePath = null) {
    // Here we determine that views are to be placed in ../views/
    $basePath = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
    $viewPath = realpath($basePath . $viewPath);

    if (substr($viewPath, 0, strlen($basePath)) != $basePath) {
      throw new Exception("View path points to illegal location. It should point to a location inside the 'views' directory");
    }
    if (is_readable($viewPath)) {
      $this->viewPath = $viewPath;
    }else{
      throw new Exception('View path does not exist: ' . $viewPath);
    }

    if (is_string($templatePath)) {
      $this->setTemplate($templatePath);
    }elseif (($templatePath !== false) && !empty(BeeHub::$CONFIG['view']['default_template'])) {
      $this->setTemplate(BeeHub::$CONFIG['view']['default_template']);
    }
  }

  /**
   * Set a template. This will be parsed besides the view itself.
   *
   * @param   String       $templatePath  The path to the template, relative to the 'views' directory
   * @return  BeeHub_View                 This instance itself for chaining methods
   */
  public function setTemplate($templatePath) {
    // Here we determine that templates are to be placed in ../templates/
    $basePath = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    $templatePath = realpath($basePath . $templatePath);

    if (substr($templatePath, 0, strlen($basePath)) != $basePath) {
      throw new Exception("Template path points to illegal location. It should point to a location inside the 'templates' directory");
    }
    if (is_readable($templatePath)) {
      $this->templatePath = $templatePath;
    }else{
      throw new Exception('Template path does not exist: ' . $templatePath);
    }

    return $this;
  }

  /**
   * Unset the template
   *
   * If this function is called, no template will be parsed. Even the default
   * template, if specified in config.ini, will be ignored.
   *
   * @return  BeeHub_View          This instance itself for chaining methods
   */
  public function unsetTemplate() {
    $this->templatePath = null;
    return $this;
  }

  /**
   * Sets a variable to be available in the view
   *
   * @param   String       $name   The name of the variable
   * @param   String       $value  The value of the variable
   * @return  BeeHub_View          This instance itself for chaining methods
   */
  public function setVar($name, $value) {
    if (!is_string($name)) {
      throw new Exception('View variable name should be a string');
    }
    $this->vars[$name] = $value;
    return $this;
  }

  /**
   * Sets a variable to be available in the template
   *
   * @param   String       $name   The name of the variable
   * @param   String       $value  The value of the variable
   * @return  BeeHub_View          This instance itself for chaining methods
   */
  public function setTemplateVar($name, $value) {
    if (!is_string($name)) {
      throw new Exception('Template variable name should be a string');
    }elseif ($name == 'content') {
      throw new Exception("Template variable name can not be 'header', 'content' or 'footer'");
    }
    $this->templateVars[$name] = $value;
    return $this;
  }

  /**
   * Parse the view and, if specified, the template and print the result.
   *
   * This will parse both view and template. Note that although output will be
   * printed to the output stream, it will first be stored in an output buffer.
   * To immediately stream a view, use {@link parseRawView}, although this will
   * prevent you from using a template!
   *
   * @return  void
   */
  public function parseView() {
    print($this->getParsedView());
  }

  /**
   * Parse the view and stream it to the output stream
   *
   * The difference between this function and {@link parseView} is that this
   * function will not parse the template and streams the output directly to
   * the output stream.
   *
   * @return  void
   */
  public function parseRawView() {
    foreach ($this->vars as $name => $value) {
      $$name = $value;
    }
    require($this->viewPath);
  }

  /**
   * Parse the view and, if specified, the template and return it as a string
   *
   * @return  String  The parsed view and template
   */
  public function getParsedView() {
    ob_start();
    $this->parseRawView();
    $content = ob_get_contents();
    ob_end_clean();

    if (!is_null($this->templatePath)) {
      foreach ($this->templateVars as $name => $value) {
        $$name = $value;
      }
      ob_start();
      include($this->templatePath);
      $content = ob_get_contents();
      ob_end_clean();
    }

    return $content;
  }
}

//  End of file
