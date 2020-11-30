<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BCAController extends Controller
{
    public static $hostUrl = 'https://sandbox.bca.co.id';
    // public static $hostUrl = 'https://devapi.klikbca.com:8066';
    // public static $hostUrl = 'https://api.klikbca.com:8065';
    public static $clientID = '457a9dba-f844-4ce9-9391-8b9e2b0a5543';
    public static $clientSecret = 'b8199802-6257-45e2-ae02-b79d4306b588';
    public static $APIKey = 'cb8a20b6-50b2-407e-97ae-55df5642005a';
    public static $APISecret = 'edac6735-e0bf-4acd-9cf5-c7647bbc9a37';
    public static $accessToken = null;
    public static $timeStamp = null;
    // public static $corp_id = 'KBBARDATAM';
    public static $corp_id = 'BCAAPI2016';
    public static $client;

    public function __construct()
    {
        self::$timeStamp = date('o-m-d') . 'T' . date('H:i:s') . '.' . substr(date('u'), 0, 3) . date('P');
        self::$client = new \GuzzleHttp\Client;
        $this->initialToken();
    }

    public function initialToken()
    {
        $output = self::$client->request('POST', self::$hostUrl . '/api/oauth/token', [
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode(self::$clientID . ':' . self::$clientSecret),
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);
        $output = json_decode($output->getBody(), true);
        return self::$accessToken = $output['access_token'];
    }

    public function getSignature($HTTPMethod, $relativeUrl, $RequestBody = '')
    {
        if (is_array($RequestBody)) {
            ksort($RequestBody);
            $encoderData = json_encode($RequestBody, JSON_UNESCAPED_SLASHES);

            $RequestBody = strtolower(hash('sha256', $encoderData));
        } else {
            $RequestBody = strtolower(hash('sha256', $RequestBody));
        }
        $StringToSign = $HTTPMethod . ":" . $relativeUrl . ":" . self::$accessToken . ":" . $RequestBody . ":" . self::$timeStamp;
        $signature = hash_hmac('sha256', $StringToSign, self::$APISecret);
        return $signature;
    }

    public function getStatements($payload = array())
    {

        $path = '/banking/v3/corporates/' . self::$corp_id .
            '/accounts/' . '0201245680' .
            '/statements?' .
            'EndDate=' . '2016-09-01' .
            '&StartDate=' . '2016-08-29';
        $method = 'GET';

        $output = self::$client->request($method, self::$hostUrl . $path, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$accessToken,
                'Content-Type' => 'application/json',
                'Origin' => $_SERVER['SERVER_NAME'],
                'X-BCA-Key' => self::$APIKey,
                'X-BCA-Timestamp' => self::$timeStamp,
                'X-BCA-Signature' => $this->getSignature($method, $path),
            ],
        ]);

        $res = json_decode($output->getBody());
        return response()->json($res);
    }

    public function getBalance()
    {
        $path = "/banking/v3/corporates/" . self::$corp_id . "/accounts/0201245680";

        $method = "GET";

        $request = self::$client->request($method, self::$hostUrl . $path, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$accessToken,
                'Content-Type' => 'application/json',
                'Origin' => $_SERVER['SERVER_NAME'],
                'X-BCA-Key' => self::$APIKey,
                'X-BCA-Timestamp' => self::$timeStamp,
                'X-BCA-Signature' => $this->getSignature($method, $path),
            ],
        ]);

        $res = json_decode($request->getBody());
        return response()->json($res);
    }

    public function fundTransfer()
    {

        $path = "/banking/corporates/transfers";

        $method = "POST";

        $bodyData = array();
        $bodyData['Amount'] = '50000.00';
        // transfer ke
        $bodyData['BeneficiaryAccountNumber'] = strtolower(str_replace(' ', '', '0201245681'));
        $bodyData['CorporateID'] = strtolower(str_replace(' ', '', self::$corp_id));
        $bodyData['CurrencyCode'] = 'idr';
        // nomer referensi
        $bodyData['ReferenceID'] = strtolower(str_replace(' ', '', '12345/PO/2017'));
        $bodyData['Remark1'] = strtolower(str_replace(' ', '', 'Testing'));
        $bodyData['Remark2'] = strtolower(str_replace(' ', '', 'Testing'));
        // transfer dari
        $bodyData['SourceAccountNumber'] = strtolower(str_replace(' ', '', '0201245680'));
        $bodyData['TransactionDate'] = self::$timeStamp;
        // ID transaksi
        $bodyData['TransactionID'] = strtolower(str_replace(' ', '', '00000001'));

        // Harus disort agar mudah kalkulasi HMAC
        ksort($bodyData);

        $request = self::$client->request($method, self::$hostUrl . $path, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$accessToken,
                'Content-Type' => 'application/json',
                'Origin' => $_SERVER['SERVER_NAME'],
                'X-BCA-Key' => self::$APIKey,
                'X-BCA-Timestamp' => self::$timeStamp,
                'X-BCA-Signature' => $this->getSignature($method, $path, $bodyData),
            ],
            'json' => $bodyData,
        ]);

        $res = json_decode($request->getBody());

        return response()->json($res);
    }

    public function transferStatus($transactionID, $transactionDate, $transactionType)
    {
        // $path = "/banking/corporates/transfers/status/17071800840035?TransactionDate=2017-07-18&TransferType=BCA";
        $path = "/banking/corporates/transfers/status/$transactionID?TransactionDate=$transactionDate&TransferType=$transactionType";

        $method = "GET";

        $request = self::$client->request($method, self::$hostUrl . $path, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$accessToken,
                'Content-Type' => 'application/json',
                'Origin' => $_SERVER['SERVER_NAME'],
                'X-BCA-Key' => self::$APIKey,
                'X-BCA-Timestamp' => self::$timeStamp,
                'X-BCA-Signature' => $this->getSignature($method, $path),
                // 'ChannelID' => '',
                'CredentialID' => self::$corp_id,
            ],
        ]);

        $res = json_decode($request->getBody());
        return response()->json($res);
    }

    public function getForex($payload = array())
    {

        $RateType = (empty($payload['rate_type'])) ? 'E-RATE' : $payload['rate_type'];
        $Currency = (empty($payload['symbol_currency'])) ? 'USD' : $payload['symbol_currency'];

        $path = '/general/rate/forex?Currency=' . $Currency . '&RateType=' . $RateType;
        $method = 'GET';

        $output = self::$client->request($method, self::$hostUrl . $path, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$accessToken,
                'Content-Type' => 'application/json',
                'Origin' => $_SERVER['SERVER_NAME'],
                'X-BCA-Key' => self::$APIKey,
                'X-BCA-Timestamp' => self::$timeStamp,
                'X-BCA-Signature' => $this->getSignature($method, $path),
            ],
        ]);

        return $output->getBody();
    }
}
