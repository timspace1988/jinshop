<?php

namespace App\Admin\Controllers;

use App\Exceptions\InternalException;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Http\Requests\Request;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Response;

class OrdersController extends AdminController
{
    use ValidatesRequests;//this trait is no included in AdminController from Laravel-Admin, so we need to add this trait here so that we can use $this->validate([],[],[])
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Order';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        //only display orders which are paid
        $grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');

        // $grid->column('id', __('Id'));
        // $grid->column('no', __('No'));
        // $grid->column('user_id', __('User id'));
        // $grid->column('address', __('Address'));
        // $grid->column('total_amount', __('Total amount'));
        // $grid->column('remark', __('Remark'));
        // $grid->column('paid_at', __('Paid at'));
        // $grid->column('payment_method', __('Payment method'));
        // $grid->column('payment_no', __('Payment no'));
        // $grid->column('refund_status', __('Refund status'));
        // $grid->column('refund_no', __('Refund no'));
        // $grid->column('closed', __('Closed'));
        // $grid->column('reviewed', __('Reviewed'));
        // $grid->column('ship_status', __('Ship status'));
        // $grid->column('ship_data', __('Ship data'));
        // $grid->column('extra', __('Extra'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        $grid->no('Order no');
        //use column metho to display relationship model's field
        $grid->column('user.name', 'Customer');
        $grid->total_amount('Total amount')->sortable();
        $grid->paid_at('Paid at')->sortable();
        $grid->ship_status('Ship status')->display(function($value){
            return Order::$shipStatusMap[$value];
        });
        $grid->refund_status('Refund status')->display(function($value){
            return Order::$refundStatusMap[$value];
        });

