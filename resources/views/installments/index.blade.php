@extends('layouts.app')
@section('title', 'Installment list')

@section('content')
    <div class="row">
        <div class="col-10 offset-1">
            <div class="card">
                <div class="card-header text-center"><h2>Installment list</h2>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Installment no</th>
                                    <th>Total amount</th>
                                    <th>Phases</th>
                                    <th>Fee rate</th>
                                    <th>Status</th>
                                    <th>Operations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($installments as $installment)
                                    <tr>
                                        <td>{{ $installment->no }}</td>
                                        <td>$ {{ $installment->total_amount }}</td>
                                        <td>{{ $installment->count }}</td>
                                        <td>{{ $installment->fee_rate }} %</td>
                                        <td>{{ \App\Models\Installment::$statusMap[$installment->status] }}</td>
                                        <td><a href="{{ route('installments.show', ['installment' => $installment->id]) }}" class="btn btn-primary btn-sm">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="float-right">{{ $installments->render() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection