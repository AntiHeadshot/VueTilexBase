<?php

class TilexError
{
  public $message;
  public $statusCode;
  public $path;
  public $method;
  public $parameters;

  function __construct($message, $statusCode, $path, $method, $parameters)
  {
    $this->message = $message;
    $this->statusCode = $statusCode;
    $this->path = $path;
    $this->method = $method;
    $this->parameters = $parameters;
  }
}

class TilexCall
{
  public $path;
  private $callback;
  public  $params;
  public $refFunc;

  function __construct($path, $callback)
  {
    $this->path = $path;
    $this->callback = $callback;

    $this->refFunc = new ReflectionFunction($callback);
    $this->params = $this->refFunc->getParameters();
  }

  function Call($paramsArray)
  {
    call_user_func_array($this->callback, $paramsArray);
  }
}

class Tilex
{

  private $_calls = array();
  private $_default;
  private $_path;

  function __construct()
  {
    $this->_path = dirname($_SERVER["PHP_SELF"]);
  }

  function GetDocumentation()
  {
    $documentation = new class
    {
    };

    $returnTypes = [];

    foreach ($this->_calls as $method => $calls) {
      $documentation->calls[$method] = [];
      foreach ($calls as  $call) {
        $callDoc = $documentation->calls[$method][] = new class
        {
        };
        $callDoc->path = $call->path;

        if (sizeof($call->params) > 0) {
          $callDoc->params = [];
          foreach ($call->params as $param) {
            $paramDoc = $callDoc->params[$param->name] = new class
            {
            };

            if ($param->isOptional()) {
              $paramDoc->optional = true;

              $defaltValue  = $param->getDefaultValue();
              if ($defaltValue != null)
                $paramDoc->default = $defaltValue;
            } else
              $paramDoc->optional = false;

            if ($param->hasType())
              $paramDoc->type = (string) $param->getType();
          }
        }

        if ($call->refFunc->hasReturnType()) {
          $returnType = $call->refFunc->getReturnType();
          $returnTypes[(string) $returnType] = $returnType;
          $callDoc->returnType = (string) $returnType;
        }

        $comment = $call->refFunc->getDocComment();
        if ($comment) {
          $comment = explode("@", preg_replace("~(^|\r\n)(\s*\*)?~", "\n", trim(substr($comment, 3, -2))));

          $callDoc->comment = trim($comment[0]);

          for ($i = 1; $i < sizeof($comment); $i++) {
            $paramComment = $comment[$i];
            $paramComment = explode(" ", $paramComment, 2);
            if ($callDoc->params[$paramComment[0]] == null)
              echo 'Parameter comment for missing parameter ' . $callDoc->path . '.' . $paramComment[0];

            $callDoc->params[$paramComment[0]]->comment = trim($paramComment[1]);
          }
        }
      }
    }

    if (sizeof($returnTypes)) {
      $documentation->returnTypes = [];

      foreach ($returnTypes as $name => $type) {
        $typeDoc = $documentation->returnTypes[$name] = new class
        {
        };
        $classType = new ReflectionClass($type->getName());
        $typeDoc->Props = [];
        foreach ($classType->getProperties() as $prop) {
          if ($prop->isPublic()) {
            $propDoc = $typeDoc->Props[$prop->name] = new class
            {
            };
            $comment = $prop->getDocComment();
            if ($comment) {
              $comment = explode("@", trim(preg_replace("~(^|\r\n)(\s*\*)?~", "\n", trim(substr($comment, 3, -2)))));
              $propDoc->comment = trim($comment[0]);
              for ($i = 1; $i < sizeof($comment); $i++) {
                $paramComment = $comment[$i];
                $paramComment = explode(" ", $paramComment, 2);

                if ($paramComment[0] == 'var')
                  $propDoc->type = trim($paramComment[1]);
                else
                  $propDoc->params[$paramComment[0]] = trim($paramComment[1]);
              }
            }
          }
        }
      }
    }
    return $documentation;
  }

  public function Get($path, $callback)
  {
    $this->AddCall('GET', $path, $callback);
  }

