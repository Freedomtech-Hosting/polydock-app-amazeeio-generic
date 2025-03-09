<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Create;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait CreateAppInstanceTrait {

 /**
     * Handles creation tasks for an app instance.
     * 
     * This method is to create the Lagoon project for the app instance. It validates the instance
     * is in the correct status, sets it to running, executes creation logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_CREATE status
     * @throws PolydockEngineProcessPolydockAppInstanceException If the process fails
     */
    public function createAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
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
            PolydockAppInstanceStatus::PENDING_CREATE, 
            $logContext,
            $testLagoonPing,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );

        $projectName = $appInstance->getKeyValue("lagoon-project-name");

        $this->info($functionName . ': starting for project: ' . $projectName, $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::CREATE_RUNNING, 
            PolydockAppInstanceStatus::CREATE_RUNNING->getStatusMessage()
        );

        $createdProjectData = $this->lagoonClient->createLagoonProjectInOrganization(
            $projectName, 
            $appInstance->getKeyValue("lagoon-deploy-git"),
            $appInstance->getKeyValue("lagoon-deploy-branch"),
            $appInstance->getKeyValue("lagoon-deploy-branch"),
            $appInstance->getKeyValue("lagoon-deploy-region-id"),
            null,
            $appInstance->getKeyValue("lagoon-deploy-organization-id"),
            true
        );

        if (isset($createdProjectData['error'])) {
            $this->error($createdProjectData['error'][0]['message'], $logContext);
            $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_FAILED, "Failed to create Lagoon project", $logContext + ['error' => $createdProjectData['error']]);
            return $appInstance;
        }

        if(!isset($createdProjectData['addProject']['id'])) {
            $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_FAILED, "Failed to create Lagoon project", $logContext + ['error' => "Missing project id"]);
            return $appInstance;
        }

        $appInstance->storeKeyValue("lagoon-project-id", $createdProjectData['addProject']['id']);

        $this->info($functionName . ': completed', $logContext + ['projectId' => $createdProjectData['addProject']['id']]);
        $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_COMPLETED, "Create completed");
        return $appInstance;
    }
}
