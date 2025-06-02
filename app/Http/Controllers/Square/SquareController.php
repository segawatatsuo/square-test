<?php

namespace App\Http\Controllers\Square;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Square\SquareClient;

class SquareController extends Controller
{
    public function __construct()
    {
        $this->client = SquareClient::builder()
            ->accessToken(env('SQUARE_ACCESS_TOKEN'))
            ->environment(env('SQUARE_ENVIRONMENT', 'sandbox'))
            ->build();
    }


    public function createPayment(Request $req)
    {
        try {

            $data = $req->all();

            $amountMoney = new \Square\Models\Money();
            $amountMoney->setAmount(2500);
            $amountMoney->setCurrency('JPY');

            $body = new \Square\Models\CreatePaymentRequest(
                $data['sourceId'],
                Str::uuid()->toString(),
            );

            $body->setAmountMoney($amountMoney);

            $res = $this->client->getPaymentsApi()->createPayment($body);

            if ($res->isSuccess()) {
                return response()->json($res->getResult());
            } else {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
