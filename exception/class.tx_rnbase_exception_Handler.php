<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013  <hannes.bochmann@das-medienkombinat.de>
 *  Contact: rene@system25.de
 *  All rights reserved
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

/**
 * @author Hannes Bochmann
 * @author Michael Wagner
 */
class tx_rnbase_exception_Handler implements tx_rnbase_exception_IHandler
{
    /**
     * Interne Verarbeitung der Exception.
     *
     * @param string                                     $actionName
     * @param Exception                                  $e
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     *
     * @return string error message
     */
    public function handleException($actionName, Exception $e, Tx_Rnbase_Configuration_ProcessorInterface $configurations)
    {
        // wir prüfen erst mal, ob die exception gefangen werden soll
        $catch = $this->catchException($actionName, $e, $configurations);
        if (null !== $catch) {
            return $catch;
        }

        // wenn nicht senden wir ggf den header
        if ($this->send503HeaderOnException($configurations)) {
            header('HTTP/1.1 503 Service Unavailable');
        }
        // wir loggen nun den fehler
        if (tx_rnbase_util_Logger::isFatalEnabled()) {
            $extKey = $configurations->getExtensionKey();
            $extKey = $extKey ? $extKey : 'rn_base';
            tx_rnbase_util_Logger::fatal(
                'Fatal error for action '.$actionName,
                $extKey,
                [
                    'Exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ],
                    '_GET' => $_GET,
                    '_POST' => $_POST,
                ]
            );
        }
        // wir senden eine fehlermail
        $addr = Tx_Rnbase_Configuration_Processor::getExtensionCfgValue('rn_base', 'sendEmailOnException');
        if ($addr) {
            tx_rnbase_util_Misc::sendErrorMail($addr, $actionName, $e);
        }

        // Now message for FE
        $ret = $this->getErrorMessage($actionName, $e, $configurations);

        return $ret;
    }

    /**
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     *
     * @return bool
     */
    protected function send503HeaderOnException(Tx_Rnbase_Configuration_ProcessorInterface $configurations)
    {
        //sending a 503 header?
        return
            (
                //shall we basically send a 503 header?
                intval(Tx_Rnbase_Configuration_Processor::getExtensionCfgValue('rn_base', 'send503HeaderOnException')) && (
                    //the plugin has the oppurtunity to prevent sending a 503 header
                    //by setting plugin.plugin_name.send503HeaderOnException = 0 in the TS config.
                    //if this option is not set we use the ext config
                    !array_key_exists('send503HeaderOnException', $configurations->getConfigArray()) ||
                    0 != $configurations->get('send503HeaderOnException')
                )
            ) ||
            (
                //did the plugin define to send the 503 header
                1 == $configurations->get('send503HeaderOnException')
            )
        ;
    }

    /**
     * Build an error message string for frontend.
     *
     * @param string                                     $actionName
     * @param Exception                                  $e
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     */
    protected function getErrorMessage($actionName, Exception $e, Tx_Rnbase_Configuration_ProcessorInterface $configurations)
    {
        if (Tx_Rnbase_Configuration_Processor::getExtensionCfgValue('rn_base', 'verboseMayday')) {
            return '<div>'
                    .'<strong>UNCAUGHT EXCEPTION FOR VIEW: '.$actionName.'</strong>'
                    .'<br />CODE: '.$e->getCode()
                    .'<br />MESSAGE: '.$e->getMessage()
                    .'<br />STACK: <pre>'.$e->__toString().'</pre>'
                .'</div>';
        }

        // typoscript nach fehlermeldungen prüfen
        // das machen wir nur, wenn sich diese exception nicht
        // dies kann passieren, wenn über typoscript wieder plugin angesteuert wird,
        // welches die selbe fehlermeldung produziert
        if (!$this->checkExceptionRecursion($actionName, $e, $configurations)) {
            // aktuelle meldung setzen, damit diese ggf. über ts ausgelesen werden kann (.current = 1)
            $configurations->getCObj()->setCurrentVal('ERROR '.$e->getCode().': '.$e->getMessage());
            // meldung auslesen
            $ret = $configurations->get('error.'.$e->getCode(), true);
            $ret = $ret ? $ret : $configurations->get('error.default', true);
        }
        // nun die sprachlabels nach einem fehler überprüfen.
        $ret = isset($ret) && $ret ? $ret : $configurations->getLL('ERROR_'.$e->getCode(), '');
        $ret = $ret ? $ret : $configurations->getLL('ERROR_default', '');
        // fallback message
        $ret = $ret ? $ret : '<div><strong>Leider ist ein unerwarteter Fehler aufgetreten.</strong></div>';

        return $ret;
    }

