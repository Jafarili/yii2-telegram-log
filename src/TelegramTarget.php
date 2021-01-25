<?php

namespace Jafarili\log;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\StringHelper;
use yii\log\Target;

/**
 * Yii 2.0 Telegram Log Target
 * TelegramTarget sends selected log messages to the specified telegram chats or channels
 *
 * You should set [telegram bot token](https://core.telegram.org/bots#botfather) and chatId in your config file like below code:
 * ```php
 * 'log' => [
 *     'targets' => [
 *         [
 *             'class' => 'Jafarili\log\TelegramTarget',
 *             'levels' => ['error'],
 *             'botToken' => '123456:abcde', // bot token secret key
 *             'chatId' => '123456', // chat id or channel username with @ like 12345 or @channel
 *         ],
 *     ],
 * ],
 * ```
 *
 * @author Ali Jafari <ali@jafari.li>
 */
class TelegramTarget extends Target
{
    /**
     * [Telegram bot token](https://core.telegram.org/bots#botfather)
     * @var string
     */
    public $botToken;

    /**
     * Destination chat id or channel username
     * @var int|string
     */
    public $chatId;

    /**
     * Telegram Api object
     * @var Api|array|string
     */
    public $telegramApi;

    /**
     * @var int max character in message text.
     */
    public $substitutionMaxLength = 3000;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->chatId === null) {
            throw new InvalidConfigException(self::className() . "::chatId property must be set");
        }
        if ($this->botToken === null) {
            throw new InvalidConfigException(self::className() . "::botToken property must be set");
        }
        $this->telegramApi = Instance::ensure($this->telegramApi, 'Telegram\Bot\Api');
        $this->telegramApi->setAccessToken($this->botToken);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $messages = array_map([$this, 'formatMessage'], $this->messages);
        $this->sendMessage(implode("\n\n", $messages));
    }

    /**
     * Send a Telegram message with the given body content.
     * @param string $body the body content
     * @return Message $message
     */
    protected function sendMessage($body)
    {
        $body = StringHelper::truncate($body, $this->substitutionMaxLength);
        return $this->telegramApi->sendMessage([
            'chat_id' => $this->chatId,
            'text' => $body,
        ]);
    }
}
