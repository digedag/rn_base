<?php

namespace Sys25\RnBase\Utility;

/**
 * Spyc -- A Simple PHP YAML Class.
 *
 * @version 0.3
 *
 * @author Chris Wanstrath <chris@ozmm.org>
 * @author Vlad Andersen <vlad@oneiros.ru>
 *
 * @see http://spyc.sourceforge.net/
 *
 * @copyright Copyright 2005-2006 Chris Wanstrath
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
/**
 * The Simple PHP YAML Class.
 *
 * This class can be used to read a YAML file and convert its contents
 * into a PHP array.  It currently supports a very limited subsection of
 * the YAML spec.
 *
 * Usage:
 * <code>
 *   $parser = new Spyc;
 *   $array  = $parser->load($file);
 * </code>
 */
class Spyc
{
    /**#@+
    * @access private
    * @var mixed
    */
    private $_haveRefs;

    private $_allNodes;

    private $_allParent;

    private $_lastIndent;

    private $_lastNode;

    private $_inBlock;

    private $_isInline;

    private $_dumpIndent;

    private $_dumpWordWrap;

    private $_containsGroupAnchor = false;

    private $_containsGroupAlias = false;

    private $path;

    private $result;

    private $LiteralBlockMarkers = ['>', '|'];

    private $LiteralPlaceHolder = '___YAML_Literal_Block___';

    private $SavedGroups = [];

    /**#@+
    * @access private
    * @var mixed
    */
    private $_nodeId;

    /**
     * Load YAML into a PHP array statically.
     *
     * The load method, when supplied with a YAML stream (string or file),
     * will do its best to convert YAML in a file into a PHP array.  Pretty
     * simple.
     *  Usage:
     *  <code>
     *   $array = Spyc::YAMLLoad('lucky.yaml');
     *   print_r($array);
     *  </code>
     *
     * @param string $input Path of YAML file or string containing YAML
     *
     * @return array
     */
    public static function yamlLoad($input)
    {
        $Spyc = new self();

        return $Spyc->load($input);
    }

    /**
     * Dump YAML from PHP array statically.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as nothing.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'FALSE' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @param array $array    PHP array
     * @param int   $indent   Pass in FALSE to use the default, which is 2
     * @param int   $wordwrap Pass in 0 for no wordwrap, FALSE for default (40)
     *
     * @return string
     */
    public static function yamlDump($array, $indent = false, $wordwrap = false)
    {
        $spyc = new self();

        return $spyc->dump($array, $indent, $wordwrap);
    }

    /**
     * Dump PHP array to YAML.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as tasteful.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'FALSE' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @param array $array    PHP array
     * @param int   $indent   Pass in FALSE to use the default, which is 2
     * @param int   $wordwrap Pass in 0 for no wordwrap, FALSE for default (40)
     *
     * @return string
     */
    public function dump($array, $indent = false, $wordwrap = false)
    {
        // Dumps to some very clean YAML.  We'll have to add some more features
        // and options soon.  And better support for folding.

        // New features and options.
        if (false === $indent or !is_numeric($indent)) {
            $this->_dumpIndent = 2;
        } else {
            $this->_dumpIndent = $indent;
        }

        if (false === $wordwrap or !is_numeric($wordwrap)) {
            $this->_dumpWordWrap = 40;
        } else {
            $this->_dumpWordWrap = $wordwrap;
        }

        // New YAML document
        $string = "---\n";

        // Start at the base of the array and move through it.
        foreach ($array as $key => $value) {
            $string .= $this->_yamlize($key, $value, 0);
        }

        return $string;
    }

    /**
     * Attempts to convert a key / value array item to YAML.
     *
     * @param string $key    The name of the key
     * @param string $value  The value of the item
     * @param int    $indent The indent of the current node
     *
     * @return string
     */
    private function _yamlize($key, $value, $indent)
    {
        if (is_array($value)) {
            // It has children.  What to do?
            // Make it the right kind of item
            $string = $this->_dumpNode($key, null, $indent);
            // Add the indent
            $indent += $this->_dumpIndent;
            // Yamlize the array
            $string .= $this->_yamlizeArray($value, $indent);
        } elseif (!is_array($value)) {
            // It doesn't have children.  Yip.
            $string = $this->_dumpNode($key, $value, $indent);
        }

        return $string;
    }

