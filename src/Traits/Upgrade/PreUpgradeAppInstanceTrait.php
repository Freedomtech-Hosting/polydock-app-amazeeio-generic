<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Upgrade;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait PreUpgradeAppInstanceTrait {

 /**
     * Handles pre-upgrade tasks for an app instance.
     * 
     * This method is called before upgrading the app instance. It validates the instance
     * is in the correct status, sets it to running, executes pre-upgrade logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_PRE_UPGRADE status
     * @throws PolydockEngineProcessPolydockAppInstanceException If the process fails
     */
    public function preUpgradeAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
    {
        $functionName = __FUNCTION__;
        $logContext = $this->getLogContext($functionName);
        $testLagoonPing = true;
        $validateLagoonValues = true;
        $validateLagoonProjectName = true;
        $validateLagoonProjectId = true;

        $this->info($functionName . ': starting', $logContext);
    
        // Throws PolydockAppInstanceStatusFlowException
        $this->validateAppInstanceStatusIsExpectedAndConfigureLagoonClientAndVerifyLagoonValues(
            $appInstance,
            PolydockAppInstanceStatus::PENDING_PRE_UPGRADE, 
            $logContext,
            $testLagoonPing,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );

        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");

        $this->info($functionName . ': starting for project: ' . $projectName . ' (' . $projectId . ')', $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::PRE_UPGRADE_RUNNING, 
            PolydockAppInstanceStatus::PRE_UPGRADE_RUNNING->getStatusMessage()
        )->save();

        // There is nothing to do here beyond checking the name and ID above
        
        $this->info($functionName . ': completed', $logContext);
        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_UPGRADE_COMPLETED, "Pre-upgrade completed")->save();
        return $appInstance;
    }
}
