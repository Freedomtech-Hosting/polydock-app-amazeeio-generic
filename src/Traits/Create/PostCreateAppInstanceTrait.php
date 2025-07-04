<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Create;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

trait PostCreateAppInstanceTrait {

 /**
     * Handles post-creation tasks for an app instance.
     * 
     * This method is called after creating the app instance. It validates the instance
     * is in the correct status, sets it to running, executes post-creation logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_POST_CREATE status
     * @throws PolydockEngineProcessPolydockAppInstanceException If the process fails
     */
    public function postCreateAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
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
            PolydockAppInstanceStatus::PENDING_POST_CREATE, 
            $logContext,
            $testLagoonPing,
            $validateLagoonValues,
            $validateLagoonProjectName,
            $validateLagoonProjectId
        );

        if($this->getRequiresAiInfrastructure()) {
            // Throws PolydockAppInstanceStatusFlowException
            $this->setAmazeeAiBackendClientFromAppInstance($appInstance);
        }

        $projectName = $appInstance->getKeyValue("lagoon-project-name");

        $this->info($functionName . ': starting for project: ' . $projectName, $logContext);
        $appInstance->setStatus(
            PolydockAppInstanceStatus::POST_CREATE_RUNNING, 
            PolydockAppInstanceStatus::POST_CREATE_RUNNING->getStatusMessage()
        )->save();

        try {

            $addGroupToProjectResult = $this->lagoonClient->addGroupToProject(
                $appInstance->getKeyValue("lagoon-deploy-group-name"),
                $projectName
            );

            if (isset($addGroupToProjectResult['error'])) {
                $this->error($addGroupToProjectResult['error'][0]['message']);
                throw new \Exception($addGroupToProjectResult['error'][0]['message']);
            }
    
            if(!isset($addGroupToProjectResult['addGroupsToProject']) || !isset($addGroupToProjectResult['addGroupsToProject']['id'])) {
                $this->error("addGroupsToProject ID not found in data");
                throw new \Exception("addGroupsToProject ID not found in data");
            }
    
#            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_CREATED_DATE", date('Y-m-d'), "GLOBAL");
#            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_CREATED_TIME", date('H:i:s'), "GLOBAL");
#            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_TYPE", $appInstance->getAppType(), "GLOBAL");
#            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_VERSION", self::getAppVersion(), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_NAME", $appInstance->getApp()->getAppName(), "GLOBAL");
#            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_DESCRIPTION", $appInstance->getApp()->getAppDescription(), "GLOBAL");
#            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_AUTHOR", $appInstance->getApp()->getAppAuthor(), "GLOBAL");
#            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_WEBSITE", $appInstance->getApp()->getAppWebsite(), "GLOBAL");
#            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_SUPPORT_EMAIL", $appInstance->getApp()->getAppSupportEmail(), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_GENERATED_APP_ADMIN_USERNAME", $appInstance->getKeyValue("lagoon-generate-app-admin-username"), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_GENERATED_APP_ADMIN_PASSWORD", $appInstance->getKeyValue("lagoon-generate-app-admin-password"), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_USER_FIRST_NAME", $appInstance->getKeyValue("user-first-name"), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_USER_LAST_NAME", $appInstance->getKeyValue("user-last-name"), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_USER_EMAIL", $appInstance->getKeyValue("user-email"), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "POLYDOCK_APP_INSTANCE_HEALTH_WEBHOOK_URL", $appInstance->getKeyValue("polydock-app-instance-health-webhook-url"), "GLOBAL");
            $this->addOrUpdateLagoonProjectVariable($appInstance, "LAGOON_FEATURE_FLAG_INSIGHTS", "false", "GLOBAL");
            
            if($this->getRequiresAiInfrastructure()) {
		sleep(2);
                $privateAiCredentials = $this->getPrivateAICredentialsFromBackend($appInstance);
                $llmApiUrl = $privateAiCredentials['litellm_api_url'];
                $llmApiHostname = preg_replace('#^https?://|/.*$#', '', $llmApiUrl);

		$this->info($functionName . ': app requires AI infrastructure', $logContext);
                $this->addOrUpdateLagoonProjectVariable($appInstance, "AI_REGION", $privateAiCredentials['region'], "GLOBAL");
		sleep(4);

		$this->info($functionName . ': Injecting AI DB Credentials', $logContext);
                $this->addOrUpdateLagoonProjectVariable($appInstance, "AI_DB_HOST_NAME", $privateAiCredentials['database_host'], "GLOBAL");
                $this->addOrUpdateLagoonProjectVariable($appInstance, "AI_DB_NAME", $privateAiCredentials['database_name'], "GLOBAL");
                $this->addOrUpdateLagoonProjectVariable($appInstance, "AI_DB_USERNAME", $privateAiCredentials['database_username'], "GLOBAL");
                $this->addOrUpdateLagoonProjectVariable($appInstance, "AI_DB_PASSWORD", $privateAiCredentials['database_password'], "GLOBAL");
		sleep(4);

		$this->info($functionName . ': Injecting AI LLM Credentials', $logContext);
                $this->addOrUpdateLagoonProjectVariable($appInstance, "AI_LLM_API_URL", $privateAiCredentials['litellm_api_url'], "GLOBAL");
                $this->addOrUpdateLagoonProjectVariable($appInstance, "AI_LLM_API_HOSTNAME", $llmApiHostname, "GLOBAL");
                $this->addOrUpdateLagoonProjectVariable($appInstance, "AI_LLM_API_HOST_NAME", $privateAiCredentials['litellm_api_url'], "GLOBAL");
                $this->addOrUpdateLagoonProjectVariable($appInstance, "AI_LLM_API_TOKEN", $privateAiCredentials['litellm_token'], "GLOBAL");
		$this->info($functionName . ': Done injecting AI infrastructure', $logContext);
            }

	} catch (\Exception $e) {
	    $this->info("Post Create Failed");
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_FAILED, "An exception occured")->save();
            return $appInstance;
        }

        $this->info($functionName . ': completed', $logContext);
        $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_COMPLETED, "Post-create completed")->save();
        return $appInstance;
    }
}