    /**
     * Attempts to convert an array to YAML.
     *
     * @param array $array The array you want to convert
     * @param int $indent The indent of the current level
     *
     * @return string
     */
    private function _yamlizeArray($array, $indent)
    {
        if (is_array($array)) {
            $string = '';
            foreach ($array as $key => $value) {
                $string .= $this->_yamlize($key, $value, $indent);
            }

            return $string;
        } else {
            return false;
        }
    }

    /**
     * Returns YAML from a key and a value.
     *
     * @param string $key The name of the key
     * @param mixed $value The value of the item
     * @param int $indent The indent of the current node
     *
     * @return string
     */
    private function _dumpNode($key, $value, $indent)
    {
        // do some folding here, for blocks
        if (false !== strpos($value, "\n") || false !== strpos($value, ': ') || false !== strpos($value, '- ')) {
            $value = $this->_doLiteralBlock($value, $indent);
        } else {
            $value = $this->_doFolding($value, $indent);
        }

        if (is_bool($value)) {
            $value = ($value) ? 'true' : 'false';
        }

        $spaces = str_repeat(' ', $indent);

        if (is_int($key)) {
            // It's a sequence
            $string = $spaces.'- '.$value."\n";
        } else {
            // It's mapped
            $string = $spaces.$key.': '.$value."\n";
        }

        return $string;
    }

    /**
     * Creates a literal block for dumping.
     *
     * @param $value
     * @param $indent int The value of the indent
     *
     * @return string
     */
    private function _doLiteralBlock($value, $indent)
    {
        $exploded = explode("\n", $value);
        $newValue = '|';
        $indent += $this->_dumpIndent;
        $spaces = str_repeat(' ', $indent);
        foreach ($exploded as $line) {
            $newValue .= "\n".$spaces.trim($line);
        }

        return $newValue;
    }

    /**
     * Folds a string of text, if necessary.
     *
     * @param string $value The string you wish to fold
     *
     * @return string
     */
    private function _doFolding($value, $indent)
    {
        // Don't do anything if wordwrap is set to 0
        if (0 === $this->_dumpWordWrap) {
            return $value;
        }

        if (strlen($value) > $this->_dumpWordWrap) {
            $indent += $this->_dumpIndent;
            $indent = str_repeat(' ', $indent);
            $wrapped = wordwrap($value, $this->_dumpWordWrap, "\n$indent");
            $value = ">\n".$indent.$wrapped;
        }

        return $value;
    }

    /* LOADING FUNCTIONS */

    public function load($input)
    {
        $source = $this->loadFromSource($input);
        if (empty($source)) {
            return [];
        }
        $this->path = [];
        $this->result = [];

        for ($i = 0, $len = count($source); $i < $len; ++$i) {
            $line = $source[$i];
            $lineIndent = $this->_getIndent($line);
            $this->path = $this->getParentPathByIndent($lineIndent);
            $line = $this->stripIndent($line, $lineIndent);
            if ($this->isComment($line)) {
                continue;
            }

            if ($literalBlockStyle = $this->startsLiteralBlock($line)) {
                $line = rtrim($line, $literalBlockStyle."\n");
                $literalBlock = '';
                $line .= $this->LiteralPlaceHolder;

                while ($this->literalBlockContinues($source[++$i], $lineIndent)) {
                    $literalBlock = $this->addLiteralLine($literalBlock, $source[$i], $literalBlockStyle);
                }
                --$i;
            }
            $lineArray = $this->_parseLine($line);
            if ($literalBlockStyle) {
                $lineArray = $this->revertLiteralPlaceHolder($lineArray, $literalBlock);
            }

            $this->addArray($lineArray, $lineIndent);
        }

        return $this->result;
    }

    private function loadFromSource($input)
    {
        if (!empty($input) && false === strpos($input, "\n") && file_exists($input)) {
            return file($input);
        }

        $foo = explode("\n", $input);
        foreach ($foo as $k => $_) {
            $foo[$k] = trim($_, "\r");
        }

        return $foo;
    }

