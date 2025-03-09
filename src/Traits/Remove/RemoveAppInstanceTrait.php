<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Remove;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait RemoveAppInstanceTrait {

 /**
     * Handles removal tasks for an app instance.
     * 
     * This method is to remove the app instance. It validates the instance
     * is in the correct status, sets it to running, executes removal logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_REMOVE status
     * @throws PolydockEngineProcessPolydockAppInstanceException If the process fails
     */
    public function removeAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
    {
        $functionName = __FUNCTION__;
        $logContext = $this->getLogContext($functionName);
        $validateLagoonValues = true;
        $validateLagoonProjectName = true;
        $validateLagoonProjectId = true;

        $this->info($functionName . ': starting', $logContext);
    
        // Throws PolydockAppInstanceStatusFlowException
        $this->validateAppInstanceStatusIsExpectedAndConfigureLagoonClientAndVerifyLagoonValues(
            $appInstance,
            PolydockAppInstanceStatus::PENDING_REMOVE, 
            true,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );

        $projectName = $appInstance->getKeyValue("lagoon-project-name");

        $this->info($functionName . ': starting for project: ' . $projectName, $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::REMOVE_RUNNING, 
            PolydockAppInstanceStatus::REMOVE_RUNNING->getStatusMessage()
        );

        $this->Log::warning("TODO: Implement remove logic", $logContext);

        $this->info($functionName . ': completed', $logContext);
        $appInstance->setStatus(PolydockAppInstanceStatus::REMOVE_COMPLETED, "Remove completed");
        return $appInstance;
    }
}
