<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Upgrade;

use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
trait PollUpgradeProgressAppInstanceTrait {

    public function pollAppInstanceUpgradeProgress(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceStatus
    {
        $logContext = $this->getLogContext(__FUNCTION__);
        $appInstance->warning("TODO: Implement upgrade progress logic", $logContext);
        return $appInstance->getStatus();
    }
}