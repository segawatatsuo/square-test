<?php

namespace App\Http\Controllers\Square;

use Square\SquareClient;
use Square\Types\Money;
#use Square\Types\CreatePaymentRequest;
use Square\Apis\PaymentsApi;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Square\Environments;
use Square\Models\CreatePaymentRequest;

class SquareController extends Controller
{
    protected $client;

    public function __construct()
    {

        // 環境変数の値を取得
        //$squareEnvironment = env('SQUARE_ENVIRONMENT', 'sandbox');
        //$accessToken = env('SQUARE_ACCESS_TOKEN');

        // 環境に対応するbaseUrlを設定
        //$baseUrl = $squareEnvironment === 'production' ? Environments::Production->value : Environments::Sandbox->value;

        /*
        $this->client = new SquareClient([
            'token' => $accessToken,
            'baseUrl' => $baseUrl,
        ]);
        */

        $client = new SquareClient(
            token: getenv('SQUARE_ACCESS_TOKEN') ?? '',
            options: [
                'baseUrl' => Environments::Sandbox->value,
            ],
        );
    }

    public function createPayment(Request $req)
    {
        try {
            $data = $req->all();

            $amountMoney = new Money();
            $amountMoney->setAmount(2500);
            $amountMoney->setCurrency('JPY');

            $idempotencyKey = Str::uuid()->toString();

            $body = new CreatePaymentRequest(
                $data['sourceId'],
                $idempotencyKey
            );
            $body->setAmountMoney($amountMoney);

            $paymentsApi = new PaymentsApi($this->client);

            $res = $paymentsApi->createPayment($body);

            if ($res->isSuccess()) {
                return response()->json($res->getResult());
            } else {
                $errors = $res->getErrors();
                \Log::error('Square Payment Error: ' . json_encode($errors));
                return response()->json(['errors' => $errors], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Square Payment Exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
