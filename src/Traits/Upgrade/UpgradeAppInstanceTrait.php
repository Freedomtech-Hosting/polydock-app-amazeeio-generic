<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Upgrade;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait UpgradeAppInstanceTrait {

 /**
     * Handles upgrade tasks for an app instance.
     * 
     * This method is to upgrade the app instance. It validates the instance
     * is in the correct status, sets it to running, executes upgrade logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_UPGRADE status
     * @throws PolydockEngineProcessPolydockAppInstanceException If the process fails
     */
    public function upgradeAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
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
            PolydockAppInstanceStatus::PENDING_UPGRADE, 
            $logContext,
            $testLagoonPing,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );

        $projectName = $appInstance->getKeyValue("lagoon-project-name");

        $this->info($functionName . ': starting for project: ' . $projectName, $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::UPGRADE_RUNNING, 
            PolydockAppInstanceStatus::UPGRADE_RUNNING->getStatusMessage()
        );

        $appInstance->warning("TODO: Implement upgrade logic", $logContext);
        try {
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_TYPE", $appInstance->getAppType(), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_VERSION", self::getAppVersion(), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_NAME", $appInstance->getApp()->getAppName(), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_DESCRIPTION", $appInstance->getApp()->getAppDescription(), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_AUTHOR", $appInstance->getApp()->getAppAuthor(), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_WEBSITE", $appInstance->getApp()->getAppWebsite(), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_SUPPORT_EMAIL", $appInstance->getApp()->getAppSupportEmail(), "GLOBAL");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $appInstance->setStatus(PolydockAppInstanceStatus::UPGRADE_FAILED, $e->getMessage() );
            return $appInstance;
        }

        $this->info($functionName . ': completed', $logContext);
        $appInstance->setStatus(PolydockAppInstanceStatus::UPGRADE_COMPLETED, "Upgrade completed");
        return $appInstance;
    }
}
