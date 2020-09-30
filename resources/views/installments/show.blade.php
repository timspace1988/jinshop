@extends('layouts.app')
@section('title', 'View installment')

@section('content')
    <div class="row">
        <div class="col-10 offset-1">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 text-center">Installment details</h5>
                </div>
                <div class="card-body">
                    <div class="installment-top">
                        <div class="installment-info">
                            <div class="line">
                                <div class="line-label">Order: </div>
                                <div class="line-value">
                                    <a href="{{ route('orders.show', ['order' => $installment->order_id]) }}" target="_blank">View</a>
                                </div>
                            </div>
                            <div class="line">
                                <div class="line-label">Total amount</div>
                                <div class="line-value">$ {{ $installment->total_amount }}</div>
                            </div>
                            <div class="line">
                                <div class="line-label">Phases</div>
                                <div class="line-value">{{ $installment->count }} phases</div>
                            </div>
                            <div class="line">
                                <div class="line-label">Fee rate</div>
                                <div class="line-value">{{ $installment->fee_rate }} %</div>
                            </div>
                            <div class="line">
                                <div class="line-lable">Expiry fine rate</div>
                                <div class="line-value">{{ $installment->fine_rate }} %</div>
                            </div>
                            <div class="line">
                                <div class="line-label">Status</div>
                                <div class="line-value">{{ \App\Models\Installment::$statusMap[$installment->status] }}</div>
                            </div>
                        </div>
                        <div class="installment-next text-right">
                            <!-- if there is no unpaid installment item -->
                            @if(is_null($nextItem))
                                <div class="installment-clear text-center">This order has been paid in full</div>
                            @else
                                <div>
                                    <span>Next payment: </span>
                                    <div class="value total-amout">$ {{ $nextItem->total }}</div>
                                </div>
                                <div>
                                    <span>Payment due: </span>
                                    <div class="value">{{ $nextItem->due_date->format('Y-m-d') }}</div>
                                </div>
                                <div class="payment buttons">
                                    <a href="{{ route('installments.alipay', ['installment' => $installment->id]) }}" class="btn btn-primary btn-sm">AliPay</a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Phase</th>
                                <th>Payment due</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Fee</th>
                                <th>Overdue charge</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    Phase {{ $item->sequence + 1 }} / {{ $installment->count }}
                                </td>
                                <td>{{ $item->due_date->format('Y-m-d') }}</td>
                                <td>
                                    <!-- if not paid yet -->
                                    @if(is_null($item->paid_at))
                                        @if($item->is_overdue)
                                            <span class="overdue">Overdue</span>
                                        @else
                                            <span class="needs-repay">Not paid</span>
                                        @endif
                                    @else   
                                        <span class="repaid">Paid</span>
                                    @endif
                                </td>
                                <td>$ {{$item->base}}</td>
                                <td>$ {{ $item->fee }}</td>
                                <td>{{ is_null($item->fine) ? 'None' : ('$' . $item->fine) }}</td>
                                <td class="text-right">$ {{ $item->total }}</td>
                            </tr>
                        @endforeach
                        <tr><td colspan="7"></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection