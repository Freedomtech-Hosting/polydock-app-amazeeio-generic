<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Claim;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait ClaimAppInstanceTrait {

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
    public function claimAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
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
            PolydockAppInstanceStatus::PENDING_POLYDOCK_CLAIM,
            $logContext,
            $testLagoonPing,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );

        $projectName = $appInstance->getKeyValue("lagoon-project-name");

        $this->info($functionName . ': starting claim of project: ' . $projectName, $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::POLYDOCK_CLAIM_RUNNING, 
            PolydockAppInstanceStatus::POLYDOCK_CLAIM_RUNNING->getStatusMessage()
        )->save();

        $appInstance->warning("TODO: Implement claim logic", $logContext);

        try {
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_CLAIMED_AT", date('Y-m-d H:i:s'), "GLOBAL");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $appInstance->setStatus(PolydockAppInstanceStatus::POLYDOCK_CLAIM_FAILED, $e->getMessage() )->save();
            return $appInstance;
        }

        $this->info($functionName . ': completed', $logContext);
        $appInstance->setStatus(PolydockAppInstanceStatus::POLYDOCK_CLAIM_COMPLETED, "Claim completed")->save();
        return $appInstance;
    }
}
