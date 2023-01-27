<?php

namespace Sys25\RnBase\Controller;

use Sys25\RnBase\ExtBaseFluid\View\Action;

/**
 * @deprecated
 */
abstract class AbstractController extends \tx_rnbase_action_BaseIOC
{
    protected function handleRequest(&$parameters, &$configurations, &$viewdata)
    {
        return $this->doRequest();
    }

    abstract protected function doRequest();

    protected function getViewClassName()
    {
        return Action::class;
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
