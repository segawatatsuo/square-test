<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>LaravelとSquareで決済実装（最小限のコード）</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script
            type="text/javascript"
            src="https://sandbox.web.squarecdn.com/v1/square.js"
        ></script>
        <script>
            const applicationId = "{{ config('services.square.application_id') }}";
            const locationId = "{{ config('services.square.location_id') }}";
            async function cardInit(payments) {
                try {
                    const card = await payments.card();
                    await card.attach('#cardBox');
                    return card;
                } catch (e) {
                    alert(e);
                }
            }
            async function getToken(paymentMethod) {
                const result = await paymentMethod.tokenize();
                if (result.status === 'OK') {
                    return result.token;
                } else {
                  alert(result.errors);
                }
            }
            async function createPayment(token) {
                $.ajax({
                    type: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '/api/v1/square/payment',
                    data: JSON.stringify({
                        locationId,
                        sourceId: token,
                    }),
                    dataType : "json"
                }).done(function(result){
                    alert(
                      '決済ID：' + result.payment.id + 
                      '　支払い額：' + result.payment.amount_money.amount + '円'
                    );
                })
            }
            $(window).on('load', async function () {
                const payments = window.Square.payments(applicationId, locationId);
                const card = await cardInit(payments);
                $('#button').on('click', async function (event) {
                    const token = await getToken(card);
                    await createPayment(token);
                });
            });
        </script>
    </head>
    <body>
        <form>
            <h1>入会費：2500円</h1>
            <div id="cardBox"></div>
            <button id="button" type="button">支払う</button>
        </form>
    </body>
</html>