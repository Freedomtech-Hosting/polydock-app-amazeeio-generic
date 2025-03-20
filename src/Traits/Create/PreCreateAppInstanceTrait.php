<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Create;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait PreCreateAppInstanceTrait {

 /**
     * Handles pre-creation tasks for an app instance.
     * 
     * This method is called before creating the app instance. It validates the instance
     * is in the correct status, sets it to running, executes pre-creation logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_PRE_CREATE status
     * @throws PolydockEngineProcessPolydockAppInstanceException If the process fails
     */
    public function preCreateAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
    {
        $functionName = __FUNCTION__;
        $logContext = $this->getLogContext($functionName);
        $testLagoonPing = true;
        $validateLagoonValues = true;
        $validateLagoonProjectName = true;
        $validateLagoonProjectId = false;

        $this->info($functionName . ': starting', $logContext);
    
        // Throws PolydockAppInstanceStatusFlowException
        $this->validateAppInstanceStatusIsExpectedAndConfigureLagoonClientAndVerifyLagoonValues(
            $appInstance,
            PolydockAppInstanceStatus::PENDING_PRE_CREATE, 
            $logContext,
            $testLagoonPing,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );


        // While we don't use this here, lets make sure we have it available for later 
        if($this->getRequiresAiInfrastructure()) {
            // Throws PolydockAppInstanceStatusFlowException
            $this->setAmazeeAiBackendClientFromAppInstance($appInstance);
        }

        $projectName = $appInstance->getKeyValue("lagoon-project-name");

        $this->info($functionName . ': starting for project: ' . $projectName, $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::PRE_CREATE_RUNNING, 
            PolydockAppInstanceStatus::PRE_CREATE_RUNNING->getStatusMessage()
        );

        $this->info($functionName . ': completed', $logContext);
        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_CREATE_COMPLETED, "Pre-create completed");
        return $appInstance;
    }
}
