<?php

namespace App\Http\Controllers\Square;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Square\SquareClient;// SquareClient クラスをuseしていることを確認
use Square\Models\Money;
use Square\Models\CreatePaymentRequest; // 必要に応じて

class SquareController extends Controller
{
    protected $client;
    public function __construct()
    {
        $this->client = new SquareClient(
            env('SQUARE_ACCESS_TOKEN'), // 最初の引数としてアクセストークンを直接渡す
            null, // 2番目の引数（バージョン）は通常nullでOK
            [
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox'),
            ]
        );
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