    /**
     * prüft ob die exception gefangen werden kann,
     * und erzeugt ggf eine alternative ausgabe.
     *
     * liefert entweder eine ausgabe (string) oder NULL,
     * um die fehlerbehandlung weiter leufen zu lassen.
     *
     * @param string                                     $actionName
     * @param Exception                                  $e
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     *
     * @return string|null
     */
    protected function catchException(
        $actionName,
        Exception $e,
        Tx_Rnbase_Configuration_ProcessorInterface $configurations
    ) {
        // typoscript nach catchanweisungen prüfen
        // das machen wir nur, wenn sich diese exception nicht
        // dies kann passieren, wenn über typoscript wieder plugin angesteuert wird,
        // welches die selbe fehlermeldung produziert
        if (!$this->checkExceptionRecursion($actionName, $e, $configurations, 'catch')) {
            // aktuelle meldung setzen, damit diese ggf. über ts ausgelesen werden kann (.current = 1)
            $configurations->getCObj()->setCurrentVal('ERROR '.$e->getCode().': '.$e->getMessage());
            // meldung auslesen
            $ret = $configurations->get('catchException.'.$e->getCode(), true);
            if (!empty($ret)) {
                return $ret;
            }
        }

        return null;
    }

    /**
     * chechs, if the exception was allready thrown in the stack.
     * returns TRUE, if the exception was allready thrown.
     *
     * die recursion können wir an dieser stelle nicht über den backtrace prüfen.
     * bei ungecachten ausgaben wird bei typo3 mit int_script gearbeidet,
     * wodurch der stack auch bei mehrfacher rekursion immer gleich ist,
     * also die methode nur ein mal auftaucht.
     *
     * @param string                                     $actionName
     * @param Exception                                  $e
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     *
     * @return bool
     */
    private function checkExceptionRecursion(
        $action,
        Exception $e,
        Tx_Rnbase_Configuration_ProcessorInterface $configurations,
        $type = 'error'
    ) {
        static $calls = 0, $trace = [];

        // konfiguration für die maximale anzahl an durchläufen holen.
        $maxCalls = $configurations->getInt('recursionCheck.maxCalls');
        $maxCalls = $maxCalls ? $maxCalls : 50;
        $maxThrows = $configurations->getInt('recursionCheck.maxThrows');
        $maxThrows = $maxThrows ? $maxThrows : 1;

        // bei mehr als 50 exception calls, müssen wir davon ausgehen,
        // das ein kritischer fehler vorliegt
        if (++$calls > $maxCalls) {
            tx_rnbase_util_Logger::fatal(
                'Too much recursion in "'.get_class($this).'"'
                    .' That should not have happened.'
                    .' It looks as if there is a problem with a faulty configuration.',
                'rn_base'
            );

            return true;
        }
        // else
        // ansonsten setzen wir eine art stack aus action, errorcode und config zusammen.

        $code = $e->getCode();
        // das typoscript wir bei jedem plugin aufruf neu generiert
        // und unterscheidet sich demnach.
        // wenn es zu einer rekursion kommt, ist das ts allerdings immer gleich!
        // (abgesehen von unaufgelösten referenzen)
        $configKey = md5(serialize($configurations->getConfigArray()));

        if (empty($trace[$type])) {
            $trace[$type] = [];
        }
        if (empty($trace[$type][$action])) {
            $trace[$type][$action] = [];
        }
        if (empty($trace[$type][$action][$code])) {
            $trace[$type][$action][$code] = [];
        }
        if (empty($trace[$type][$action][$code][$configKey])) {
            $trace[$type][$action][$code][$configKey] = 0;
        }
        ++$trace[$type][$action][$code][$configKey];

        if (isset($trace[$type][$action][$code][$configKey])
            && $trace[$type][$action][$code][$configKey] > $maxThrows
        ) {
            return true;
        }

        return false;
    }
}
