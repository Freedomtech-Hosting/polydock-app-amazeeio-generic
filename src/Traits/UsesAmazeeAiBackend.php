<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits;

use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceStatusFlowException;
use FreedomtechHosting\PolydockAmazeeAIBackendClient\Client;


trait UsesAmazeeAiBackend
{
    /**
     * Sets the lagoon client from the app instance.
     * 
     * @param PolydockAppInstanceInterface $appInstance The app instance to set the lagoon client from
     * @return void
     * @throws PolydockAppInstanceStatusFlowException If lagoon client is not found
     */
    public function setAmazeeAiBackendClientFromAppInstance(PolydockAppInstanceInterface $appInstance): void
    {
        $engine = $appInstance->getEngine();
        $this->engine = $engine;

        $amazeeAiBackendClientProvider = $engine->getPolydockServiceProviderSingletonInstance("PolydockServiceProviderAmazeeAiBackend");
        $this->amazeeAiBackendClientProvider = $amazeeAiBackendClientProvider;

        if (!method_exists($amazeeAiBackendClientProvider, 'getAmazeeAiBackendClient')) {
            throw new PolydockAppInstanceStatusFlowException('Amazee AI backend client provider does not have getAmazeeAiBackendClient method');
        } else {
            // TODO: Fix this, this is a hack to get around the fact that the lagoon client provider is not typed
            /** @phpstan-ignore-next-line */
            $this->amazeeAiBackendClient = $this->amazeeAiBackendClientProvider->getAmazeeAiBackendClient(); 
        }

        if(!$this->amazeeAiBackendClient) {
            throw new PolydockAppInstanceStatusFlowException('Amazee AI backend client not found');
        }

        if(!($this->amazeeAiBackendClient instanceof Client)) {
            throw new PolydockAppInstanceStatusFlowException('Amazee AI backend client is not an instance of ' . Client::class);
        }

        if(!$this->pingAmazeeAiBackend()) {
            throw new PolydockAppInstanceStatusFlowException('Amazee AI backend is not healthy');
        }

        if(!$this->checkAmazeeAiBackendAuth()) {
            throw new PolydockAppInstanceStatusFlowException('Amazee AI backend is not authorized');
        }
    }

    public function checkAmazeeAiBackendAuth(): bool
    {
        $logContext = $this->getLogContext(__FUNCTION__);

        $this->info('Checking amazeeAI backend auth', $logContext);

        $response = $this->amazeeAiBackendClient->getMe();
        
        if(! $response['is_admin']) {
            $this->error('Amazee AI backend is not authorized as an admin', $logContext + $response);
            throw new PolydockAppInstanceStatusFlowException('Amazee AI backend is not authorized as an admin');
        }

        if(! $response['is_active']) {
            $this->error('Amazee AI backend is not an active admin', $logContext + $response);
            throw new PolydockAppInstanceStatusFlowException('Amazee AI backend is not an active admin');
        }
        
        $this->info('Amazee AI backend is authorized and active', $logContext + $response);
        return true;
    }

    public function pingAmazeeAiBackend(): bool
    {
        $logContext = $this->getLogContext(__FUNCTION__);

        if(!$this->amazeeAiBackendClient) {
            throw new PolydockAppInstanceStatusFlowException('amazeeAI backend client not found for ping');
        }

        try {
            $response = $this->amazeeAiBackendClient->health();

            if (is_array($response) && isset($response['status'])) {
                if ($response['status'] === 'healthy') {
                    $this->info('amazeeAI backend is healthy', $logContext + $response);
                    return true;
                } else {
                    $this->error('amazeeAI backend is not healthy: ', $logContext + $response);
                    return false;
                }
            } else {
                $this->error('Error pinging amazeeAI backend: ', $logContext + $response);
                return false;
            }
        } catch (\Exception $e) {
            $this->error('Error pinging amazeeAI backend: ', $logContext + ['error' => $e->getMessage()]);
            throw new PolydockAppInstanceStatusFlowException('Error pinging Lagoon API: ' . $e->getMessage());    
        }

        return false;
    }
}