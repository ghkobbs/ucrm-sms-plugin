<?php

declare(strict_types=1);

namespace SmsNotifier\Facade;

use SmsNotifier\Data\NotificationData;
use SmsNotifier\Data\PluginData;
use SmsNotifier\Factory\MessageTextFactory;
use SmsNotifier\Service\Logger;
use SmsNotifier\Service\OptionsManager;
use SmsNotifier\Service\SmsNumberProvider;
use MessageBird\Client;

class MBNotifierFacade extends AbstractMessageNotifierFacade {

    // /** @var Client */
    private $messageBirdClient;

    /** @var PluginData */
    private $pluginData;

    public function __construct(
        Logger $logger,
        MessageTextFactory $messageTextFactory,
        SmsNumberProvider $smsNumberProvider,
        OptionsManager $optionsManager
    )
    {
        parent::__construct($logger, $messageTextFactory, $smsNumberProvider);
        // load config data
        $this->pluginData = $optionsManager->load();
    }

    // $MessageBird = new \MessageBird\Client($this->pluginData->messageBirdKey);

    /*
     * Get Message Bird SMS API object (unless it's already initialized)
    */
    public function getmessageBirdClient(): Client
    {
        if (!$this->messageBirdClient) {
            $this->messageBirdClient = new Client(
                $this->pluginData->messageBirdKey
            );
        }
        return $this->messageBirdClient;
    }

    /*
     * Send message through the Message Bird client
    */
    protected function sendMessage(
        NotificationData $notificationData,
        string $clientSmsNumber,
        string $messageBody
    ): void
    {
        
        $Message = new \MessageBird\Objects\Message();
        
        $Message->originator = $this->getSenderNumber();
        $Message->recipients = array($clientSmsNumber);
        $Message->datacoding = $this->getDataCoding();
        $Message->body = $messageBody;
        // $MessageBird->messages->create($Message);

        $this->getmessageBirdClient()->messages->create($Message);
        
    }

    /*
     * Data coding - Tells Message Bird the text format to use.
    */
    private function getDataCoding(): string
    {
        return $this->pluginData->datacoding;
    }

    /*
     * Sender - required by Message Bird. In this plugin, we only load it from config.
     */
    private function getSenderNumber(): string
    {
        return $this->pluginData->originator;
    }
}
