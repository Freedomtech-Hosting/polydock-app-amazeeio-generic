<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Deploy;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait DeployAppInstanceTrait {

 /**
     * Handles deployment tasks for an app instance.
     * 
     * This method is to deploy the app instance. It validates the instance
     * is in the correct status, sets it to running, executes deployment logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_DEPLOY status
     * @throws PolydockEngineProcessPolydockAppInstanceException If the process fails
     */
    public function deployAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
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
            PolydockAppInstanceStatus::PENDING_DEPLOY, 
            $logContext,
            $testLagoonPing,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );

        $projectName = $appInstance->getKeyValue("lagoon-project-name");

        $this->info($functionName . ': starting for project: ' . $projectName, $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::DEPLOY_RUNNING, 
            PolydockAppInstanceStatus::DEPLOY_RUNNING->getStatusMessage()
        );

        $this->warning("TODO: Implement deploy logic", $logContext);

        $this->info($functionName . ': completed', $logContext);
        $appInstance->setStatus(PolydockAppInstanceStatus::DEPLOY_COMPLETED, "Deploy completed");
        return $appInstance;
    }
}
