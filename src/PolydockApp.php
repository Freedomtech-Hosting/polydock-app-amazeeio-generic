<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric;

use FreedomtechHosting\PolydockApp\PolydockAppBase;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceStatusFlowException;
use FreedomtechHosting\FtLagoonPhp\Client as LagoonClient;
use FreedomtechHosting\PolydockApp\PolydockEngineInterface;
use FreedomtechHosting\PolydockApp\PolydockServiceProviderInterface;

class PolydockApp extends PolydockAppBase
{
    /**
     * @var string
     */
    public static string $version = '0.0.1';
    
    /**
     * @var LagoonClient
     */
    protected LagoonClient $lagoonClient;

    /**
     * @var PolydockEngineInterface
     */
    protected PolydockEngineInterface $engine;

    /**
     * @var PolydockServiceProviderInterface
     */
    protected PolydockServiceProviderInterface $lagoonClientProvider;

    /**
     * Get the version of the app
     * 
     * @return string
     */
    public static function getAppVersion(): string
    {
        return self::$version;
    }

    /**
     * Pings the Lagoon API to check if it is running
     * 
     * @return bool
     * @throws PolydockAppInstanceStatusFlowException If lagoon client is not found
     */
    public function pingLagoonAPI(): bool
    {
        if(!$this->lagoonClient) {
            throw new PolydockAppInstanceStatusFlowException('Lagoon client not found for ping');
        }

        try {
            $ping =  $this->lagoonClient->pingLagoonAPI();
            
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon API ping', ['ping' => $ping]);
            }

            return $ping;
        } catch (\Exception $e) {
            throw new PolydockAppInstanceStatusFlowException('Error pinging Lagoon API: ' . $e->getMessage());    
        }
    }

    /**
     * Sets the lagoon client from the app instance.
     * 
     * @param PolydockAppInstanceInterface $appInstance The app instance to set the lagoon client from
     * @return void
     */
    public function setLagoonClientFromAppInstance(PolydockAppInstanceInterface $appInstance): void
    {
        $engine = $appInstance->getEngine();
        $this->engine = $engine;

        $lagoonClientProvider = $engine->getPolydockServiceProviderSingletonInstance("PolydockServiceProviderFTLagoon");
        $this->lagoonClientProvider = $lagoonClientProvider;

        if (!method_exists($lagoonClientProvider, 'getLagoonClient')) {
            throw new PolydockAppInstanceStatusFlowException('Lagoon client provider does not have getLagoonClient method');
        } else {
            /** @phpstan-ignore-next-line */
            $this->lagoonClient = $lagoonClientProvider->getLagoonClient(); 
        }

        if(!$this->lagoonClient) {
            throw new PolydockAppInstanceStatusFlowException('Lagoon client not found');
        }

        if(!($this->lagoonClient instanceof LagoonClient)) {
            throw new PolydockAppInstanceStatusFlowException('Lagoon client is not an instance of LagoonClient');
        }
    }

    /**
     * Verifies that the lagoon values are available.
     * 
     * @param PolydockAppInstanceInterface $appInstance The app instance to verify
     * @param string $verifyLocation The location of the verification
     * @return bool True if the lagoon values are available, false otherwise
     */ 
    public function verifyLagoonValuesAreAvailable(PolydockAppInstanceInterface $appInstance, $verifyLocation): bool
    {
        $lagoonDeployGit = $appInstance->getKeyValue("lagoon-deploy-git");
        $lagoonRegionId = $appInstance->getKeyValue("lagoon-deploy-region-id");
        $lagoonOrganizationId = $appInstance->getKeyValue("lagoon-deploy-organization-id");
        $lagoonProjectPrefix = $appInstance->getKeyValue("lagoon-deploy-project-prefix");
        $appType = $appInstance->getAppType();
        
        if(!$lagoonDeployGit) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon deploy git value not set', ['class' => self::class, 'location' => $verifyLocation]);
            }
            return false;
        }

        if(!$lagoonRegionId) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon region id value not set', ['class' => self::class, 'location' => $verifyLocation]);
            }
            return false;
        }

        if(!$lagoonOrganizationId) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon organization id value not set', ['class' => self::class, 'location' => $verifyLocation]);
            }
            return false;
        }

        if(!$lagoonProjectPrefix) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon project prefix value not set', ['class' => self::class, 'location' => $verifyLocation]);
            }
            return false;
        }

        if(!$appType) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('App type value not set, and Polydock needs this to be set in Lagoon', ['class' => self::class, 'location' => $verifyLocation]);
            }
            return false;
        }

        return true;
    } 

    /**
     * Verifies that the project name is available.
     * 
     * @param PolydockAppInstanceInterface $appInstance The app instance to verify
     * @param string $verifyLocation The location of the verification
     * @return bool True if the project name is available, false otherwise
     */
    public function verifyLagoonProjectNameIsAvailable(PolydockAppInstanceInterface $appInstance, $verifyLocation): bool
    {
        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        if(!$projectName) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon project name not available', ['class' => self::class, 'location' => $verifyLocation]);
            }
            return false;
        }

        return true;
    }

    /**
     * Verifies that the project id is available.
     * 
     * @param PolydockAppInstanceInterface $appInstance The app instance to verify
     * @param string $verifyLocation The location of the verification
     * @return bool True if the project id is available, false otherwise
     */
    public function verifyLagoonProjectIdIsAvailable(PolydockAppInstanceInterface $appInstance, $verifyLocation): bool
    {
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        if(!$projectId) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon project id not available', ['class' => self::class, 'location' => $verifyLocation]);
            }
            return false;
        }

        return true;
    }

    /**
     * Verifies that the project name and id are available.
     * 
     * @param PolydockAppInstanceInterface $appInstance The app instance to verify
     * @param string $verifyLocation The location of the verification
     * @return bool True if the project name and id are available, false otherwise
     */
    public function verifyLagoonProjectAndIdAreAvailable(PolydockAppInstanceInterface $appInstance, $verifyLocation): bool
    {
        if(!$this->verifyLagoonProjectNameIsAvailable($appInstance, $verifyLocation)) {
            return false;
        }
     
        if(!$this->verifyLagoonProjectIdIsAvailable($appInstance, $verifyLocation)) {
            return false;
        }

        return true;
    }

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
     */
    public function preCreateAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface 
    {
        $this->setLagoonClientFromAppInstance($appInstance);

        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_PRE_CREATE) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to pre-create');
        }

        $this->info('Pre-creating app instance', ['class' => self::class, 'location' => 'preCreateAppInstance']);
        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_CREATE_RUNNING, "Starting pre-create");
        
        $ping = $this->pingLagoonAPI();
        if(!$ping) { 
            $appInstance->setStatus(PolydockAppInstanceStatus::PRE_CREATE_FAILED, "Lagoon API ping failed");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon API ping successful', ['class' => self::class, 'location' => 'preCreateAppInstance']);
        }

        if(!$this->verifyLagoonValuesAreAvailable($appInstance, 'preCreateAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::PRE_CREATE_FAILED, "Required Lagoon values not available");
            return $appInstance;
        }

        $appInstance->storeKeyValue("lagoon-project-name", $appInstance->generateUniqueProjectName($appInstance->getKeyValue("lagoon-deploy-project-prefix")));
        $projectName = $appInstance->getKeyValue("lagoon-project-name");

        if(!$this->verifyLagoonProjectNameIsAvailable($appInstance, 'preCreateAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::PRE_CREATE_FAILED, "Lagoon project name not available");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project name verified: ' . $projectName, ['class' => self::class, 'location' => 'preCreateAppInstance', 'projectName' => $projectName]);
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_CREATE_COMPLETED, "Pre-create completed");
        return $appInstance;
    }

    /**
     * Handles creation tasks for an app instance.
     * 
     * This method is called when creating the app instance. It validates the instance
     * is in the correct status, sets it to running, executes creation logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_CREATE status
     */
    public function createAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {
        $this->setLagoonClientFromAppInstance($appInstance);

        $this->info('Creating app instance', ['class' => self::class, 'location' => 'createAppInstance']);
        $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_RUNNING, "Starting create");

        $ping = $this->pingLagoonAPI();
        if(!$ping) { 
            $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_FAILED, "Lagoon API ping failed");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon API ping successful', ['class' => self::class, 'location' => 'createAppInstance']);
        }

        if(!$this->verifyLagoonValuesAreAvailable($appInstance, 'createAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_FAILED, "Required Lagoon values not available");
            return $appInstance;
        }

        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        if(!$this->verifyLagoonProjectNameIsAvailable($appInstance, 'createAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_FAILED, "Lagoon project name not available");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project name verified: ' . $projectName, ['class' => self::class, 'location' => 'createAppInstance', 'projectName' => $projectName]);
        }

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
            $this->error($createdProjectData['error'][0]['message']);
            $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_FAILED, "Failed to create Lagoon project - error returned from Lagoon API", ['error' => $createdProjectData['error']]);
            return $appInstance;
        }

        if(!isset($createdProjectData['addProject']['id'])) {
            $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_FAILED, "Failed to create Lagoon project - missing project id", ['error' => "Missing project id"]);
            return $appInstance;
        }

        $appInstance->storeKeyValue("lagoon-project-id", $createdProjectData['addProject']['id']);

        if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project created', ['class' => self::class, 'location' => 'createAppInstance', 'projectId' => $createdProjectData['addProject']['id']]);
        }
        
        $appInstance->setStatus(PolydockAppInstanceStatus::CREATE_COMPLETED, "Create completed");
        return $appInstance;
    }

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
     */
    public function postCreateAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {
        $this->setLagoonClientFromAppInstance($appInstance);
     
        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_POST_CREATE) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to post-create');
        }

        $this->info('Post-creating app instance', ['class' => self::class, 'location' => 'postCreateAppInstance']);
        $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_RUNNING, "Starting post-create");
        
        $ping = $this->pingLagoonAPI();
        if(!$ping) { 
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_FAILED, "Lagoon API ping failed");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon API ping successful', ['class' => self::class, 'location' => 'postCreateAppInstance']);
        }

        if(!$this->verifyLagoonValuesAreAvailable($appInstance, 'postCreateAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_FAILED, "Required Lagoon values not available");
            return $appInstance;
        }

        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        if(!$this->verifyLagoonProjectAndIdAreAvailable($appInstance, 'postCreateAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_FAILED, "Lagoon project name or id not available");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project name and id verified: ' . $projectName . ' and ' . $projectId, ['class' => self::class, 'location' => 'postCreateAppInstance', 'projectName' => $projectName, 'projectId' => $projectId]);
        }

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
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_FAILED, $e->getMessage() );
            return $appInstance;
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_COMPLETED, "Post-create completed");
        return $appInstance;
    }

    public function addOrUpdateLagoonProjectVariable(PolydockAppInstanceInterface $appInstance, $variableName, $variableValue, $variableScope): void
    {
        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        
        $variable = $this->lagoonClient->addOrUpdateScopedVariableForProject($projectName, $variableName, $variableValue, $variableScope);
        
        if(! isset($variable['addOrUpdateEnvVariableByName']['id'])) {
            throw new \Exception('Failed to add or update ' . $variableName . ' variable');
        }

        if($this->lagoonClient->getDebug()) {
            $variableId = $variable['addOrUpdateEnvVariableByName']['id'];
            $this->debug('Added or updated ' . $variableName . ' variable: ' . $variableValue, ['class' => self::class, 'location' => 'addOrUpdateLagoonProjectVariable', 'projectName' => $projectName, 'projectId' => $projectId, 'variableId' => $variableId]);
        }
    }

    /**
     * Handles pre-deployment tasks for an app instance.
     * 
     * This method is called before deploying the app instance. It validates the instance
     * is in the correct status, sets it to running, executes pre-deployment logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_PRE_DEPLOY status
     */
    public function preDeployAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {
        $this->setLagoonClientFromAppInstance($appInstance);

        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_PRE_DEPLOY) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to pre-deploy');
        }

        $this->info('Pre-deploying app instance', ['class' => self::class, 'location' => 'preDeployAppInstance']);
        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_DEPLOY_RUNNING, "Starting pre-deploy");
        
        $ping = $this->pingLagoonAPI();
        if(!$ping) { 
            $appInstance->setStatus(PolydockAppInstanceStatus::PRE_DEPLOY_FAILED, "Lagoon API ping failed");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon API ping successful', ['class' => self::class, 'location' => 'preDeployAppInstance']);
        }

        if(!$this->verifyLagoonValuesAreAvailable($appInstance, 'preDeployAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::PRE_DEPLOY_FAILED, "Required Lagoon values not available");
            return $appInstance;
        }

        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        if(!$this->verifyLagoonProjectAndIdAreAvailable($appInstance, 'preDeployAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::PRE_DEPLOY_FAILED, "Lagoon project name or id not available");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project name and id verified: ' . $projectName . ' and ' . $projectId, ['class' => self::class, 'location' => 'preDeployAppInstance', 'projectName' => $projectName, 'projectId' => $projectId]);
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_DEPLOY_COMPLETED, "Pre-deploy completed");
        return $appInstance;
    }

    /**
     * Handles deployment tasks for an app instance.
     * 
     * This method is called when the app instance is deployed. It validates the instance
     * is in the correct status, sets it to running, executes deployment logic,
     * and marks it as completed.
     *
      * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_DEPLOY status
     */
    public function deployAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {
        $this->setLagoonClientFromAppInstance($appInstance);

        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_DEPLOY) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to deploy');
        }

        $this->info('Deploying app instance', ['class' => self::class, 'location' => 'deployAppInstance']);
        $appInstance->setStatus(PolydockAppInstanceStatus::DEPLOY_RUNNING, "Starting deploy");
        
        $ping = $this->pingLagoonAPI();
        if(!$ping) { 
            $appInstance->setStatus(PolydockAppInstanceStatus::DEPLOY_FAILED, "Lagoon API ping failed");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon API ping successful', ['class' => self::class, 'location' => 'deployAppInstance']);
        }

        if(!$this->verifyLagoonValuesAreAvailable($appInstance, 'deployAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::DEPLOY_FAILED, "Required Lagoon values not available");
            return $appInstance;
        }
     
        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        if(!$this->verifyLagoonProjectAndIdAreAvailable($appInstance, 'deployAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::DEPLOY_FAILED, "Lagoon project name or id not available");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project name and id verified: ' . $projectName . ' and ' . $projectId, ['class' => self::class, 'location' => 'deployAppInstance', 'projectName' => $projectName, 'projectId' => $projectId]);
        }


        $appInstance->setStatus(PolydockAppInstanceStatus::DEPLOY_COMPLETED, "Deploy completed");
        return $appInstance;
    }

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
     */
    public function postDeployAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {
        $this->setLagoonClientFromAppInstance($appInstance);

        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_POST_DEPLOY) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to post-deploy');
        }

        $this->info('Post-deploying app instance', ['class' => self::class, 'location' => 'postDeployAppInstance']);
        $appInstance->setStatus(PolydockAppInstanceStatus::POST_DEPLOY_RUNNING, "Starting post-deploy");

        $ping = $this->pingLagoonAPI();
        if(!$ping) { 
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_DEPLOY_FAILED, "Lagoon API ping failed");
            return $appInstance;
        }  else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon API ping successful', ['class' => self::class, 'location' => 'postDeployAppInstance']);
        }
       
        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        if(!$this->verifyLagoonProjectAndIdAreAvailable($appInstance, 'postDeployAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_DEPLOY_FAILED, "Lagoon project name or id not available");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project name and id verified: ' . $projectName . ' and ' . $projectId, ['class' => self::class, 'location' => 'postDeployAppInstance', 'projectName' => $projectName, 'projectId' => $projectId]);
        }


        $appInstance->setStatus(PolydockAppInstanceStatus::POST_DEPLOY_COMPLETED, "Post-deploy completed");
        return $appInstance;
    }

    /**
     * Handles pre-removal tasks for an app instance.
     * 
     * This method is called before removing the app instance. It validates the instance
     * is in the correct status, sets it to running, executes pre-removal logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_PRE_REMOVE status
     */
    public function preRemoveAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {
        $this->setLagoonClientFromAppInstance($appInstance);

        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_PRE_REMOVE) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to pre-remove');
        }

        $this->info('Pre-removing app instance', ['class' => self::class, 'location' => 'preRemoveAppInstance']);
        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_REMOVE_RUNNING, "Starting pre-remove");
        
        $ping = $this->pingLagoonAPI();
        if(!$ping) { 
            $appInstance->setStatus(PolydockAppInstanceStatus::PRE_REMOVE_FAILED, "Lagoon API ping failed");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon API ping successful', ['class' => self::class, 'location' => 'preRemoveAppInstance']);
        }

        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        if(!$this->verifyLagoonProjectAndIdAreAvailable($appInstance, 'preRemoveAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::PRE_REMOVE_FAILED, "Lagoon project name or id not available");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project name and id verified: ' . $projectName . ' and ' . $projectId, ['class' => self::class, 'location' => 'preRemoveAppInstance', 'projectName' => $projectName, 'projectId' => $projectId]);
        }



        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_REMOVE_COMPLETED, "Pre-remove completed");
        return $appInstance;
    }

    /** 
     * Handles removal tasks for an app instance.
     * 
     * This method is called when the app instance is removed. It validates the instance
     * is in the correct status, sets it to running, executes removal logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_REMOVE status
     */
    public function removeAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {
        $this->setLagoonClientFromAppInstance($appInstance);

        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_REMOVE) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to remove');
        }

        $this->info('Removing app instance', ['class' => self::class, 'location' => 'removeAppInstance']);
        $appInstance->setStatus(PolydockAppInstanceStatus::REMOVE_RUNNING, "Starting remove");

        $ping = $this->pingLagoonAPI();
        if(!$ping) { 
            $appInstance->setStatus(PolydockAppInstanceStatus::REMOVE_FAILED, "Lagoon API ping failed");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon API ping successful', ['class' => self::class, 'location' => 'removeAppInstance']);
        }

        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        if(!$this->verifyLagoonProjectAndIdAreAvailable($appInstance, 'removeAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::REMOVE_FAILED, "Lagoon project name or id not available");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project name and id verified: ' . $projectName . ' and ' . $projectId, ['class' => self::class, 'location' => 'removeAppInstance', 'projectName' => $projectName, 'projectId' => $projectId]);
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::REMOVE_COMPLETED, "Remove completed");
        return $appInstance;
    }

    /**
     * Handles post-removal tasks for an app instance.
     * 
     * This method is called after removing the app instance. It validates the instance
     * is in the correct status, sets it to running, executes post-removal logic,
     * and marks it as completed.
     *
     * @param PolydockAppInstanceInterface $appInstance The app instance to process
     * @return PolydockAppInstanceInterface The processed app instance
     * @throws PolydockAppInstanceStatusFlowException If instance is not in PENDING_POST_REMOVE status
     */
    public function postRemoveAppInstance(PolydockAppInstanceInterface $appInstance): PolydockAppInstanceInterface
    {   
        $this->setLagoonClientFromAppInstance($appInstance);

        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_POST_REMOVE) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to post-remove');
        }

        $this->info('Post-removing app instance', ['class' => self::class, 'location' => 'postRemoveAppInstance']);
        $appInstance->setStatus(PolydockAppInstanceStatus::POST_REMOVE_RUNNING, "Starting post-remove");

        $ping = $this->pingLagoonAPI();
        if(!$ping) { 
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_REMOVE_FAILED, "Lagoon API ping failed");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon API ping successful', ['class' => self::class, 'location' => 'postRemoveAppInstance']);
        }

        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        if(!$this->verifyLagoonProjectAndIdAreAvailable($appInstance, 'postRemoveAppInstance')) {
            $appInstance->setStatus(PolydockAppInstanceStatus::POST_REMOVE_FAILED, "Lagoon project name or id not available");
            return $appInstance;
        } else if($this->lagoonClient->getDebug()) {
            $this->debug('Lagoon project name and id verified: ' . $projectName . ' and ' . $projectId, ['class' => self::class, 'location' => 'postRemoveAppInstance', 'projectName' => $projectName, 'projectId' => $projectId]);
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::POST_REMOVE_COMPLETED, "Post-remove completed");
        return $appInstance;
    }
}
