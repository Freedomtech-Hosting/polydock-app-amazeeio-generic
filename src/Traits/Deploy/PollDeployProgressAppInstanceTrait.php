<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Deploy;

use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
trait PollDeployProgressAppInstanceTrait {

    public function pollAppInstanceDeploymentProgress(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceStatus
    {
        $logContext = $this->getLogContext(__FUNCTION__);
        $appInstance->warning("TODO: Implement deploy progress logic", $logContext);
        return $appInstance->getStatus();
    }
}