    /**
     * Finds and returns the indentation of a YAML line.
     *
     * @param string $line A line from the YAML file
     *
     * @return int
     */
    private function _getIndent($line)
    {
        if (!preg_match('/^ +/', $line, $match)) {
            return 0;
        }
        if (!empty($match[0])) {
            return strlen($match[0]);
        }

        return 0;
    }

    /**
     * Parses YAML code and returns an array for a node.
     *
     * @param string $line A line from the YAML file
     *
     * @return array
     */
    private function _parseLine($line)
    {
        if (!$line) {
            return [];
        }
        $line = trim($line);
        if (!$line) {
            return [];
        }

        if ($group = $this->nodeContainsGroup($line)) {
            $this->addGroup($line, $group);
            $line = $this->stripGroup($line, $group);
        }

        if ($this->startsMappedSequence($line)) {
            return $this->returnMappedSequence($line);
        }

        if ($this->startsMappedValue($line)) {
            return $this->returnMappedValue($line);
        }

        if ($this->isArrayElement($line)) {
            return $this->returnArrayElement($line);
        }

        return $this->returnKeyValuePair($line);
    }

    /**
     * Finds the type of the passed value, returns the value as the new type.
     *
     * @param string $value
     *
     * @return mixed
     */
    private function _toType($value)
    {
        if (false !== strpos($value, '#')) {
            $value = trim(preg_replace('/#(.+)$/', '', $value));
        }

        if (preg_match('/^("(.*)"|\'(.*)\')/', $value, $matches)) {
            $value = (string) preg_replace('/(\'\'|\\\\\')/', "'", end($matches));
            $value = preg_replace('/\\\\"/', '"', $value);
        } elseif (preg_match('/^\\[(.+)\\]$/', $value, $matches)) {
            // Inline Sequence

            // Take out strings sequences and mappings
            $explode = $this->_inlineEscape($matches[1]);

            // Propogate value array
            $value = [];
            foreach ($explode as $v) {
                $value[] = $this->_toType($v);
            }
        } elseif (false !== strpos($value, ': ') && !preg_match('/^{(.+)/', $value)) {
            // It's a map
            $array = explode(': ', $value);
            $key = trim($array[0]);
            array_shift($array);
            $value = trim(implode(': ', $array));
            $value = $this->_toType($value);
            $value = [$key => $value];
        } elseif (preg_match('/{(.+)}$/', $value, $matches)) {
            // Inline Mapping

            // Take out strings sequences and mappings
            $explode = $this->_inlineEscape($matches[1]);

            // Propogate value array
            $array = [];
            foreach ($explode as $v) {
                $array += $this->_toType($v);
            }
            $value = $array;
        } elseif ('null' == strtolower($value) or '' == $value or '~' == $value) {
            $value = null;
        } elseif (preg_match('/^[0-9]+$/', $value)) {
            $value = (int) $value;
        } elseif (in_array(
            strtolower($value),
            ['true', 'on', '+', 'yes', 'y']
        )) {
            $value = true;
        } elseif (in_array(
            strtolower($value),
            ['false', 'off', '-', 'no', 'n']
        )) {
            $value = false;
        } elseif (is_numeric($value)) {
            $value = (float) $value;
        }
        //     else {
        //       // Just a normal string, right?
        //     }

        //  print_r ($value);
        return $value;
    }

