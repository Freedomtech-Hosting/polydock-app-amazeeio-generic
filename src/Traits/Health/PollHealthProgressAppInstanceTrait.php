<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Health;

use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
trait PollHealthProgressAppInstanceTrait {

    public function pollAppInstanceHealthStatus(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceStatus
    {
        $logContext = $this->getLogContext(__FUNCTION__);
        $appInstance->warning("TODO: Implement health check logic", $logContext);
        return PolydockAppInstanceStatus::RUNNING_HEALTHY;
    }
}