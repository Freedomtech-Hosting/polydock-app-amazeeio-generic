<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric;

use FreedomtechHosting\PolydockAppAmazeeioGeneric\Traits\UsesAmazeeAiBackend;
use FreedomtechHosting\PolydockAmazeeAIBackendClient\Client as AmazeeAiBackendClient;
class PolydockAiApp extends PolydockApp
{

    use UsesAmazeeAiBackend;

    /**
     * @var AmazeeAiBackendClient
     */
    protected AmazeeAiBackendClient $amazeeAiBackendClient;

    /**
     * @var bool
     */
    protected bool $requiresAiInfrastructure = true;

}