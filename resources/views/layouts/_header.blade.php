<nav class="navbar navbar-expand-lg navbar-light bg-light navbar-static-top">
    <div class=container>
        <!-- branding image -->
        <a class="navbar-brand" href="{{ url('/') }}">
            JinShop
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- left side of navbar -->
            <ul class="navbar-nav mr-auto">
                <!-- Beginning of category tree on top -->
                <!-- firstly check if $categoryTree is passed here -->
                @if(isset($categoryTree))
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="categoryTree">All categories <b class="caret"></b></a>
                        <ul class="dropdown-menu" aria-labelledby="categoryTree">
                            <!-- traverse the $categoryTree collection, pass each entry as $category varible to the template 'layouts._category_item', render it and display it here -->
                            @each('layouts._category_item', $categoryTree, 'category')
                            <!-- each(p1, p2, p3) p1: template name, p2: collection to traverse, p3: name of varible being passed as-->
                        </ul>
                    </li>
                @endif
                <!-- End of category tree -->
            </ul>

            <!-- right side of navbar -->
            <ul class="navbar-nav navbar-right">
                <!-- start of register and login links -->
                @guest
                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Sign in</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Sign up</a></li>
                @else
                <li class="nav-item">
                    <a href="{{ route('cart.index') }}" class="nav-link mt-1"><i class="fa fa-shopping-cart"></i></a>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/60/h/6" class="img-responsive img-circle" width="30px" height="30px">
                        {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a href="{{ route('products.favorites') }}" class="dropdown-item">My saved items</a>
                        <a href="{{ route('orders.index') }}" class="dropdown-item">My orderss</a>
                        <a href="{{ route('installments.index') }}" class="dropdown-item">My installments</a>
                        <a href="{{ route('user_addresses.index') }}" class="dropdown-item">Manage addresses</a>
                        <a href="#" class="dropdown-item" id="logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Sign out</a>
                        <form action="{{ route('logout') }}" id="logout-form" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>
                @endguest
                <!-- end of register and login links -->
            </ul>
        </div>
    </div>
</nav>