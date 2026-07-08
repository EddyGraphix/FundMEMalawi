<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use Paychangu\Laravel\Http\Client as SdkHttpClient;
use Paychangu\Laravel\Paychangu;
use Paychangu\Laravel\Resources\Banks\Card;
use Paychangu\Laravel\Resources\Banks\DirectCharge;
use Paychangu\Laravel\Resources\BillPayments\Airtime;
use Paychangu\Laravel\Resources\BillPayments\Bill;
use Paychangu\Laravel\Resources\Checkout;
use Paychangu\Laravel\Resources\MobileMoney\MobileMoney;
use Paychangu\Laravel\Resources\Payouts\BankPayout;
use Paychangu\Laravel\Resources\Payouts\MobileMoneyPayout;
use Paychangu\Laravel\Resources\Verification;
use Paychangu\Laravel\Resources\VirtualAccount\Customers as VirtualAccountCustomers;

class TimedPayChangu extends Paychangu
{
    protected function createTimedClient(string $url): SdkHttpClient
    {
        $guzzle = new GuzzleClient([
            'base_uri' => $url,
            'timeout' => 30,
            'connect_timeout' => 15,
            'headers' => [
                'Authorization' => 'Bearer '.$this->privateKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        return new SdkHttpClient($this->privateKey, $url, $guzzle);
    }

    public function checkout(): Checkout
    {
        $url = $this->apiBaseUrl.'payment';

        return new Checkout($this->createTimedClient($url));
    }

    public function mobile_money(): MobileMoney
    {
        $url = $this->apiBaseUrl.'mobile-money/';

        return new MobileMoney($this->createTimedClient($url));
    }

    public function direct_charge(): DirectCharge
    {
        $url = $this->apiBaseUrl.'direct-charge/';

        return new DirectCharge($this->createTimedClient($url));
    }

    public function card(): Card
    {
        $url = $this->apiBaseUrl.'charge-card/';

        return new Card($this->createTimedClient($url));
    }

    public function mobile_money_payout(): MobileMoneyPayout
    {
        $url = $this->apiBaseUrl.'mobile-money/';

        return new MobileMoneyPayout($this->createTimedClient($url));
    }

    public function bank_payout(): BankPayout
    {
        $url = $this->apiBaseUrl.'direct-charge/';

        return new BankPayout($this->createTimedClient($url));
    }

    public function bill(): Bill
    {
        $url = $this->apiBaseUrl.'bills/';

        return new Bill($this->createTimedClient($url));
    }

    public function airtime(): Airtime
    {
        $url = $this->apiBaseUrl.'bills/';

        return new Airtime($this->createTimedClient($url));
    }

    public function verification(): Verification
    {
        $url = $this->apiBaseUrl.'payment';

        return new Verification($this->createTimedClient($url));
    }

    public function virtual_account_customers(): VirtualAccountCustomers
    {
        $url = $this->apiBaseUrl.'virtual-account/api/';

        return new VirtualAccountCustomers($this->createTimedClient($url));
    }
}