    /**
     * Used in inlines to check for more inlines or quoted strings.
     *
     * @return array
     */
    private function _inlineEscape($inline)
    {
        // There's gotta be a cleaner way to do this...
        // While pure sequences seem to be nesting just fine,
        // pure mappings and mappings with sequences inside can't go very
        // deep.  This needs to be fixed.

        $saved_strings = [];

        // Check for strings
        $regex = '/(?:(")|(?:\'))((?(1)[^"]+|[^\']+))(?(1)"|\')/';
        if (preg_match_all($regex, $inline, $strings)) {
            $saved_strings = $strings[0];
            $inline = preg_replace($regex, 'YAMLString', $inline);
        }
        unset($regex);

        // Check for sequences
        if (preg_match_all('/\[(.+)\]/U', $inline, $seqs)) {
            $inline = preg_replace('/\[(.+)\]/U', 'YAMLSeq', $inline);
            $seqs = $seqs[0];
        }

        // Check for mappings
        if (preg_match_all('/{(.+)}/U', $inline, $maps)) {
            $inline = preg_replace('/{(.+)}/U', 'YAMLMap', $inline);
            $maps = $maps[0];
        }

        $explode = explode(', ', $inline);

        // Re-add the sequences
        if (!empty($seqs)) {
            $i = 0;
            foreach ($explode as $key => $value) {
                if (false !== strpos($value, 'YAMLSeq')) {
                    $explode[$key] = str_replace('YAMLSeq', $seqs[$i], $value);
                    ++$i;
                }
            }
        }

        // Re-add the mappings
        if (!empty($maps)) {
            $i = 0;
            foreach ($explode as $key => $value) {
                if (false !== strpos($value, 'YAMLMap')) {
                    $explode[$key] = str_replace('YAMLMap', $maps[$i], $value);
                    ++$i;
                }
            }
        }

        // Re-add the strings
        if (!empty($saved_strings)) {
            $i = 0;
            foreach ($explode as $key => $value) {
                while (false !== strpos($value, 'YAMLString')) {
                    $explode[$key] = preg_replace('/YAMLString/', $saved_strings[$i], $value, 1);
                    ++$i;
                    $value = $explode[$key];
                }
            }
        }

        return $explode;
    }

    private function literalBlockContinues($line, $lineIndent)
    {
        if (!trim($line)) {
            return true;
        }
        if ($this->_getIndent($line) > $lineIndent) {
            return true;
        }

        return false;
    }

    private function addArray($array, $indent)
    {
        $key = key($array);
        if (!isset($array[$key])) {
            return false;
        }
        if ([] === $array[$key]) {
            $array[$key] = '';
        }
        $value = $array[$key];
        $tempPath = self::flatten($this->path);
        $_arr = [];
        eval('$_arr = $this->result'.$tempPath.';');

        if ($this->_containsGroupAlias) {
            do {
                if (!isset($this->SavedGroups[$this->_containsGroupAlias])) {
                    echo "Bad group name: $this->_containsGroupAlias.";

                    break;
                }
                $groupPath = $this->SavedGroups[$this->_containsGroupAlias];
                eval('$value = $this->result'.self::flatten($groupPath).';');
            } while (false);
            $this->_containsGroupAlias = false;
        }
        if (!is_array($_arr)) {
            $_arr = [];
        }

        // Adding string or numeric key to the innermost level or $this->arr.
        if ($key) {
            $_arr[$key] = $value;
        } else {
            if (!is_array($_arr)) {
                $key = 0;
            } else {
                $_arr[] = $value;
                end($_arr);
                $key = key($_arr);
            }
        }

        $this->path[$indent] = $key;

        eval('$this->result'.$tempPath.' = $_arr;');

        if ($this->_containsGroupAnchor) {
            $this->SavedGroups[$this->_containsGroupAnchor] = $this->path;
            $this->_containsGroupAnchor = false;
        }
    }

    private static function flatten($array)
    {
        $tempPath = [];
        if (!empty($array)) {
            foreach ($array as $_) {
                if (!is_int($_)) {
                    $_ = "'$_'";
                }
                $tempPath[] = "[$_]";
            }
        }
        $tempPath = implode('', $tempPath);

        return $tempPath;
    }

    private function startsLiteralBlock($line)
    {
        $lastChar = substr(trim($line), -1);
        if (in_array($lastChar, $this->LiteralBlockMarkers)) {
            return $lastChar;
        }

        return false;
    }

    private function addLiteralLine($literalBlock, $line, $literalBlockStyle)
    {
        $line = $this->stripIndent($line);
        $line = str_replace("\r\n", "\n", $line);

        if ('|' == $literalBlockStyle) {
            return $literalBlock.$line;
        }
        if (0 == strlen($line)) {
            return $literalBlock."\n";
        }

        if ("\n" != $line) {
            $line = trim($line, "\r\n ").' ';
        }

        return $literalBlock.$line;
    }

    private function revertLiteralPlaceHolder($lineArray, $literalBlock)
    {
        foreach ($lineArray as $k => $_) {
            if (substr($_, -1 * strlen($this->LiteralPlaceHolder)) == $this->LiteralPlaceHolder) {
                $lineArray[$k] = rtrim($literalBlock, " \r\n");
            }
        }

        return $lineArray;
    }

