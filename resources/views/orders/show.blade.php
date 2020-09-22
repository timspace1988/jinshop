@extends('layouts.app')
@section('title', 'Order details')

@section('content')

@endsection

@section('scriptsAfterJs')
<script>
    $(document).ready(function(){
        //click on received button
        $('#btn-receive').click(function(){
            //Confirm window pops out
            swal({
                title: "Have you received your items?",
                icon: "warning",
                dangerous: true,
                buttons: ['Cancel', 'Yes'],
            }).then(function(ret){
                //if click on cancel, then do nothing
                if(!ret){
                    return;
                }
                //submit the received request using ajax
                axios.post('{{ route("orders.received", [$order->id])}}')
                     .then(function(){
                          //reload the page
                          location.reload();
                      });
            });
        });

        //click on apply for refund button
        $('#btn-apply-refund').click(function(){
            swal({
                text: 'Please enter your reason for refund.',
                content: "input",
                //the 'input' here will be used by following function as param in this script, it's different from $request->input('param') in controller, which is the data sent via post method                
            }).then(function(input){
                //when user hit the  submit button on pop-out window, it will execute this function
                if(!input){
                    swal('You have not enter the reason for refund');
                    return
                }

                //request access for refund interface
                axios.post('{{ route("orders.apply_refund", [$order->id]) }}', {reason: input})
                     .then(function(){
                         swal('Your request for refund is submitted', '', 'success').then(function(){
                             //when user hit the confirm button on pop-out window, reload the page
                             location.reload();
                         });
                     });
            });
        });
    });
</script>
@endsection