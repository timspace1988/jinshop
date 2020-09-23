<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Moontoast\Math\BigNumber;

class InstallmentItem extends Model
{
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING => 'pending',
        self::REFUND_STATUS_PROCESSING => 'processing',
        self::REFUND_STATUS_SUCCESS => 'successful',
        self::REFUND_STATUS_FAILED => 'failed',
    ];

    protected $fillable = [
        'sequence',
        'base',
        'fee',
        'fine',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_no',
        'refund_status',
    ];

    protected $dates = ['due_date', 'paid_at'];

    public function installment(){
        return $this->belongsTo(Installment::class);
    }

    //a visitor, it returns the total amount the customer need to pay(total amount includes full price and all other fees)
    public function getTotalAttribute(){
        //bcmath provides methods for decimals calculation, it is not convenient to use, so we changed to sue moontoas/math
        //$total = bcadd($this->base, $this->fee, 2);

        //moontoast/math is an objcet-oriented encapusulation of bcmath, it provides add(), subtract(), multiply(), divide() and other methods
        //second param in new BigNumber is the precision scale, we need to set it everytime we new a BigNumber, it is still not so convenient, so, write a help function in bootstrap/herlpers.php, check it
        //$total = (new BigNumber($this->base, 2))->add($this->fee);

        $total = big_number($this->base)->add($this->fee);

        //if there is fine, add it to total
        if(!is_null($this->fine)){
            $total->add($this->fine);
        }

        return $total->getValue();
    }

    //a visitor, it check if current installment item is dued
    public function getIsOverdueAttribute(){
        return Carbon::now()->gt($this->due_date);
    }
}
