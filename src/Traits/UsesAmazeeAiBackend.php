<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits;

use FreedomtechHosting\PolydockApp\PolydockAppInstanceStatusFlowException;

trait UsesAmazeeAiBackend
{
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