  public function Post($path, $callback)
  {
    $this->AddCall('POST', $path, $callback);
  }

  public function Delete($path, $callback)
  {
    $this->AddCall('DELETE', $path, $callback);
  }

  public function Add($method, $path, $callback)
  {
    $this->AddCall($method, $path, $callback);
  }

  function
  default($callback)
  {
    $this->_default = $callback;
  }

  private function AddCall($method, $path, $callback)
  {
    if (!isset($this->_calls[$method])) {
      $this->_calls[$method] = array();
    }
    $this->_calls[$method][] = new TilexCall($path, $callback);
  }

  public static function Error($mesage, $statusCode = 400)
  {
    header('Content-type: application/json');
    http_response_code($statusCode);
    die(json_encode(new TilexError($mesage, $statusCode, parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $_SERVER['REQUEST_METHOD'], ['GET' => $_GET, 'POST' => $_POST])));
  }

  public static function Return($object, $statusCode = 200)
  {
    header('Content-type: application/json');
    http_response_code($statusCode);
    if ($object !== null)
      die(json_encode($object));
    die('""');
  }

  public function Call()
  {
    $calledPath = substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen($this->_path) + 1);
    $method = strtoupper($_SERVER['REQUEST_METHOD']);
    $paths = $this->_calls[$method];

    if ($_POST != null && $_GET != null)
      $glbl = array_merge($_POST, $_GET);
    else if ($method == 'GET') {
      $glbl = $_GET;
    } else {
      $glbl = $_POST;
    }

    if (isset($paths)) {
      foreach ($paths as $call) {
        if (Tilex::TryCall($method, $calledPath, $call->path, $call, $glbl)) {
          return;
        }
      }
    }

    if (isset($this->_default)) {
      $array = ['path' => $calledPath, 'method' => $method];
      $params = array();
      $refFunc = new ReflectionFunction($this->_default);
      foreach ($refFunc->getParameters() as $param) {
        if (isset($array[$param->name])) {
          array_push($params, $array[$param->name]);
        }
      }
      call_user_func_array($this->_default, $params);
      http_response_code(404);
    } else {
      Tilex::Error("Not Found", 404);
    }
  }

  private static function TryCall($method, $input, $structure, $call, $glbl)
  {
    $array = ['path' => $input, 'method' => $method];
    if (Tilex::GetParts($input, $structure, $array)) {
      $params = array();
      foreach ($call->params as $param) {
        if (isset($array[$param->name])) {
          array_push($params, $array[$param->name]);
        } else if (isset($glbl[$param->name])) {
          array_push($params, $glbl[$param->name]);
        } else {
          if ($param->isOptional())
            array_push($params, $param->getDefaultValue());
          else
            return false;
        }
      }

      $call->Call($params);
      return true;
    }
    return false;
  }

  private static function Split($string, $index)
  {
    return preg_split('/(?<=.{' . $index . '})/', $string, 2);
  }

  private static function Multiexplode($delimiters, $string)
  {
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return $launch;
  }

  public static function GetParts($input, $structure, &$array)
  {
    $lastName = NULL;
    try {
      $parts = Tilex::Multiexplode(['{', '}'], $structure);
      for ($i = 0; $i < count($parts); $i++) {
        if ($i % 2 == 0) {
          if (isset($lastName)) {
            if (strlen($parts[$i]) < 1) {
              $pos = strlen($input);
            } else {
              $pos = strpos($input, $parts[$i]);
              if ($pos < 1) {
                return false;
              }
            }
            list($array[$lastName], $input) = Tilex::Split($input, $pos);
          }

          $length = strlen($parts[$i]);
          if ($length > strlen($input))
            return false;
          list($inputPart, $input) = Tilex::Split($input, $length);
          if ($inputPart != $parts[$i]) {
            return false;
          }
        } else {
          $lastName = $parts[$i];
        }
      }
      if (strlen($input) > 0)
        return false;
      return true;
    } catch (Exception $ex) {
      return false;
    }
  }
}