        //disable the 'new' button, we don't create new order in admin panel
        $grid->disableCreateButton();
        //disable delete and edit button
        $grid->actions(function($actions){
            $actions->disableDelete();
            $actions->disableEdit();
        });
        //disable batch delete button
        $grid->tools(function($tools){
            $tools->batch(function($batch){
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    //We create our own show details method and pages
    public function show($id, Content $content){
        //we will keep using laravel-admin's left and top menus, but that page's content will be ours
        return $content->header('Order details')
                       ->body(view('admin.orders.show', ['order' => Order::find($id)]));
    }

    //Send the items to customer
    public function ship(Order $order, Request $request){
        //Check if this order has been paid
        if(!$order->paid_at){
            throw new InvalidRequestException('This order has not been paid yet.');
        }
        //check if this order has been shipped(we only have 3 shipping status: pending, delivered, received)
        if($order->ship_status !== Order::SHIP_STATUS_PENDING){
            throw new InvalidRequestException('The items of order have been shipped.');
        }
        //crowdfunding order can only be sent ater the crowdfunding is successful
        if($order->type === Order::TYPE_CROWDFUNDING && $order->items[0]->product->crowdfunding->status !== CrowdfundingProduct::STATUS_SUCCESS){
            throw new InvalidRequestException('Crowdfunding order can only be sent after it was successful.');
        }
        //after laravel 5.5 validate method can return the data which just being validate
        $data = $this->validate($request,
                    [
                        'express_company' => ['required'],
                        'express_no' => ['required'],
                    ], 
                    [],
                    [
                        'express_company' => 'Courier company',
                        'express_no' => 'ship no',
                    ]);
        //Update the ship status to in delivery(SHIP_STATUS_DELIVERED here means being shipped)
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            //in Order model, we use $casts to cast 'ship_data' to json type before it been saved in database, that means ship_data should be assigned with an array, so we can directly assign $data to ship_data here
            'ship_data' => $data,
        ]);

        //return to previous page
        return redirect()->back();
    }

    //Administrator's action of handling refund application
    public function handleRefund(Order $order, HandleRefundRequest $request){
        
        //Check if the order's refund status is correct, must be applied status
        if($order->refund_status !== Order::REFUND_STATUS_APPLIED){
            throw new InvalidRequestException('Incorrect order-refund status');
        }
        //dd('hello');
        //check if administrator approve the refund request
        if($request->input('agree')){
            //firstly, if agree to refund, we need to clear previous refund delince reason
            $extra = $order->extra ?: [];
            unset($extra['refund_disagree_reason']);
            $order->update(['extra' => $extra]);
            
            //execute refund logic
            $this->_refundOrder($order);
        }else{
            //dd('hello');
            //put reason of rejecting refund into order's extra field
            $extra = $order->extra ?: [];
            $extra['refund_disagree_reason'] = $request->input('reason'); 

            //update order's extra and refund status
            $order->update([
                'extra' => $extra,
                'refund_status' => Order::REFUND_STATUS_PENDING,//if admin doesn't approve refund, we will not display refund info on order details page, so we set it pending status
            ]);

            return $order;
        }

    
    }

    //method execute refund logic(this method will be called in handleRefund method if admi agree to refund)
    public function _refundOrder(Order $order){
        //return "test";
        //return Response::json(['msg' => 'test']);
  

        //Check what payment method is used by customer on this order
        switch($order->payment_method){
            case 'wechat':
                //we leave wechat out temporarily,
                break;
            case 'alipay':
                //generate a refund no
                $refundNo = $order->getAvailableRefundNo();

                //call refund method of alily instance, $ret is the returned data
                try{
                    $ret = app('alipay')->refund([
                        'out_trade_no' => $order->no,//order no
                        'refund_amount' => $order->total_amount,
                        'out_request_no' =>$refundNo,//refund no we just generated
                    ]);
                }catch(\Throwable $t){
                    return['code' => $t->getCode(), 'msg' => $t->getMessage()];
                }
                //$ret = 'alipay sandbox server collapsed, use this for just for test.';


                //according to alipay document, if returned data contains sub_code field, it means refund failed
                //if(!$ret){
                if($ret->sub_code){
                    //save refund failed code into order's extra field
                    $extra = $order->extra;
                    $extra['refund_failed_code'] = $ret->sub_code;
                    //Save(update) order's refund_no, refund_status and extra
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_FAILED,
                        'extra' => $extra,
                    ]);
                   return $ret; 
                }else{
                    // if refund sucess, save the refund no and update refund status
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_SUCCESS,
                    ]);
                    return $ret;
                }
                break;
            default:
                //usually will not happened, doing this will make system rebust
                throw new InternalException('Unknown payment method: ' . $order->payment_method);
                break;
        }


    } 

    // /**
    //  * Make a show builder.
    //  *
    //  * @param mixed $id
    //  * @return Show
    //  */
    // protected function detail($id)
    // {
    //     $show = new Show(Order::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('no', __('No'));
    //     $show->field('user_id', __('User id'));
    //     $show->field('address', __('Address'));
    //     $show->field('total_amount', __('Total amount'));
    //     $show->field('remark', __('Remark'));
    //     $show->field('paid_at', __('Paid at'));
    //     $show->field('payment_method', __('Payment method'));
    //     $show->field('payment_no', __('Payment no'));
    //     $show->field('refund_status', __('Refund status'));
    //     $show->field('refund_no', __('Refund no'));
    //     $show->field('closed', __('Closed'));
    //     $show->field('reviewed', __('Reviewed'));
    //     $show->field('ship_status', __('Ship status'));
    //     $show->field('ship_data', __('Ship data'));
    //     $show->field('extra', __('Extra'));
    //     $show->field('created_at', __('Created at'));
    //     $show->field('updated_at', __('Updated at'));

    //     return $show;
    // }

    // /**
    //  * Make a form builder.
    //  *
    //  * @return Form
    //  */
    // protected function form()
    // {
    //     $form = new Form(new Order());

    //     $form->text('no', __('No'));
    //     $form->number('user_id', __('User id'));
    //     $form->textarea('address', __('Address'));
    //     $form->decimal('total_amount', __('Total amount'));
    //     $form->textarea('remark', __('Remark'));
    //     $form->datetime('paid_at', __('Paid at'))->default(date('Y-m-d H:i:s'));
    //     $form->text('payment_method', __('Payment method'));
    //     $form->text('payment_no', __('Payment no'));
    //     $form->text('refund_status', __('Refund status'))->default('pending');
    //     $form->text('refund_no', __('Refund no'));
    //     $form->switch('closed', __('Closed'));
    //     $form->switch('reviewed', __('Reviewed'));
    //     $form->text('ship_status', __('Ship status'))->default('pending');
    //     $form->textarea('ship_data', __('Ship data'));
    //     $form->textarea('extra', __('Extra'));

    //     return $form;
    // }
}
