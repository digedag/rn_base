<?php

use Sys25\RnBase\Exception\ExceptionHandler;
use Sys25\RnBase\Exception\ExceptionHandlerInterface;
use Sys25\RnBase\Exception\PageNotFound404;
use Sys25\RnBase\Exception\SkipActionException;
use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Utility\Arrays;
use Sys25\RnBase\Utility\Logger;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2013 René Nitzsche
 *  All rights reserved
 *
 *  Based on code by Elmar Hinz Contact: elmar.hinz@team-red.net
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/***************************************************************
 *
 * This is a refactored version of class tx_lib_controller.
 * What has been changed?
 * - uses action classes instead of action functions
 * - no default actions anymore
 * - possible to process multiple actions in one request
 *
 * Note: Javadoc is mostly not up to date.
 ***************************************************************/

/*
 * The central controller used as entry point for a plugin
 * This class should not be derived by other classes.
 *
 * ---------------------------------------------------
 * Replacement of tslib::pi_base() by MVC architecture
 * ---------------------------------------------------
 *
 * Controllers of this kind are called from TS Setup in the typical position
 * "tt_content.list.20.pluginKey" exactly like the traditional tslib::pi_base() plugins.
 *
 * The pluginKey is defined by the function tx_rnbase_util_Extensions::addPlugin()
 * within the file ext_tables.php as second element of the array
 * that is handled as first parameter to the function.
 *
 * tx_rnbase_util_Extensions::addPlugin(array(pluginLabel, pluginKey), list_type)
 *
 * -----------------------------------------
 * Easily controlled by the action classes
 * -----------------------------------------
 *
 * The controller dispatches the requests to action classes controlled by the action parameter.
 * The action parameter can come from 3 sources:
 * 1.) a POST-requests of a form
 * 2.) a GET-request of a link.
 * 3.) statically be set in TS
 *
 * ------------------------------------
 * The parameter array name: action (designator)
 * ------------------------------------
 *
 * According to the coding guidelines parameters of plugins have to be send as array with a unique
 * identifier to keep them in their own namespace. In tslib_pibase this identifier is called:
 * $prefixId. In tx_lib it is called $action.
 *
 * The easy way is to share a common designator throughout a whole extension. Unless the designator is
 * explicitly set as class variable the function getDesignator() defaults to the extension key. Always
 * access it by this function. (This function is inherited from tx_lib_pluginCommon via tx_lib_object.)
 *
 * --------------------------------------------------------
 * Better caching by finegrained usage of USER and USER_INT
 * --------------------------------------------------------
 *
 * The action parameter can be set in a static way within TS. By this a common controller class
 * can be shared by multiple plugins, which makes sense for multiple smaller static plugins.
 * That enables to differ between USER and USER_INT plugins in a more finegrained way.
 * See the extension elfaq for an example of an advanced usage of this technic.
 *
 * ----------------------------------------------------------------------------
 * Flexible to extend by other extensions via registration instead of XCLASSing
 * ----------------------------------------------------------------------------
 *
 * The set of action functions can be enlarged by other classes, even by other extensions.
 * To do so you need to register the extending classes to the global variable
 * $TYPO3_CONF_VARS['CONTROLLERS']. For more see main();
 * TODO: provide a function to register plugins.
 *
 * Used by: none
 *
 * @author René Nitzsche (rene@system25.de)
 * @package TYPO3
 * @subpackage rn_base
 */

class tx_rnbase_controller
{
    public $configurationsClassName = 'Tx_Rnbase_Configuration_Processor'; // You may overwrite this in your subclass with an own configurations class.

    public $parameters;

    public $configurations;

    public $defaultAction = 'defaultAction';

    public $cobj; // Plugins cObj instance from T3

    private $errors = [];

    /*
     * main(): A factory method for the responsible action
     *
     * --------------------------------------------------------------------
     * Summary: Finds and executes the action
     * --------------------------------------------------------------------
     *
     *      a) Parameters and configurations are loaded into objects.
     *      b) The action parameter "xy" matches the class "xyAction()".
     *      c) Registered controller classes have precedence.
     *
     * --------------------------------------------------------------------
     * The configuration goes into the configurations object
     * --------------------------------------------------------------------
     *
     * The configuration array comes in as second parameter.
     * It is the TS subtree down from the treenode of the plugins includation.
     * It is loaded into the configurations object.
     * That object is passed to all MVC classes.
     *
     * --------------------------------------------------------------------
     * The parameters go into the parameters object
     * --------------------------------------------------------------------
     *
     * The parameter array is filled from the GPvars marked by the qualifier from
     * - GET prameters from a link or redirect:  ... &designator[action]=theAction&designator[parameterName]=value ....
     * - POST parameters from a form
     * The qualifier is also known as prefixId (and designator in tx_lib).
     *
     * --------------------------------------------------------------------
     * Finding the action function
     * --------------------------------------------------------------------
     *
     *  Ultimative fallback:                   '$this->unknownAction()'
     *  Typical action:                        $parameters->action
     *  Fixed action (related context boxes):  $configurations->action
     *
     * All "default-actions" are removed, since this is confusing and useless.
     *
     * The action parameter "xy" matches the function "xyAction()".
     * By appending the postfix "Action" to the function name we assure,
     * that no other functions than actions can be addressed be sending an action parameter.
     * For security reasons, please don't append the Postfix "Action"for non action functions
     * in controller classes.
     *
     * If no action is provided the fallback action is the defaultAction.
     * If an invalid action is provided the function unknownAction() is called.
     *
     * --------------------------------------------------------------------
     * Finding the ultimative action controller
     * --------------------------------------------------------------------
     *
     * Additional controller classes can be registered that contain new actions or overwrite
     * existing actions. This way you can develop extensions for extensions, without need of XCLASS.
     * Registerd controller classes have precedence.
     *
     * Register a controller class B for this controller class A:
     * $TYPO3_CONF_VARS['CONTROLLERS']['A']['B'] = 1;
     *
     * New register a controller class C for the controller class B:
     * $TYPO3_CONF_VARS['CONTROLLERS']['B']['C'] = 1;
     *
     * Registration has to be done in lowercase typically in ext_tables.php.
     *
     * TODO: Write a registration function, so that the global variable is not accessed directly.
     *
     * @param  string   incomming content, not used by plugins
     * @param  array    TS configuration subtree down from the treenode of the plugin
     * @return string   the complete result of the plugin, typically it's (x)html
     */

