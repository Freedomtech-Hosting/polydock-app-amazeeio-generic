<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric;

use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Create\PreCreateAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Create\CreateAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Create\PostCreateAppInstanceTrait;

use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Deploy\PreDeployAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Deploy\DeployAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Deploy\PostDeployAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Deploy\PollDeployProgressAppInstanceTrait;

use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Remove\PreRemoveAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Remove\RemoveAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Remove\PostRemoveAppInstanceTrait;

use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Upgrade\PreUpgradeAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Upgrade\UpgradeAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Upgrade\PostUpgradeAppInstanceTrait;
use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Upgrade\PollUpgradeProgressAppInstanceTrait;   

use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\Health\PollHealthProgressAppInstanceTrait;

use FreedomtechHosting\PolydockApp\PolydockAppBase;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceStatusFlowException;
use FreedomtechHosting\FtLagoonPhp\Client as LagoonClient;
use FreedomtechHosting\PolydockApp\PolydockEngineInterface;
use FreedomtechHosting\PolydockApp\PolydockServiceProviderInterface;
use FreedomtechHosting\PolydockApp\PolydockAppVariableDefinitionBase;
use FreedomtechHosting\PolydockApp\PolydockAppVariableDefinitionInterface;

class PolydockApp extends PolydockAppBase
{
    // Create
    use PreCreateAppInstanceTrait;
    use CreateAppInstanceTrait;
    use PostCreateAppInstanceTrait;

    // Deploy
    use PreDeployAppInstanceTrait;
    use DeployAppInstanceTrait;
    use PostDeployAppInstanceTrait;
    use PollDeployProgressAppInstanceTrait;
    // Remove
    use PreRemoveAppInstanceTrait;
    use RemoveAppInstanceTrait;
    use PostRemoveAppInstanceTrait;

