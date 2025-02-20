<?php

namespace FreedomtechHosting\PolydockAppAmazeeioGeneric;

use FreedomtechHosting\PolydockApp\PolydockAppBase;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceStatusFlowException;

class PolydockAppAmazeeioGeneric extends PolydockAppBase
{
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
        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_PRE_CREATE) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to pre-create');
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_CREATE_RUNNING);
        // TODO: Add your logic here
        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_CREATE_COMPLETED);
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
        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_POST_CREATE) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to post-create');
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_RUNNING);
        // TODO: Add your logic here
        $appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_COMPLETED);
        return $appInstance;
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
        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_PRE_DEPLOY) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to pre-deploy');
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_DEPLOY_RUNNING);
        // TODO: Add your logic here
        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_DEPLOY_COMPLETED);
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
        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_POST_DEPLOY) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to post-deploy');
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::POST_DEPLOY_RUNNING);
        // TODO: Add your logic here
        $appInstance->setStatus(PolydockAppInstanceStatus::POST_DEPLOY_COMPLETED);
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
        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_PRE_REMOVE) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to pre-remove');
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_REMOVE_RUNNING);
        // TODO: Add your logic here
        $appInstance->setStatus(PolydockAppInstanceStatus::PRE_REMOVE_COMPLETED);
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
        if($appInstance->getStatus() !== PolydockAppInstanceStatus::PENDING_POST_REMOVE) {
            throw new PolydockAppInstanceStatusFlowException('App instance is not in the correct status to post-remove');
        }

        $appInstance->setStatus(PolydockAppInstanceStatus::POST_REMOVE_RUNNING);
        // TODO: Add your logic here
        $appInstance->setStatus(PolydockAppInstanceStatus::POST_REMOVE_COMPLETED);
        return $appInstance;
    }
}