    public function main($out, $configurationArray)
    {
        tx_rnbase_util_Misc::pushTT('tx_rnbase_controller', 'start');

        // Making the configurations object
        tx_rnbase_util_Misc::pushTT('init configuration', '');
        $configurations = $this->_makeConfigurationsObject($configurationArray);
        tx_rnbase_util_Misc::pullTT();

        try {
            // check for doConvertToUserIntObject
            $configurations->getBool('toUserInt')
            // convert the USER to USER_INT
            && $configurations->convertToUserInt();
        } catch (SkipActionException $e) {
            // dont do anything! the controller will be called twice,
            // if we convert the USER to USER_INTERNAL
            return '';
        }

        tx_rnbase_util_Misc::enableTimeTrack($configurations->get('_enableTT') ? true : false);
        // Making the parameters object
        tx_rnbase_util_Misc::pushTT('init parameters', '');
        $parameters = $this->_makeParameterObject($configurations);
        // Make sure to keep all parameters
        $configurations->setParameters($parameters);
        tx_rnbase_util_Misc::pullTT();

        // Finding the action:
        $actions = $this->_findAction($parameters, $configurations);
        if (!isset($actions)) {
            return $this->getUnknownAction();
        }
        $out = '';
        if (!is_array($actions)) {
            $actions = [$actions];
        }

        try {
            foreach ($actions as $actionName) {
                tx_rnbase_util_Misc::pushTT('call action', $actionName);
                $out .= $this->doAction($actionName, $parameters, $configurations);
                tx_rnbase_util_Misc::pullTT();
            }
        } catch (SkipActionException $e) {
            // Bei USER_INT im ersten Aufruf die Ausgabe unterdrücken
            $out = '';
        }
        tx_rnbase_util_Misc::pullTT();

        return $out;
    }

    /**
     * Call a single action.
     *
     * @param string                                     $actionName     class name
     * @param tx_rnbase_IParams                          $parameters
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     *
     * @return string
     */
    public function doAction($actionName, &$parameters, &$configurations)
    {
        $ret = '';

        try {
            // Creating the responsible Action
            $action = tx_rnbase::makeInstance($actionName);
            if (is_object($action)) {
                $ret = $action->execute($parameters, $configurations);
            }
        } catch (SkipActionException $e) {
            // Wenn ein View USER_INT verlangt, dann muss das für alle gelten
            // Darum die Exception weiter oben behandeln
            throw $e;
        } catch (PageNotFound404 $e) {
            $message = Strings::trimExplode("\n", $e->getMessage(), true, 2);
            if (count($message) > 1) {
                // Default 404 anhängen
                $message[1] .= "\n".$GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
            }
            $this->handlePageNotFound(
                count($message) > 1 ? $message[0] : $e->getMessage(),
                count($message) > 1 ? $message[1] : ''
            );
        } // Nice to have, aber weder aufwärts noch abwärtskompatibel...
        catch (TYPO3\CMS\Core\Error\Http\PageNotFoundException $e) {
            $this->handlePageNotFound('TYPO3\\CMS\\Core\\Error\\Http\\PageNotFoundException was thrown');
        } catch (Exception $e) {
            $ret = $this->handleException($actionName, $e, $configurations);
            $this->errors[] = $e;
        }

        return $ret;
    }

