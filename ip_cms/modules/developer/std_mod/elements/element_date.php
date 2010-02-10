<?php
/**
 * @package   Library
 * @copyright Copyright (C) 2009 JSC Apro media.
 * @license   GNU/GPL, see license.html
 */
namespace Modules\developer\std_mod;


if (!defined('BACKEND')) exit;
class ElementDate extends Element{ //data element in area
  var $regExpression;
  var $regExpressionError;

  function __construct($variables){
    global $parametersMod;
    $this->order = true;
    
    $this->regExpression = '/^([1-3][0-9]{3,3})-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][0-9]|3[0-1])$/';
    $this->regExpressionError = $parametersMod->getValue('developer', 'std_mod', 'admin_translations', 'incorrect_date_format');
    
    parent::__construct($variables);
    
    if(!isset($variables['dbField']) || $variables['dbField'] == ''){
      $backtrace = debug_backtrace();
      if(isset($backtrace[0]['file']) && $backtrace[0]['line'])
        trigger_error('ElementDateTime dbField parameter not set. (Error source: '.$backtrace[0]['file'].' line: '.$backtrace[0]['line'].' ) ');
      else
        trigger_error('ElementDateTime dbField parameter not set.');
      exit;
    }
    
    foreach ($variables as $name => $value) {
      switch ($name){
        case 'regExpressionError':
          $this->regExpressionError = $value;
          break;
        case 'dbField':
          $this->dbField = $value;
          break;
        
      }
    }

  }

  function printFieldNew($prefix, $parentId = null, $area = null){
    $html = new StdModHtmlOutput();
    $value = null;

    $html->date($prefix, $this->defaultValue, $this->disabledOnInsert);

    return $html->html;

  }



  function printFieldUpdate($prefix, $record, $area){
    $value = null;

    $value = substr($record[$this->dbField], 0, 10);

    $html = new StdModHtmlOutput();
    $html->date($prefix, $value, $this->disabledOnUpdate);
    return $html->html;
  }

  function getParameters($action, $prefix, $area){
    if($action == 'insert'){
      if($this->visibleOnInsert && !$this->disabledOnInsert && $action == 'insert'){
        return array("name"=>$this->dbField, "value"=>$_REQUEST[''.$prefix]);
      }else{
        return array("name"=>$this->dbField, "value"=>$this->defaultValue);
      }
    }
    if($action == 'update'){
      if($this->visibleOnUpdate && !$this->disabledOnUpdate && $action == 'update'){
        return array("name"=>$this->dbField, "value"=>$_REQUEST[''.$prefix]);
      }
    }  
  }


  function previewValue($record, $area){
    $answer = htmlspecialchars(substr($record[$this->dbField], 0, 10));
    return $answer;

  }

  function checkField($prefix, $action, $area){
    global $parametersMod;
    

    if($action != 'update' || !$this->disabledOnUpdate && $this->visibleOnUpdate){
      if($action == 'insert' && $this->disabledOnInsert || $action == 'insert' && !$this->visibleOnInsert)
        $_POST[$prefix] = $this->defaultValue;
      
      if ($this->required && (!isset($_POST[$prefix]) || $_POST[$prefix] == null))
      return $parametersMod->getValue('developer', 'std_mod','admin_translations','error_required');
  
      if($this->regExpression != null){
        if($_POST[$prefix] == null || preg_match($this->regExpression, $_POST[$prefix]))
        return null;
        else
        return $this->regExpressionError.'&nbsp;';
      }
            
    }

    return null;
  }




  function printSearchField($level, $key, $area){
    if (isset($_REQUEST['search'][$level][$key]))
    $value = $_REQUEST['search'][$level][$key];
    else
    $value = '';
    return '<input name="search['.$level.']['.$key.']" value="'.htmlspecialchars($value).'" />';
  }

  function getFilterOption($value, $area){
    return " ".$this->dbField." like '%".mysql_real_escape_string($value)."%' ";
  }


}

