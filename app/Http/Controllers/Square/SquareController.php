<?php

namespace App\Http\Controllers\Square;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Square\SquareClient;
use Square\Models\Money;
use Square\Models\CreatePaymentRequest;
use Square\Environments; // Environments クラスをuseしていることを確認

class SquareController extends Controller
{
    protected $client;

    public function __construct()
    {
        // 環境変数の値を取得
        $squareEnvironment = env('SQUARE_ENVIRONMENT', 'sandbox');
        $accessToken = env('SQUARE_ACCESS_TOKEN');

        // 環境に対応するbaseUrlを設定
        $baseUrl = $squareEnvironment === 'production' ? Environments::Production->value : Environments::Sandbox->value;

    // ここに一時的に追加
    dd([
        'accessToken' => $accessToken,
        'baseUrl' => $baseUrl,
        'accessToken_type' => gettype($accessToken),
        'baseUrl_type' => gettype($baseUrl),
    ]);


        $this->client = new SquareClient([
            'token' => $accessToken,
            'baseUrl' => $baseUrl,
        ]);
    }

    public function createPayment(Request $req)
    {
        try {
            $data = $req->all();

            // 金額をint型で設定
            $amountMoney = new Money();
            $amountMoney->setAmount(2500); // 2500円
            $amountMoney->setCurrency('JPY');

            $idempotencyKey = Str::uuid()->toString();

            $body = new CreatePaymentRequest(
                $data['sourceId'],
                $idempotencyKey
            );

            $body->setAmountMoney($amountMoney);

            // 必要に応じて、locationIdを設定
            // $body->setLocationId($data['locationId']); // フロントエンドからlocationIdが渡されている場合

            $res = $this->client->getPaymentsApi()->createPayment($body);

            if ($res->isSuccess()) {
                return response()->json($res->getResult());
            } else {
                $errors = $res->getErrors();
                // エラーログの出力や、より詳細なエラーメッセージの返却を検討
                \Log::error('Square Payment Error: ' . json_encode($errors));
                return response()->json(['errors' => $errors], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Square Payment Exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}