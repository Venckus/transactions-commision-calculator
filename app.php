<?php

class CommisionsCalculator extends Transaction
{
    private $rates;
    private $isEu;
    private $transaction;


    public function __construct()//$transaction)
    {
        // $this->splitedParams($transaction);
        // $this->rates = $this->getRates();
    }


    public function process($transaction)
    {
        $this->extractTransactionValues($transaction)
            ->getCountry()
            ->getRates()
        ;
        // print_r($this); die();
    }

    protected function getCountry()
    {
        $binResults = file_get_contents(
            'https://lookup.binlist.net/' .$this->transaction->bin
        );
        $json = json_decode($binResults);

        $this->isEu($json->country->alpha2);
        
        return $this;
    }
    protected function getRates()
    {
        $r = @json_decode(
            file_get_contents(
                'https://api.exchangeratesapi.io/latest'),
                true
            )['rates'][$this->transaction->currency];
        print_r($r); die();
        $this->rates = $r;
        return $this;
    }


    private function extractTransactionValues($transaction)
    {
        $transaction_jsn = json_decode($transaction, true);

        if (isset($transaction_jsn)) {

            $this->transaction = new Transaction();

            foreach ($transaction_jsn as $key => $value) {

                $this->transaction->$key = $value;
            }
        }
        // print_r($this->transaction); die();
        return $this;
    }


    private function isEu($countryCode)
    {
        $euCodes = [
            'AT',
            'BE',
            'BG',
            'CY',
            'CZ',
            'DE',
            'DK',
            'EE',
            'ES',
            'FI',
            'FR',
            'GR',
            'HR',
            'HU',
            'IE',
            'IT',
            'LT',
            'LU',
            'LV',
            'MT',
            'NL',
            'PO',
            'PT',
            'RO',
            'SE',
            'SI',
            'SK',
        ];

        if (in_array($countryCode, $euCodes)) {

            return 'yes';

        } else {

            return 'no';
        }
    }
}


class Transaction
{
    protected $bin;
    protected $currency;
    protected $country;

    protected function setBin($bin)
    {
        $this->bin = $bin;
    }

    protected function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    protected function setCountry($country)
    {
        $this->country = $country;
    }
}

$client = new CommisionsCalculator();

foreach (explode("\n", file_get_contents($argv[1])) as $row) {

    $client->process($row);
}