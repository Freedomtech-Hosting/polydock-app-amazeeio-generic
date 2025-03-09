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
        $deployEnvironment = $appInstance->getKeyValue("lagoon-deploy-branch");
        $logContext['projectName'] = $projectName;
        $logContext['deployEnvironment'] = $deployEnvironment;
        
        $this->info($functionName . ': starting for project: ' . $projectName . ' and environment: ' . $deployEnvironment, $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::DEPLOY_RUNNING, 
            PolydockAppInstanceStatus::DEPLOY_RUNNING->getStatusMessage()
        );

        $createdDeployment = $this->lagoonClient->deployProjectEnvironmentByName(
            $projectName, 
            $deployEnvironment
        );

        if (isset($createdDeployment['error'])) {
            $this->error($createdDeployment['error'][0]['message'], $logContext);
            $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_FAILED, "Failed to create Lagoon project", $logContext + ['error' => $createdDeployment['error']]);
            return $appInstance;
        }

        $latestDeploymentName = $createdDeployment['deployEnvironmentBranch'] ?? null;

        if(empty($latestDeploymentName)) {
            $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_FAILED, "Failed to create Lagoon project", $logContext + ['error' => "Missing deployment name"]);
            return $appInstance;
        }

        $appInstance->storeKeyValue("lagoon-latest-deployment-name", $latestDeploymentName);

        $this->info($functionName . ': completed', $logContext);
        $appInstance->setStatus(PolydockAppInstanceStatus::DEPLOY_RUNNING, "Deploy running");
        return $appInstance;
    }
}
