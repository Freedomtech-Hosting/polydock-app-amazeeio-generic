<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Deploy;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait PostDeployAppInstanceTrait {

 /**
     * Handles post-deployment tasks for an app instance.
     * 
     * This method is called after deploying the app instance. It validates the instance
     * is in the correct status, sets it to running, executes post-deployment logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_POST_DEPLOY status
     * @throws PolydockEngineProcessPolydockAppInstanceException If the process fails
     */
    public function postDeployAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
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
            PolydockAppInstanceStatus::PENDING_POST_DEPLOY, 
            $logContext,
            $testLagoonPing,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );

        $projectName = $appInstance->getKeyValue("lagoon-project-name");

        $this->info($functionName . ': starting for project: ' . $projectName, $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::POST_DEPLOY_RUNNING, 
            PolydockAppInstanceStatus::POST_DEPLOY_RUNNING->getStatusMessage()
        );

        $this->appInstance->warning("TODO: Implement post-deploy logic", $logContext);
        try {
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_LAST_DEPLOYED_DATE", date('Y-m-d'), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_LAST_DEPLOYED_TIME", date('H:i:s'), "GLOBAL");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_DEPLOY_FAILED, $e->getMessage() );
            return $appInstance;
        }

        $this->info($functionName . ': completed', $logContext);
        $appInstance->setStatus(PolydockAppInstanceStatus::POST_DEPLOY_COMPLETED, "Post-deploy completed");
        return $appInstance;
    }
}
