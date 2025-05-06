<?php

namespace App\Services;

use Illuminate\Http\Request;

class SurroundingsApi
{
    private $currentSearchType;

    private $yelpApiKey = 'Bearer 5KElIffVD9ak_CJuO9JR50t_nAYM-VFG7SauuRDQfoxH-Lp84ZxwKpyo15Bm9F2mq9tU5eNF-Rhw5DVgazxmp28F0BYAtJG126RKh4y3zAmlmU6wK_SUBIpTlFbzX3Yx';

    private $ticketMasterDiscovery = 'https://app.ticketmaster.com/discovery/v2/events.json?apikey=7g94WBVHVpPZfTD7ANdBQdC438WXCHOj';

    private $ticketMasterDiscoveryClassifications = 'https://app.ticketmaster.com/discovery/v2/classifications.json?apikey=7g94WBVHVpPZfTD7ANdBQdC438WXCHOj';

    public function __construct()
    {
    }

    public function pullInfoObject(Request | string $request)
    {
        if (gettype($request) === 'string') {
            $serviceUrl = $request;
        } else {
            $validatedData = $request->validate([
                'config_url' => 'required|string'
            ]);
            $serviceUrl = $validatedData['config_url'];
        }

        $curl = curl_init($serviceUrl);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->yelpApiKey,
        ]);

        $curlResponse = curl_exec($curl);

        if ($curlResponse === false)
        {
            $info = curl_getinfo($curl);
            curl_close($curl);
            die('curl error');
        }

        curl_close($curl);

        if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR')
        {
            die('error occured: ' . $decoded1->response->errormessage);
        }

        return json_decode($curlResponse);
    }

    public function searchBusinesses(Request $request)
    {
        $serviceUrl = $request->config_url;

        $curl = curl_init($serviceUrl);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->yelpApiKey,
        ]);

        $curlResponse = curl_exec($curl);

        if ($curlResponse === false)
        {
            $info = curl_getinfo($curl);
            curl_close($curl);
            die('curl error');
        }

        curl_close($curl);

        if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR')
        {
            die('error occured: ' . $decoded1->response->errormessage);
        }

        return json_decode($curlResponse);
    }

    public function searchEvents(Request $request)
    {
        $serviceUrl = $this->ticketMasterDiscovery . '&' . $request->config_url;

        $curl = curl_init($serviceUrl);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curlResponse = curl_exec($curl);

        if ($curlResponse === false)
        {
            $info = curl_getinfo($curl);
            curl_close($curl);
            die('curl error');
        }

        curl_close($curl);

        if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR')
        {
            die('error occured: ' . $decoded1->response->errormessage);
        }

        return json_decode($curlResponse);
    }

    public function getEvent(Request $request)
    {
        $serviceUrl = $this->ticketMasterDiscovery . '&' . $request->config_url;

        $curl = curl_init($serviceUrl);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curlResponse = curl_exec($curl);

        if ($curlResponse === false)
        {
            $info = curl_getinfo($curl);
            curl_close($curl);
            die('curl error');
        }

        curl_close($curl);

        if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR')
        {
            die('error occured: ' . $decoded1->response->errormessage);
        }

        return json_decode($curlResponse);
    }

    public function getClassifications(Request $request)
    {
        $serviceUrl = $this->ticketMasterDiscoveryClassifications;

        try
        {
            $curl = curl_init($serviceUrl);

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $curlResponse = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($curlResponse === false)
            {
                throw new Exception(curl_error($curl), curl_errno($curl));
            }

            curl_close($curl);
        }
        catch(Exception $e)
        {
            trigger_error(
                sprintf(
                    'Curl failed with error #%d: %s',
                    $e->getCode(),
                    $e->getMessage()
                ),
                E_USER_ERROR
            );
        }

        if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR')
        {
            die('error occured: ' . $decoded1->response->errormessage);
        }

        return json_decode($curlResponse);
    }
}