    private function stripIndent($line, $indent = -1)
    {
        if (-1 == $indent) {
            $indent = $this->_getIndent($line);
        }

        return substr($line, $indent);
    }

    private function getParentPathByIndent($indent)
    {
        if (0 == $indent) {
            return [];
        }

        $linePath = $this->path;
        do {
            end($linePath);
            $lastIndentInParentPath = key($linePath);
            if ($indent <= $lastIndentInParentPath) {
                array_pop($linePath);
            }
        } while ($indent <= $lastIndentInParentPath);

        return $linePath;
    }

    private function clearBiggerPathValues($indent)
    {
        if (0 == $indent) {
            $this->path = [];
        }
        if (empty($this->path)) {
            return true;
        }

        foreach ($this->path as $k => $_) {
            if ($k > $indent) {
                unset($this->path[$k]);
            }
        }

        return true;
    }

    private function isComment($line)
    {
        return preg_match('/^#/', $line);
    }

    private function isArrayElement($line)
    {
        if (!$line) {
            return false;
        }
        if ('-' != $line[0]) {
            return false;
        }
        if (strlen($line) > 3) {
            if ('---' == substr($line, 0, 3)) {
                return false;
            }
        }

        return true;
    }

    private function isHashElement($line)
    {
        if (!preg_match('/^(.+?):/', $line, $matches)) {
            return false;
        }
        $allegedKey = $matches[1];
        if ($allegedKey) {
            return true;
        }

        return false;
    }

    private function isLiteral($line)
    {
        if ($this->isArrayElement($line)) {
            return false;
        }
        if ($this->isHashElement($line)) {
            return false;
        }

        return true;
    }

    private function startsMappedSequence($line)
    {
        if (preg_match('/^-(.*):$/', $line)) {
            return true;
        }
    }

    private function returnMappedSequence($line)
    {
        $array = [];
        $key = trim(substr(substr($line, 1), 0, -1));
        $array[$key] = '';

        return $array;
    }

    private function returnMappedValue($line)
    {
        $array = [];
        $key = trim(substr($line, 0, -1));
        $array[$key] = '';

        return $array;
    }

    private function startsMappedValue($line)
    {
        if (preg_match('/^(.*):$/', $line)) {
            return true;
        }
    }

    private function returnKeyValuePair($line)
    {
        $array = [];

        if (preg_match('/^(.+):/', $line, $key)) {
            // It's a key/value pair most likely
            // If the key is in double quotes pull it out
            if (preg_match('/^(["\'](.*)["\'](\s)*:)/', $line, $matches)) {
                $value = trim(str_replace($matches[1], '', $line));
                $key = $matches[2];
            } else {
                // Do some guesswork as to the key and the value
                $explode = explode(':', $line);
                $key = trim($explode[0]);
                array_shift($explode);
                $value = trim(implode(':', $explode));
            }

            // Set the type of the value.  Int, string, etc
            $value = $this->_toType($value);
            if (empty($key)) {
                $array[] = $value;
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    private function returnArrayElement($line)
    {
        if (strlen($line) <= 1) {
            return [[]];
        } // Weird %)
        $array = [];
        $value = trim(substr($line, 1));
        $value = $this->_toType($value);
        $array[] = $value;

        return $array;
    }

    private function nodeContainsGroup($line)
    {
        if (false === strpos($line, '&') && false === strpos($line, '*')) {
            return false;
        } // Please die fast ;-)
        if (preg_match('/^(&[^ ]+)/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^(\*[^ ]+)/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(&[^" ]+$)/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(\*[^" ]+$)/', $line, $matches)) {
            return $matches[1];
        }

        return false;
    }

    private function addGroup($line, $group)
    {
        if ('&' == substr($group, 0, 1)) {
            $this->_containsGroupAnchor = substr($group, 1);
        }
        if ('*' == substr($group, 0, 1)) {
            $this->_containsGroupAlias = substr($group, 1);
        }
    }

    private function stripGroup($line, $group)
    {
        $line = trim(str_replace($group, '', $line));

        return $line;
    }
}
