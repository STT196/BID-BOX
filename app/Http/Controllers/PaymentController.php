<?php
  
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use Omnipay\Omnipay;
use App\Models\Payment;
 
class PaymentController extends Controller
{
    public $gateway;
    public $completePaymentUrl;
 
    public function __construct()
    {
        $this->gateway = Omnipay::create('Stripe\PaymentIntents');
        $this->gateway->setApiKey( env('STRIPE_SECRETE'));
        $this->completePaymentUrl = url('confirm');
    }
 
    public function index()
    {
        return view('payment.payment');
    }
 
    public function process(Request $request)
    {
        if($request->input('stripeToken'))
        {
            $token = $request->input('stripeToken');
 
            $response = $this->gateway->authorize([
                'amount' => $request->input('amount'),
                'currency' => env('STRIPE_CURRENCY'),
                'description' => 'This is a X purchase transaction.',
                'token' => $token,
                'returnUrl' => $this->completePaymentUrl,
                'confirm' => true,
            ])->send();
 
            if($response->isSuccessful())
            {
                $response = $this->gateway->capture([
                    'amount' => $request->input('amount'),
                    // 'currency' => env('STRIPE_CURRENCY'),
                    'paymentIntentReference' => $response->getPaymentIntentReference(),
                ])->send();
 
                $arr_payment_data = $response->getData();
                // dd($arr_payment_data);
                $this->store_payment([      //මේ ටිකම වෙනස් කරන්න ඕනෙ
                    'payment_id' => $arr_payment_data['id'],
                    'email' => $request->input('email'), //auth user මෙතඩ් එකෙන් මේල් එක ගමු
                    'amount' => $arr_payment_data['amount']/100,
                    'currency' => env('STRIPE_CURRENCY'),
                    'status' => $arr_payment_data['status'],
                ]);
 
                return redirect("payment")->with("success", "Payment is successful. Your payment id is: ". $arr_payment_data['id']);
            }
            elseif($response->isRedirect())
            {
                session(['email' => $request->input('email')]);
                $response->redirect();
            }
            else
            {
                return redirect("payment")->with("error", $response->getMessage());
            }
        }

        // dd($request);
    }
 
    public function confirm(Request $request)
    {
        $response = $this->gateway->confirm([
            'paymentIntentReference' => $request->input('payment_intent'),
            'returnUrl' => $this->completePaymentUrl,
        ])->send();
         
        if($response->isSuccessful())
        {
            $response = $this->gateway->capture([
                'amount' => $request->input('amount'),
                'currency' => env('STRIPE_CURRENCY'),
                'paymentIntentReference' => $request->input('payment_intent'),
            ])->send();
 
            $arr_payment_data = $response->getData();
 
            $this->store_payment([
                'payment_id' => $arr_payment_data['id'],
                'email' => session('payer_email'),
                'amount' => $arr_payment_data['amount']/100,
                // 'currency' => env('STRIPE_CURRENCY'),
                'status' => $arr_payment_data['status'],
            ]);
 
            return redirect("payment")->with("success", "Payment is successful. Your payment id is: ". $arr_payment_data['id']);
        }
        else
        {
            return redirect("payment")->with("error", $response->getMessage());
        }
    }
 
    public function store_payment($arr_data = [])
    {
        $isPaymentExist = Payment::where('payment_id', $arr_data['payment_id'])->first();  
  
        if(!$isPaymentExist)
        {
            $payment = new Payment;
            $payment->payment_id = $arr_data['payment_id'];
            $payment->email = $arr_data['email'];
            $payment->amount = $arr_data['amount'];
            $payment->currency = env('STRIPE_CURRENCY');
            $payment->status = $arr_data['status'];
            $payment->save();
        }
    }
}