    // Upgrade
    use PreUpgradeAppInstanceTrait;
    use UpgradeAppInstanceTrait;
    use PostUpgradeAppInstanceTrait;
    use PollUpgradeProgressAppInstanceTrait;
    // Health
    use PollHealthProgressAppInstanceTrait;

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
     * Get the default variable definitions for this app specifically
     * 
     * @return array<PolydockAppVariableDefinitionInterface> 
     */
    public static function getAppDefaultVariableDefinitions(): array
    {
        return [
            new PolydockAppVariableDefinitionBase('lagoon-deploy-git'),
            new PolydockAppVariableDefinitionBase('lagoon-deploy-branch'),
            new PolydockAppVariableDefinitionBase('lagoon-deploy-region-id'),
            new PolydockAppVariableDefinitionBase('lagoon-deploy-private-key'),
            new PolydockAppVariableDefinitionBase('lagoon-deploy-organization-id'),
            new PolydockAppVariableDefinitionBase('lagoon-deploy-project-prefix'),
            new PolydockAppVariableDefinitionBase('lagoon-project-name'),
        ];
    }

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
     * @throws PolydockAppInstanceStatusFlowException If lagoon client is not found
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
            // TODO: Fix this, this is a hack to get around the fact that the lagoon client provider is not typed
            /** @phpstan-ignore-next-line */
            $this->lagoonClient = $this->lagoonClientProvider->getLagoonClient(); 
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
    public function verifyLagoonValuesAreAvailable(PolydockAppInstanceInterface $appInstance, $logContext = []): bool
    {
        $lagoonDeployGit = $appInstance->getKeyValue("lagoon-deploy-git");
        $lagoonRegionId = $appInstance->getKeyValue("lagoon-deploy-region-id");
        $lagoonPrivateKey = $appInstance->getKeyValue("lagoon-deploy-private-key");
        $lagoonOrganizationId = $appInstance->getKeyValue("lagoon-deploy-organization-id");
        $lagoonProjectPrefix = $appInstance->getKeyValue("lagoon-deploy-project-prefix");
        $lagoonProjectName = $appInstance->getKeyValue("lagoon-project-name");
        $appType = $appInstance->getAppType();
        
        if(!$lagoonDeployGit) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon deploy git value not set', $logContext);
            }
            return false;
        }

        if(!$lagoonRegionId) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon region id value not set', $logContext);
            }
            return false;
        }

        if(!$lagoonPrivateKey) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon private key value not set', $logContext);
            }
            return false;
        }

        if(!$lagoonOrganizationId) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon organization id value not set', $logContext);
            }
            return false;
        }

        if(!$lagoonProjectPrefix) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon project prefix value not set', $logContext);
            }
            return false;
        }

        if(!$appType) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('App type value not set, and Polydock needs this to be set in Lagoon', $logContext);
            }
            return false;
        }

        if(!$lagoonProjectName) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon project name value not set', $logContext);
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
    public function verifyLagoonProjectNameIsAvailable(PolydockAppInstanceInterface $appInstance, $logContext = []): bool
    {
        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        if(!$projectName) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon project name not available', $logContext);
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
    public function verifyLagoonProjectIdIsAvailable(PolydockAppInstanceInterface $appInstance, $logContext = []): bool
    {
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        if(!$projectId) {
            if($this->lagoonClient->getDebug()) {
                $this->debug('Lagoon project id not available', $logContext);
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
    public function verifyLagoonProjectAndIdAreAvailable(PolydockAppInstanceInterface $appInstance, $logContext = []): bool
    {
        if(!$this->verifyLagoonProjectNameIsAvailable($appInstance, $logContext)) {
            return false;
        }
     
        if(!$this->verifyLagoonProjectIdIsAvailable($appInstance, $logContext)) {
            return false;
        }

        return true;
    }

    public function validateLagoonPingAndThrowExceptionIfFailed($logContext = []): void
    {
        $ping = $this->pingLagoonAPI();
        if(!$ping) {
            $this->error('Lagoon API ping failed', $logContext);
            throw new PolydockAppInstanceStatusFlowException('Lagoon API ping failed');
        }
    }

    public function validateAppInstanceStatusIsExpectedAndConfigureLagoonClientAndVerifyLagoonValues(
        PolydockAppInstanceInterface $appInstance,
        PolydockAppInstanceStatus $expectedStatus,
        $logContext = [],
        bool $testLagoonPing = true,
        bool $verifyLagoonValuesAreAvailable = true,
        bool $verifyLagoonProjectNameIsAvailable = true,
        bool $verifyLagoonProjectIdIsAvailable = true
    ): void
    {
        $this->validateAppInstanceStatusIsExpected($appInstance, $expectedStatus, $logContext);
        $this->setLagoonClientFromAppInstance($appInstance, $logContext);

        if($testLagoonPing) {
            $this->validateLagoonPingAndThrowExceptionIfFailed($appInstance);
            $this->info('Lagoon API ping successful', $logContext);
        }

        if($verifyLagoonValuesAreAvailable) {
            if(!$this->verifyLagoonValuesAreAvailable($appInstance, $logContext)) {
                $this->error('Required Lagoon values not available', $logContext);
                throw new PolydockAppInstanceStatusFlowException('Required Lagoon values not available');
            }
        }

        if($verifyLagoonProjectNameIsAvailable) {
            if(!$this->verifyLagoonProjectNameIsAvailable($appInstance, $logContext)) {
                $this->error('Lagoon project name not available', $logContext);
                throw new PolydockAppInstanceStatusFlowException('Lagoon project name not available');
            }
        }

        if($verifyLagoonProjectIdIsAvailable) {
            if(!$this->verifyLagoonProjectIdIsAvailable($appInstance, $logContext)) {
                $this->error('Lagoon project id not available', $logContext);
                throw new PolydockAppInstanceStatusFlowException('Lagoon project id not available');
            }
        }
    }

    /**
     * Get the log context for a specific function.
     * 
     * @param string $location The location of the log context
     * @return array The log context
     */
    public function getLogContext(string $location): array
    {
        return ['class' => self::class, 'location' => $location];
    }

    public function addOrUpdateLagoonProjectVariable(PolydockAppInstanceInterface $appInstance, $variableName, $variableValue, $variableScope): void
    {
        $projectName = $appInstance->getKeyValue("lagoon-project-name");
        $projectId = $appInstance->getKeyValue("lagoon-project-id");
        $logContext = $this->getLogContext('addOrUpdateLagoonProjectVariable');
        $logContext['projectName'] = $projectName;
        $logContext['projectId'] = $projectId;
        $logContext['variableName'] = $variableName;
        $logContext['variableValue'] = $variableValue;
        $logContext['variableScope'] = $variableScope;
        
        $variable = $this->lagoonClient->addOrUpdateScopedVariableForProject($projectName, $variableName, $variableValue, $variableScope);
        
        if(isset($variable['error'])) {
            $this->error('Failed to add or update ' . $variableName . ' variable', 
                    $logContext + [
                        'lagoonVariable' => $variable, 
                        'error' => $variable['error']
                    ]);
            throw new \Exception('Failed to add or update ' . $variableName . ' variable');
        }

        if($this->lagoonClient->getDebug()) {
            $this->debug('Added or updated variable', $logContext);
        }
    }
}
