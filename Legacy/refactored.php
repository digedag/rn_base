<?php


namespace Sys25\RnBase\Controller {
    /**
     * @deprecated
     */
    abstract class AbstractController extends \tx_rnbase_action_BaseIOC
    {
        // @codingStandardsIgnoreStart (interface/abstract mistake)
        protected function handleRequest(&$parameters, &$configurations, &$viewdata)
        {
            // @codingStandardsIgnoreEnd
            return $this->doRequest();
        }
        abstract protected function doRequest();

        protected function getViewClassName()
        {
            return \Sys25\RnBase\Fluid\View\Action::class;
        }

        protected function assignToView($name, $data)
        {
            $this->getViewData()->offsetSet($name, $data);

            return $this;
        }

        protected function getConfigurationValue($confId)
        {
            return $this->getConfigurations()->get($this->getConfId().$confId);
        }
    }
}
