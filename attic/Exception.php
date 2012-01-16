<?php

/*·************************************************************************
 * Copyright ©2007-2011 Pieter van Beek <http://pieterjavanbeek.hyves.nl/>
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
 *
 * $Id: Exception.php 170 2011-01-19 14:15:53Z kobasoft $
 **************************************************************************/

/**
 * PHP's built-in Exception class.
 *
 * This file should never be included/required. It's here only for documentation
 * purposes.
 * @package PHP
 */

/**
 * PHP's built-in Exception class.
 * @package PHP
 */
class Exception
{
  /**
   * @var string
   */
  protected $message = 'Unknown exception';   // exception message
  /**
   * @var int
   */
  protected $code = 0;                        // user defined exception code
  /**
   * @var string
   */
  protected $file;                            // source filename of exception
  /**
   * @var int
   */
  protected $line;                            // source line of exception

  /**
   * Constructor.
   * @param string $message
   * @param int $code
   */
  function __construct($message = null, $code = 0);

  /**
   * @return string
   */
  final function getMessage();                // message of exception
  /**
   * @return int
   */
  final function getCode();                   // code of exception
  /**
   * @return string
   */
  final function getFile();                   // source filename
  /**
   * @return int
   */
  final function getLine();                   // source line
  /**
   * @return array
   */
  final function getTrace();                  // an array of the backtrace()
  /**
   * @return string
   */
  final function getTraceAsString();          // formatted string of trace

  /* Overrideable */
  function __toString();                       // formatted string for display
}

