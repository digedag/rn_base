<?php

namespace Sys25\RnBase\Tests;

use Exception;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;
use tx_rnbase_util_Spyc;
use tx_rnbase_util_Typo3Classes;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018-2021 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Basis Testcase.
 *
 * @author Michael Wagner
 */
abstract class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * whether global variables should be backuped.
     *
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * whether static attributes should be backuped.
     *
     * @var bool
     */
    protected $backupStaticAttributes = false;

    /**
     * Initialize database connection in $GLOBALS and connect if requested.
     */
    public static function prepareLegacyTypo3DbGlobal()
    {
        if (!TYPO3::isTYPO80OrHigher()) {
            return;
        }

        $db = $GLOBALS['TYPO3_DB'];
        if (!$db->isConnected()) {
            \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->initializeTypo3DbGlobal();
        }
    }

    /**
     * Sample:
     * self::createConfigurations(
     *   array(), 'rn_base', 'rn_base',
     *   tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class),
     *   tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass())
     * );.
     *
     * @param array  $configurationArray
     * @param string $extensionKey
     * @param string $qualifier
     *
     * @return ConfigurationInterface
     *
     * @deprecated use tx_rnbase_tests_Utility::createConfigurations() instead
     */
    protected static function createConfigurations(
        array $configurationArray,
        $extensionKey,
        $qualifier = ''
    ) {
        return call_user_func_array(
            ['tx_rnbase_tests_Utility', 'createConfigurations'],
            func_get_args()
        );
    }

    /**
     * Wrapper for deprecated getMock method.
     *
     * Taken From nimut/testing-framework
     *
     * @param string $originalClassName
     * @param array  $methods
     * @param array  $arguments
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param bool   $cloneArguments
     * @param bool   $callOriginalMethods
     * @param null   $proxyTarget
     *
     * @throws \PHPUnit_Framework_Exception
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMock(
        $originalClassName,
        $methods = [],
        array $arguments = [],
        $mockClassName = '',
        $callOriginalConstructor = true,
        $callOriginalClone = true,
        $callAutoload = true,
        $cloneArguments = false,
        $callOriginalMethods = false,
        $proxyTarget = null
    ) {
        if (method_exists($this, 'createMock')) {
            $mockBuilder = $this->getMockBuilder($originalClassName)
                ->setMethods($methods)
                ->setConstructorArgs($arguments)
                ->setMockClassName($mockClassName)
                ->setProxyTarget($proxyTarget);
            if (!$callOriginalConstructor) {
                $mockBuilder->disableOriginalConstructor();
            }
            if (!$callOriginalClone) {
                $mockBuilder->disableOriginalClone();
            }
            if (!$callAutoload) {
                $mockBuilder->disableAutoload();
            }
            if ($cloneArguments) {
                $mockBuilder->enableArgumentCloning();
            }
            if ($callOriginalMethods) {
                $mockBuilder->enableProxyingToOriginalMethods();
            }

            return $mockBuilder->getMock();
        }

        return parent::getMock(
            $originalClassName,
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments,
            $callOriginalMethods,
            $proxyTarget
        );
    }

    /**
     * Returns a mock of.
     *
     * @param array  $record
     * @param string $class
     *
     * @return \tx_rnbase_model_base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModel(
        $record = null,
        $class = 'tx_rnbase_model_base',
        array $methods = []
    ) {
        // $record has to be an array,
        // if there is an scalar value,
        // a db select fill be performed to get the record
        if (!is_array($record)) {
            $record = ['uid' => (int) $record];
        }

        if (!tx_rnbase::load($class)) {
            throw new Exception('The model "'.$class.'" could not be loaded.');
        }

        $isNewModel = (
            is_subclass_of($class, 'Tx_Rnbase_Domain_Model_Base') ||
            'Tx_Rnbase_Domain_Model_Base' == $class
        );

        // create the mock
        $model = $this->getMock(
            $class,
            array_merge(
                [
                    $isNewModel ? 'loadRecord' : 'reset',
                    'getColumnWrapped',
                ],
                $methods
            ),
            [$record]
        );

        $model
            ->expects(self::any())
            ->method($isNewModel ? 'loadRecord' : 'reset')
            ->will(self::returnSelf());
        $model
            ->expects(self::never())
            ->method('getColumnWrapped');

        return $model;
    }

    /**
     * Converts a YAML to a model mock.
     *
     * YAML example:
     * _model: Tx_Rnbase_Domain_Model_Base
     * _record:
     *   uid: 3
     * getCategory:
     *   _model: Tx_Rnbase_Domain_Model_Data
     *   _record:
     *   uid: 5
     * getCategories:
     *   -
     *   _model: Tx_Rnbase_Domain_Model_Data
     *   _record:
     *     uid: 12
     *   -
     *   _model: Tx_Rnbase_Domain_Model_Data
     *   _record:
     *     uid: 13
     *
     * @param mixed $data              Usually the yaml file
     * @param bool  $tryToLoadYamlFile
     *
     * @return \tx_rnbase_model_base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function loadYaml($data, $tryToLoadYamlFile = true)
    {
        // there is no array, so convert the yaml content or file
        if ($tryToLoadYamlFile && !is_array($data)) {
            $data = tx_rnbase_util_Spyc::YAMLLoad($data);
        }

        // we have an model
        if (isset($data['_model'])) {
            // find all getter methods to mock.
            $getters = $this->yamlFindGetters($data);

            $clazz = (
                empty($data['_model']) ? 'tx_rnbase_model_base' : $data['_model']
            );

            $model = $this->getModel(
                (array) ($data['_record']),
                $clazz,
                $getters
            );

            // mock the getters and return the value from the nested yaml
            foreach ($getters as $getter) {
                (
                    $model
                    ->expects(self::any())
                    ->method($getter)
                    ->will($this->returnValue($this->loadYaml($data[$getter], false)))
                );
            }

            return $model;
        } elseif (is_array($data)) {
            $array = [];
            foreach ($data as $field => $value) {
                if (is_array($value)) {
                    $value = $this->loadYaml($value);
                }
                $array[$field] = $value;
            }

            return $array;
        }
        // else: return the data only

        return $data;
    }

    /**
     * Returns all getters.
     * Getters are fields beginning with "get" and a following uppercase char.
     *
     * @param array $array
     *
     * @return \tx_rnbase_model_base|\PHPUnit_Framework_MockObject_MockObject
     */
    private function yamlFindGetters(
        array $array
    ) {
        $getters = [];

        foreach (array_keys($array) as $field) {
            if ('g' === $field[0] &&
                'e' === $field[1] &&
                't' === $field[2] &&
                strtoupper($field[3]) === $field[3]
            ) {
                $getters[] = $field;
            }
        }

        return $getters;
    }

    /**
     * Helper function to call protected methods.
     * This method is taken from TYPO3 BaseTestCase initialy.
     *
     * The classic way:
     *   ->callInaccessibleMethod($object, $methodname, $arg1, $arg2)
     *
     * The new way, with support for arguments as reference:
     *   ->callInaccessibleMethod(array($object, $methodname), array($arg1, $arg2))
     *
     * @param object|array $object The object to be invoked or an a array with object and $name
     * @param string|array $name   the name of the method to call or the arguments array
     *
     * @return mixed
     */
    protected function callInaccessibleMethod($object, $name)
    {
        if (is_array($object)) {
            // the new way (supports arguments as references)
            // $object is a array (with object and name) and $name a arguments array!
            $arguments = $name;
            list($object, $name) = $object;
        } else {
            // the classic way to read the arguments
            // Remove first two arguments ($object and $name)
            $arguments = func_get_args();
            array_splice($arguments, 0, 2);
        }

        $reflectionObject = new \ReflectionObject($object);
        $reflectionMethod = $reflectionObject->getMethod($name);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $arguments);
    }

    /**
     * Helper function to set an inaccessible property.
     *
     * @param object $object
     * @param string $property
     * @param mixed  $value
     */
    protected function setInaccessibleProperty($object, $property, $value = null)
    {
        $refObject = new ReflectionObject($object);
        $refProperty = $refObject->getProperty($property);
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $value);
    }

    /**
     * Helper function to get an inaccessible property.
     *
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    protected function getInaccessibleProperty($object, $property)
    {
        $refObject = new ReflectionObject($object);
        $refProperty = $refObject->getProperty($property);
        $refProperty->setAccessible(true);

        return $refProperty->getValue($object);
    }

    /**
     * Helper function to set an inaccessible property.
     *
     * @param string $class
     * @param string $property
     * @param mixed  $value
     */
    protected function setInaccessibleStaticProperty($class, $property, $value = null)
    {
        $reflectedClass = new ReflectionClass($class);
        $reflectedProperty = $reflectedClass->getProperty($property);
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($value);
    }

    /**
     * Creates a mock object which allows for calling protected methods
     * and access of protected properties.
     * This method is taken from TYPO3 BaseTestCase.
     *
     * @param string        $originalClassName       name of class t
     * @param array<string> $methods                 name of the methods to mock
     * @param array         $arguments               arguments to pass to constructor
     * @param string        $mockClassName           the class name to use for the mock class
     * @param bool          $callOriginalConstructor whether to call the constructor
     * @param bool          $callOriginalClone       whether to call the __clone method
     * @param bool          $callAutoload            whether to call any autoload function
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     *                                                                                                  a mock of $originalClassName with access methods added
     *
     * @see \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase::getAccessibleMock
     */
    protected function getAccessibleMock(
        $originalClassName,
        array $methods = [],
        array $arguments = [],
        $mockClassName = '',
        $callOriginalConstructor = true,
        $callOriginalClone = true,
        $callAutoload = true
    ) {
        if ('' === $originalClassName) {
            throw new \InvalidArgumentException('$originalClassName must not be empty.', 1334701880);
        }

        return $this->getMock(
            $this->buildAccessibleProxy($originalClassName),
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload
        );
    }

    /**
     * Creates a proxy class of the specified class which allows
     * for calling even protected methods and access of protected properties.
     * This method is taken from TYPO3 BaseTestCase.
     *
     * @param string $className Name of class to make available, must not be empty
     *
     * @return string Fully qualified name of the built class, will not be empty
     *
     * @see Tx_Extbase_Tests_Unit_BaseTestCase::buildAccessibleProxy
     */
    protected function buildAccessibleProxy($className)
    {
        $accessibleClassName = uniqid('Tx_Rnbase_Phpunit_AccessibleProxy');
        $class = new \ReflectionClass($className);
        $abstractModifier = $class->isAbstract() ? 'abstract ' : '';

        $interface = '';
        if (class_exists('\\Nimut\\TestingFramework\\MockObject\\AccessibleMockObjectInterface')) {
            $interface = 'implements \\Nimut\\TestingFramework\\MockObject\\AccessibleMockObjectInterface';
        } elseif (class_exists('\\Tx_Phpunit_Interface_AccessibleObject')) {
            $interface = 'implements \\Tx_Phpunit_Interface_AccessibleObject';
        }

        // @TODO: #43 refactor to a stand alone interface
        eval(
            $abstractModifier.'class '.$accessibleClassName.
            ' extends '.$className.' '.$interface.' {'.
            'public function _call($methodName) {'.
            'if ($methodName === \'\') {'.
            'throw new \InvalidArgumentException(\'$methodName must not be empty.\', 1334663993);'.
            '}'.
            '$args = func_get_args();'.
            'return call_user_func_array(array($this, $methodName), array_slice($args, 1));'.
            '}'.
            'public function _callRef('.
            '$methodName, &$arg1 = NULL, &$arg2 = NULL, &$arg3 = NULL, &$arg4 = NULL, &$arg5= NULL, &$arg6 = NULL, '.
            '&$arg7 = NULL, &$arg8 = NULL, &$arg9 = NULL'.
            ') {'.
            'if ($methodName === \'\') {'.
            'throw new \InvalidArgumentException(\'$methodName must not be empty.\', 1334664210);'.
            '}'.
            'switch (func_num_args()) {'.
            'case 0:'.
            'throw new RuntimeException(\'The case of 0 arguments is not supposed to happen.\', 1334703124);'.
            'break;'.
            'case 1:'.
            '$returnValue = $this->$methodName();'.
            'break;'.
            'case 2:'.
            '$returnValue = $this->$methodName($arg1);'.
            'break;'.
            'case 3:'.
            '$returnValue = $this->$methodName($arg1, $arg2);'.
            'break;'.
            'case 4:'.
            '$returnValue = $this->$methodName($arg1, $arg2, $arg3);'.
            'break;'.
            'case 5:'.
            '$returnValue = $this->$methodName($arg1, $arg2, $arg3, $arg4);'.
            'break;'.
            'case 6:'.
            '$returnValue = $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5);'.
            'break;'.
            'case 7:'.
            '$returnValue = $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);'.
            'break;'.
            'case 8:'.
            '$returnValue = $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);'.
            'break;'.
            'case 9:'.
            '$returnValue = $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8);'.
            'break;'.
            'case 10:'.
            '$returnValue = $this->$methodName('.
            '$arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9'.
            ');'.
            'break;'.
            'default:'.
            'throw new \InvalidArgumentException('.
            '\'_callRef currently only allows calls to methods with no more than 9 parameters.\''.
            ');'.
            '}'.
            'return $returnValue;'.
            '}'.
            'public function _set($propertyName, $value) {'.
            'if ($propertyName === \'\') {'.
            'throw new \InvalidArgumentException(\'$propertyName must not be empty.\', 1334664355);'.
            '}'.
            '$this->$propertyName = $value;'.
            '}'.
            'public function _setRef($propertyName, &$value) {'.
            'if ($propertyName === \'\') {'.
            'throw new \InvalidArgumentException(\'$propertyName must not be empty.\', 1334664545);'.
            '}'.
            '$this->$propertyName = $value;'.
            '}'.
            'public function _setStatic($propertyName, $value) {'.
            'if ($propertyName === \'\') {'.
            'throw new \InvalidArgumentException(\'$propertyName must not be empty.\', 1344242602);'.
            '}'.
            'self::$$propertyName = $value;'.
            '}'.
            'public function _get($propertyName) {'.
            'if ($propertyName === \'\') {'.
            'throw new \InvalidArgumentException(\'$propertyName must not be empty.\', 1334664967);'.
            '}'.
            'return $this->$propertyName;'.
            '}'.
            'public function _getStatic($propertyName) {'.
            'if ($propertyName === \'\') {'.
            'throw new \InvalidArgumentException(\'$propertyName must not be empty.\', 1344242603);'.
            '}'.
            'return self::$$propertyName;'.
            '}'.
            '}'
        );

        return $accessibleClassName;
    }

    /**
     * Same as getMockForAbstractClass, with mockedMethods as secnd param only.
     *
     * @param string $originalClassName
     * @param array  $mockedMethods
     * @param array  $arguments
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param bool   $cloneArguments
     *
     * @throws \PHPUnit_Framework_Exception
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockForAbstract(
        $originalClassName,
        $mockedMethods = [],
        array $arguments = [],
        $mockClassName = '',
        $callOriginalConstructor = true,
        $callOriginalClone = true,
        $callAutoload = true,
        $cloneArguments = false
    ) {
        return $this->getMockForAbstractClass(
            $originalClassName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );
    }

    protected function resetIndependentEnvironmentCache()
    {
        $property = new ReflectionProperty(
            tx_rnbase_util_Typo3Classes::getGeneralUtilityClass(),
            'indpEnvCache'
        );
        $property->setAccessible(true);
        $property->setValue(null, []);
    }
}
