<?php

namespace Armincms\SofreApi\Http\Controllers;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Armincms\Orderable\Models\Order as Invoice;
use Armincms\SofreApi\Http\Requests\OrderRequest;
use Armincms\Sofre\Models\Order;
use Armincms\Sofre\Helper;

class OrderStoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request 
     * @return \Illuminate\Http\Response
     */
    public function handle(OrderRequest $request)
    {   
        $request->validateOrder(); 

        \Auth::loginUsingId(2);

        [$order, $invoice] = \DB::transaction(function() use ($request) {
            $order = $this->createOrder($request, $invoice = $this->createInvoice(
                $request, $restaurant = $request->findRestaurant()->load('menus.food')
            ));

            return [$order, $invoice];
        });    

        return [
            'Tracking Code' => strval($invoice->trackingCode()),
            'Number'        => $order->number,// restaurant invoice number
            'Payment Url'   => app('site')->get('orders')->url("{$invoice->trackingCode()}/billing"),
            'Status'        => $order->marked_as,
        ];
    }   

    /**
     * Create new Invoice for the given request.
     * 
     * @param  \Illuminate\Http\Request  $request 
     * @param \Illuminate\Database\Eloqeunt\Model           
     * @return \Illuminate\Database\Eloqeunt\Model           
     */
    protected function createInvoice(Request $request, $restaurant)
    {
        return tap(Invoice::createFromModel($restaurant), function($invoice) use ($request, $restaurant) {
            $addItemCallback = function($menu) use ($invoice, $request) {
                $invoice->addItem($menu, intval($request->get("items.{$menu->id}")) ?: 1);
            };

            $restaurant->menus->whereIn('id', array_keys((array) $request->get('items')))->each(
                $addItemCallback
            );

            $invoice->asOnHold(); 
        });  
    } 

    /**
     * Create new order for the given rqestrequest.
     * 
     * @param  \Illuminate\Http\Request  $request 
     * @param \Illuminate\Database\Eloqeunt\Model           
     * @return \Illuminate\Database\Eloqeunt\Model           
     */
    public function createOrder($request, $invoice)
    {
        return tap(new Order, function($order) use ($invoice, $request) {
            $order->invoice()->associate($invoice);

            $order->forceFill([
                'sending_method' => $request->get('sending_method'),
                'payment_method' => $payment = $request->get('payment_method'),
            ]);

            $payment === 'online' ? $order->asPending() : $order->asOnHold(); 
        });
    }
}
