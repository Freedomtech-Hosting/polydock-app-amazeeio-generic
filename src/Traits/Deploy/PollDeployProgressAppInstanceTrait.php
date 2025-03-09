<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Deploy;

use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait PollDeployProgressAppInstanceTrait {

    public function pollAppInstanceDeploymentProgress(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {
        $logContext = $this->getLogContext(__FUNCTION__);
        $appInstance->warning("TODO: Implement deploy progress logic", $logContext);
        return $appInstance;
    }
}