<?php
#mrpc/Command/Twitch/DefaultController.php

namespace mrpc\Command\Twitch;

use mrpc\TwitchChatClient;
use Minicli\Command\CommandController;
use mrpc\SevenDaysToDie;

class DefaultController extends CommandController
{
    /**
     * @var TwitchChatClient
     */
    protected $client = null;

    public function handle()
    {
        $app = $this->getApp();
        $this->getPrinter()->info("Connecting to 7 Days To Die server...");
        $sevenDaysToDie = new SevenDaysToDie(
            $app->config->settings['7days']['hostname'],
            $app->config->settings['7days']['port'],
            $app->config->settings['7days']['password']
        );
        $sevenDaysToDie->connect();
        if (!$sevenDaysToDie->isConnected()) {
            $this->getPrinter()->error(
                $sevenDaysToDie->getLastError()
            );
            return;
        }
        $this->getPrinter()->info("Connected.\n\n");
        $this->getPrinter()->info("Starting Minichat...");

        

        $twitch_user = $app->config->settings['twitch']['username'];
        $twitch_oauth = $app->config->settings['twitch']['oauth'];
        $twitch_channel = $app->config->settings['twitch']['channel'];

        if (!$twitch_user OR !$twitch_oauth) {
            $this->getPrinter()->error(
                "Missing twitch credentials."
            );
            return;
        }

        

        $client = new TwitchChatClient(
            $twitch_user, $twitch_oauth, $twitch_channel
        );
        $client->connect();

        if (!$client->isConnected()) {
            $this->getPrinter()->error(
                "It was not possible to connect."
        );
            return;
        }
        $this->client = $client;

        $this->getPrinter()->info("Connected to Twitch.\n");

        /**
         * Main Loop
         */
        while (true) {
            $content = $client->read(512);
            $sevenDaysContent = $sevenDaysToDie->read(512);

            //is it a ping?
            if (strstr($content, 'PING')) {
                $client->send('PONG :tmi.twitch.tv');
                continue;
            }

            //is it an actual msg?
            if (strstr($content, 'PRIVMSG')) {
                $this->parseMessage($content);
                continue;
            }

            if (trim($content) != '') {
                $this->getPrinter()->info($content);
                $this->getPrinter()->newline();
                continue;
            }
            if (trim($sevenDaysContent) != '') {
                \mrpc\Logger::log(trim($sevenDaysContent), '7daystodie');
            }

            usleep(100000);
        }
    }

    /**
     * Parse a message and print the output
     */
    public function parseMessage($raw_message)
    {
        \mrpc\Logger::log(trim($raw_message), 'twitchLogs');
        $this->getPrinter()->out(' --- RAW START ---');
        $this->getPrinter()->newline();
        $this->getPrinter()->out($raw_message);
        $this->getPrinter()->newline();
        $this->getPrinter()->out(' --- RAW END ---');
        $this->getPrinter()->newline();
        $this->getPrinter()->newline();

        $parts = explode(":", $raw_message, 3);
        $nick_parts = explode("!", $parts[1]);

        $infoParts = explode(';', $parts[0]);

        var_dump($parts);
        var_dump($nick_parts);
        var_dump($infoParts);

        $nick = $nick_parts[0];
        if (isset($parts[2])) {
            $message = trim(preg_replace('/\s\s+/', ' ',$parts[2]));
        } else {
            $message = '';
        }

        $style_nick = "info";

        if ($nick === $this->getApp()->config->twitch_user) {
            $style_nick = "info_alt";
        }

        $this->getPrinter()->out($nick, $style_nick);
        $this->getPrinter()->out(': ');
        $this->getPrinter()->out($message);
        $this->getPrinter()->newline();
        switch ($message) {
            case "!spawn";
                $this->asnwer('what do you want to spawn?');
                break;
        }

    }

    /**
     * Send an answer to twitch channel
     */
    public function asnwer($message)
    {
        $this->client->send(
            'PRIVMSG #' 
            . $this->getApp()->config->twitch_channel 
            . ' :' 
            . $message
        );
        $this->getPrinter()->out("twitchBot: ", 'info_alt');
        $this->getPrinter()->out($message);
        $this->getPrinter()->newline();
    }
}