    protected function handlePageNotFound(string $reason, string $header = ''): void
    {
        if (!\Sys25\RnBase\Utility\TYPO3::isTYPO104OrHigher()) {
            \Sys25\RnBase\Utility\TYPO3::getTSFE()->pageNotFoundAndExit($reason, $header);
        }

        $response = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Controller\ErrorController::class)
            ->pageNotFoundAction(
                $GLOBALS['TYPO3_REQUEST'],
                $reason
            );
        throw new \TYPO3\CMS\Core\Http\ImmediateResponseException($response);
    }

    /**
     * Returns all unhandeled exceptions.
     *
     * @return array[Exception] or empty array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Interne Verarbeitung der Exception.
     *
     * @param Exception                                  $e
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     */
    private function handleException($actionName, Exception $e, $configurations)
    {
        $exceptionHandlerClass = \Sys25\RnBase\Configuration\Processor::getExtensionCfgValue(
            'rn_base',
            'exceptionHandler'
        );

        $defaultExceptionHandlerClass = ExceptionHandler::class;
        if (!$exceptionHandlerClass) {
            $exceptionHandlerClass = $defaultExceptionHandlerClass;
        }

        $exceptionHandler = tx_rnbase::makeInstance($exceptionHandlerClass);

        if (!$exceptionHandler instanceof ExceptionHandlerInterface) {
            $exceptionHandler = tx_rnbase::makeInstance($defaultExceptionHandlerClass);
            Logger::fatal(
                "the configured error handler ($exceptionHandlerClass) does not implement the ExceptionHandlerInterface",
                'rn_base'
            );
        }

        return $exceptionHandler->handleException($actionName, $e, $configurations);
    }

    /**
     * This is returned, if an invalid action has been send.
     *
     * @return string error text
     */
    public function getUnknownAction()
    {
        return '<p id="unknown_action">Unknown action.</p>';
    }

    //------------------------------------------------------------------------------------
    // Private functions
    //------------------------------------------------------------------------------------

    /**
     * Find the actions to handle the request
     * You can define more than one actions per request. So think of an action as a content element
     * to render.
     * So if your plugin supports a list and a detail view, you can render both of them
     * on the same page, including only one plugin. Make a view selection and add both views.
     * The controller will serve the request to both actions.
     *
     * Order: defaultAction < configurationDefaultAction < parametersAction < configurationsAction
     *
     * 1.) The defaultAction is the ultimative Fallback if nothing else is given.
     * 2.) The configurationDefaultAction can be set in TS and/or flexform to customize the initial view.
     * 3.) The parametersAction is given by form or link to controll the behaviour.
     * 4.) The configurationAction can force a fixed view of a context element.
     *
     * @param     object     the parameters object
     * @param     object     the configurations objet
     *
     * @return array an array with the actions or NULL
     */
    protected function _findAction($parameters, $configurations)
    {
        // What should be preferred? Config or Request?
        // An action from parameter is preferred
        $action = !intval($configurations->get('ignoreActionParam')) ? $this->_getParameterAction($parameters) : false;
        if (!$action) {
            $action = $configurations->get('action');
        } else {
            // Bei Actions aus dem Request kodierte Klassen korrigieren
            $action = str_replace('\\\\', '\\', $action);
        }

        // Falls es mehrere Actions sind den String splitten
        if ($action) {
            $action = tx_rnbase_util_Strings::trimExplode(',', $action);
        }
        if (is_array($action) && 1 == count($action)) {
            $action = tx_rnbase_util_Strings::trimExplode('|', $action[0]); // Nochmal mit Pipe versuchen
        }
        // If there is still no action we use defined defaultAction
        $action = !$action ? $configurations->get('defaultAction') : $action;

        return $action;
    }

    /**
     * Find the action from parameter string or array.
     *
     * The action value can be sent in two forms:
     * a) designator[action] = actionValue
     * b) designator[action][actionValue] = something
     *
     * Form b) is usfull Form HTML forms with multiple submit buttons.
     * You shouldn't use the button label as action value,
     * because it is language dependant.
     *
     * @param   object   the parameter object
     *
     * @return string the action value
     */
    public function _getParameterAction($parameters)
    {
        $action = $parameters->offsetGet('action');
        if (!is_array($action)) {
            return $action;
        } else {
            return key($action);
        }
    }

    /**
     * Make the configurations object.
     *
     * Used by main()
     *
     * @param array $configurationArray the local configuration array
     *
     * @return Tx_Rnbase_Configuration_ProcessorInterface the configurations
     */
    public function _makeConfigurationsObject($configurationArray)
    {
        // TODO, die Configklasse sollte über TS variabel gehalten werden
        // Make configurations object
        /* @var $configurations Tx_Rnbase_Configuration_Processor */
        $configurations = tx_rnbase::makeInstance($this->configurationsClassName);

        // Dieses cObj wird dem Controller von T3 übergeben
        $configurations->init($configurationArray, $this->cObj, $this->extensionKey, $this->qualifier);

        return $configurations;
    }

    /**
     * Returns an ArrayObject containing all parameters.
     *
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     */
    protected function _makeParameterObject($configurations)
    {
        $parameters = tx_rnbase::makeInstance(Parameters::class);
        $parameters->setQualifier($configurations->getQualifier());

        // get parametersArray for defined qualifier
        $parametersArray = Parameters::getPostAndGetParametersMerged($configurations->getQualifier());
        if ($configurations->isUniqueParameters() && array_key_exists($configurations->getPluginId(), $parametersArray)) {
            $parametersArray = $parametersArray[$configurations->getPluginId()];
        }
        Arrays::overwriteArray($parameters, $parametersArray);

        return $parameters;
    }
}
