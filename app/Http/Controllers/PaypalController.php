<?php

namespace App\Http\Controllers;
use Srmklive\PayPal\Services\ExpressCheckout;
use Illuminate\Http\Request;
use NunoMaduro\Collision\Provider;
// use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Cart;
use App\Models\Product;
use PayPal as PayPalClient;
use DB;
class PaypalController extends Controller
{

    public function payment()
    {
        return view('checkout');
    }

    /**
     * process transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function processTransaction(Request $request)
    {
        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $cart = Cart::where('user_id',auth()->user()->id)->where('order_id',null)->get()->toArray();
        
        $data = [];
        
        // return $cart;
        $data['items'] = array_map(function ($item) use($cart) {
            $name=Product::where('id',$item['product_id'])->pluck('title');
            return [
                'name' =>$name ,
                'price' => $item['price'],
                'desc'  => 'Thank you for using paypal',
                'qty' => $item['quantity']
            ];
        }, $cart);

        $data['invoice_id'] ='ORD-'.strtoupper(uniqid());
        $data['invoice_description'] = "Order #{$data['invoice_id']} Invoice";
        $data['return_url'] = route('payment.success');
        $data['cancel_url'] = route('payment.cancel');

        $total = 0;
        foreach($data['items'] as $item) {
            $total += $item['price']*$item['qty'];
        }

        $data['total'] = $total;
        if(session('coupon')){
            $data['shipping_discount'] = session('coupon')['value'];
        }
        Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => session()->get('id')]);

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('successTransaction'),
                "cancel_url" => route('cancelTransaction'),
            ],
            "purchase_units" => [
                $data['items']
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {

            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }

            return redirect()
                ->route('payment')
                ->with('error', 'Something went wrong.');

        } else {
            return redirect()
                ->route('payment')
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    /**
     * success transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function successTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            return redirect()
                ->route('payment')
                ->with('success', 'Transaction complete.');
        } else {
            return redirect()
                ->route('payment')
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelTransaction(Request $request)
    {
        return redirect()
            ->route('payment')
            ->with('error', $response['message'] ?? 'You have canceled the transaction.');
    }

    // public function payment()
    // {
    //     $cart = Cart::where('user_id',auth()->user()->id)->where('order_id',null)->get()->toArray();
        
    //     $data = [];
        
    //     // return $cart;
    //     $data['items'] = array_map(function ($item) use($cart) {
    //         $name=Product::where('id',$item['product_id'])->pluck('title');
    //         return [
    //             'name' =>$name ,
    //             'price' => $item['price'],
    //             'desc'  => 'Thank you for using paypal',
    //             'qty' => $item['quantity']
    //         ];
    //     }, $cart);

    //     $data['invoice_id'] ='ORD-'.strtoupper(uniqid());
    //     $data['invoice_description'] = "Order #{$data['invoice_id']} Invoice";
    //     $data['return_url'] = route('payment.success');
    //     $data['cancel_url'] = route('payment.cancel');

    //     $total = 0;
    //     foreach($data['items'] as $item) {
    //         $total += $item['price']*$item['qty'];
    //     }

    //     $data['total'] = $total;
    //     if(session('coupon')){
    //         $data['shipping_discount'] = session('coupon')['value'];
    //     }
    //     Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => session()->get('id')]);

    //     // return session()->get('id');
    //     $provider = new ExpressCheckout;
  
    //     $response = $provider->setExpressCheckout($data);
    
    //     return redirect($response['paypal_link']);
    // }
   
    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        dd('Your payment is canceled. You can create cancel page here.');
    }
  
    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function success(Request $request)
    {
        $provider = new ExpressCheckout;
        $response = $provider->getExpressCheckoutDetails($request->token);
        // return $response;
  
        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            request()->session()->flash('success','You successfully pay from Paypal! Thank You');
            session()->forget('cart');
            session()->forget('coupon');
            return redirect()->route('home');
        }
  
        request()->session()->flash('error','Something went wrong please try again!!!');
        return redirect()->back();
    }
}
