<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Deploy;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait PollDeployProgressAppInstanceTrait {

    public function pollAppInstanceDeploymentProgress(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {
        $functionName = __FUNCTION__;
        $logContext = $this->getLogContext($functionName);
        $testLagoonPing = true;
        $validateLagoonValues = true;
        $validateLagoonProjectName = true;
        $validateLagoonProjectId = true;

        $possibleDeploymentStatusesToPolydockAppInstanceStatus = [
            'new' => PolydockAppInstanceStatus::DEPLOY_RUNNING, 
            'pending' => PolydockAppInstanceStatus::DEPLOY_RUNNING, 
            'running' => PolydockAppInstanceStatus::DEPLOY_RUNNING, 
            'cancelled' => PolydockAppInstanceStatus::DEPLOY_FAILED, 
            'error' => PolydockAppInstanceStatus::DEPLOY_FAILED, 
            'failed' => PolydockAppInstanceStatus::DEPLOY_FAILED, 
            'complete' => PolydockAppInstanceStatus::DEPLOY_COMPLETED,
        ];

        $this->info($functionName . ': starting', $logContext);
    
        // Throws PolydockAppInstanceStatusFlowException
        $this->validateAppInstanceStatusIsExpectedAndConfigureLagoonClientAndVerifyLagoonValues(
            $appInstance,
            PolydockAppInstanceStatus::DEPLOY_RUNNING, 
            $logContext,
            $testLagoonPing,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );

        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        $deployEnvironment = $appInstance->getKeyValue("lagoon-deploy-branch");
        $latestDeploymentName = $appInstance->getKeyValue("lagoon-latest-deployment-name");

        $logContext['projectName'] = $projectName;
        $logContext['projectId'] = $projectId;
        $logContext['deployEnvironment'] = $deployEnvironment;
        $logContext['latestDeploymentName'] = $latestDeploymentName;

        $this->info($functionName . ': polling for project: ' . $projectName . ' (' . $projectId . ')' 
            . ' and environment: ' . $deployEnvironment 
            . ' and deployment name: ' . $latestDeploymentName, $logContext);

        $deploymentStatus = $this->lagoonClient->getProjectDeploymentByProjectIdDeploymentName(
            $projectId, 
            $deployEnvironment,
            $latestDeploymentName
        );

        $this->debug($functionName . ': deployment status: ' . json_encode($deploymentStatus), $logContext);

        if (isset($deploymentStatus['error'])) {
            $this->error($deploymentStatus['error'][0]['message'], $logContext);
            return $appInstance;
        }

        $deploymentId = $deploymentStatus['id'] ?? null;
        $deploymentName = $deploymentStatus['name'] ?? null;
        $deploymentPriority = $deploymentStatus['priority'] ?? '';
        $deploymentBuildStep = $deploymentStatus['buildStep'] ?? '';
        $deploymentStatus = $deploymentStatus['status'] ?? '';
        $deploymentStarted = $deploymentStatus['started'] ?? '';
        $deploymentCompleted = $deploymentStatus['completed'] ?? '';

        $logContext['deploymentId'] = $deploymentId;
        $logContext['deploymentName'] = $deploymentName;
        $logContext['deploymentPriority'] = $deploymentPriority;
        $logContext['deploymentBuildStep'] = $deploymentBuildStep;
        $logContext['deploymentStatus'] = $deploymentStatus;
        $logContext['deploymentStarted'] = $deploymentStarted;
        $logContext['deploymentCompleted'] = $deploymentCompleted;

        $appInstance->info($functionName . ': deployment status: ' . $deploymentStatus, $logContext);

        $emptyFields = [];
        if(empty($deploymentId)) {
            $appInstance->warning("Deployment id is empty", $logContext);
            $emptyFields[] = "deploymentId";
        }

        if(empty($deploymentName)) {
            $appInstance->warning("Deployment name is empty", $logContext);
            $emptyFields[] = "deploymentName";
        }

        if(empty($deploymentStatus)) {
            $appInstance->warning("Deployment status is empty", $logContext);
            $emptyFields[] = "deploymentStatus";
        }

        if(count($emptyFields) > 0) {
            $appInstance->warning("Required deployment status fields are empty: " . implode(", ", $emptyFields), $logContext);
            return $appInstance;
        }

        $deploymentStatusKey = "lagoon-deployment-{$deploymentName}.status";
        $deploymentBuildStepKey = "lagoon-deployment-{$deploymentName}.buildStep";

        $appInstance->storeKeyValue($deploymentStatusKey, $deploymentStatus);
        $appInstance->storeKeyValue($deploymentBuildStepKey, $deploymentBuildStep);

        $currentPolydockAppInstanceStatus = $appInstance->getStatus();
        $fetchedDeploymentStatus = $possibleDeploymentStatusesToPolydockAppInstanceStatus[$deploymentStatus] ?? null;

        if(empty($fetchedDeploymentStatus)) {
            $appInstance->warning("Unknown deployment status: " . $deploymentStatus, $logContext);
            return $appInstance;
        }

        if($currentPolydockAppInstanceStatus !== $fetchedDeploymentStatus) {
            $appInstance->setStatus(
                $possibleDeploymentStatusesToPolydockAppInstanceStatus[$deploymentStatus], 
                    "Deploy is " . $deploymentStatus
            )->save();
        }

        $appInstance->info($functionName . " - deploy status successfully polled and updated", $logContext);
        
        return $appInstance;
    }
}