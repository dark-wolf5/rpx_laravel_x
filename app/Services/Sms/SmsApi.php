<?php

namespace App\Services\Sms;

use TextMagic\Models\SendMessageInputObject;
use TextMagic\Api\TextMagicApi;
use TextMagic\Configuration;

use Illuminate\Support\Facades\Log;

class SmsApi
{
    private $tMobile;
    private $mintMobile;
    private $verizon;
    private $att;
    private $carrier;
    private $textMagicToken;
    private $username;

    public function __construct(
        private string $phoneNumber
    ) {
        $this->textMagicToken = config('services.text_magic.api_token');
        $this->username = config('services.text_magic.username');
    }

    public function getCarrierFromPh(): string {
        // TextMagic API
        $phone = $this->phoneNumber;
        $url = "https://rest.textmagic.com/api/v2/lookups/$phone?country=US";
        $ch = curl_init();
        $token = $this->textMagicToken;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERNAME, $this->username);
        curl_setopt($ch, CURLOPT_PASSWORD, $token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Access-Control-Allow-Credentials: true',
            'Content-Type: application/json'
        ]);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
    }

    public function sendSms(string $message) {
        // put your Username and API Key from https://my.textmagic.com/online/api/rest-api/keys page.
        $config = Configuration::getDefaultConfiguration()
            ->setUsername(
                config('services.text_magic.username')
            )
            ->setPassword(
                config('services.text_magic.api_token')
            );

        $api = new TextMagicApi(
            new \GuzzleHttp\Client(),
            $config
        );

        // Simple ping request example
        try {
            $result = $api->ping();
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling TextMagicApi->ping: ', $e->getMessage(), PHP_EOL;
        }

        // Send a new message request example
        $input = new SendMessageInputObject();
        $input->setText('Test message test');

        $input->setPhones('+17866005946');

        try {
            $result = $api->sendMessage($input);
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling TextMagicApi->sendMessage: ', $e->getMessage(), PHP_EOL;
        }
    }
}
