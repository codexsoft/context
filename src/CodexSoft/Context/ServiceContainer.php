<?php

namespace CodexSoft\Context;

class ServiceContainer extends ParameterBag
{

    /**
     * @var bool
     */
    private $isolated;

    /**
     * @return bool
     */
    public function isIsolated(): bool
    {
        return $this->isolated;
    }

    /**
     * @param bool $isolated
     *
     * @return ServiceContainer
     */
    public function setIsolated(bool $isolated): ServiceContainer
    {
        $this->isolated = $isolated;
        return $this;
    }

}