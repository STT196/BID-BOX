@extends('product.layout.app')

@section('content')



{{-- if the product found and returned to blade from controller --}}
@isset($product)
<div class="container">
    <div class="card">
      <div class="container-fliud">
        <div class="wrapper row">
          <div class="preview col-md-6">
                        <div class="preview-pic tab-content">
                            <img src="{{ asset('assets/images/product/'.$product->image_path) }}" class="img-fluid">

                        </div>
          </div>
          <div class="details col-md-6">

            <h3 class="product-title">{{$product->name}}</h3>
                <h4><span>Category</span> : {{$category->name}}</h4>

            <div class="rating">
              <span class="review-no">Total BIDs : {{ $bid_info[0] }}</span>
            </div>

            <p class="product-description"><textarea cols='40' rows='20'>{!! nl2br($product->long_description) !!}</textarea></p>
            @if($bid_info[1]>' ')

                 <?php
                    $p= $bid_info[1]
                ?>

                <h4 class="price">Current Bid Price: <span>{{ number_format((float)$p, 2, '.', '') }}</span></h4>
            @elseif($bid_info[1]<=$product->price)
                        <h4 class="price">Price Start: <span>{{ number_format((float)$product->price, 2, '.', '')}}</span></h4>
            @endif
            
            <div class="card_area d-flex align-items-center">

            @auth {{-- Check if user is logged in and is user the owner of this product --}}
            @if(Auth::user()->id != $product->user_id)
                <form id="bid-form" class="row g-3" method="POST" action="{{route('product.bid' , $product->id )}}" onsubmit="return confirm('Are you sure to submit this Bid?' );">
                @csrf
                    <div class="col-auto">
                        <label for="amount" class="visually-hidden"></label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Rs</div>
                            </div>
                            <input  id="amount" name="amount" type="number" class="form-control" step="any"  placeholder="Enter Your Amount">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary mb-3"> BID NOW </button>
                    </div>
                </form>
                            
            @endif
            @endauth
            </div>
                <p id="demo" style="color:red;"></p>
          </div>
        </div>
      </div>
    </div>
  </div>

@endisset


{{-- IF there is no product with this id --}}
@empty($product)
<div class="alert alert-danger text-center" role="alert">
        <strong>{{ _('Product Not Found')}}</strong> 
    </div>
@endempty



{{-- Getting Random Errors  --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif



{{-- Success Bid alert **not yet finished**  --}}
@if ($message = \Session::get('success'))
  <div class="alert alert-success text-justify">
    {{ $message }}
  </div>
@endif


@endsection




{{--JS for Count Down Timer --}}
@isset($product)
<script>

            // Set the date we're counting down to
            var countDownDate = new Date(new Date("{{ $product->expired_at }}").getTime());
            // Update the count down every 1 second
            var x = setInterval(function() {
                // if (document.getElementById("hour") == null) {
                //     return; -10 -24 -14
                // }
                var demo = document.getElementById('demo');
                var d
                var s ;
                var h;
                var m;
                var f = true;
                // Get today's date and time
                var now = new Date().getTime();
                // Find the distance between now and the count down date
                var distance = countDownDate - now;
                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                // Output the result in an element with id="demo"
                d = days;
                h = hours ;
                m = minutes;
                s = seconds;
                demo.innerHTML = d + "d   " + h + "m   " + m + "min   " + s + "s   ";
                // If the count down is over, write some text
                if (distance < 1) {
                    $('#bid-form').hide();
                    $('#demo').hide();
                    clearInterval(x);
                } else {
                    if (f) {
                        
                        f = false;
                    }
                }
            }, 1000);
</script>
@endisset

