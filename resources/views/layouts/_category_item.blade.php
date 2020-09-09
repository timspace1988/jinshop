<!-- if the category passed here has children entry, children entry is not null -->
@if(isset($category['children']) && count($category['children']) > 0)
    <!-- display the category and put its children to its dropdown menu -->
    <li class="dropdown-submenu">
        <a href="{{ route('products.index', ['category_id' => $category['id']]) }}" class="dropdown-item dropdown-toggle">{{ $category['name'] }}</a>
        <ul class="dropdown-menu">
            <!-- traverse on each child and recursively call and pass to this template itself -->
            @each('layouts._category_item', $category['children'], 'category')
        </ul>
    </li>
@else
    <!-- if the category passed here doesn't have children entry, display this category and not setup the dropdown menu -->
    <li><a href="{{ route('products.index', ['category_id' => $category['id']]) }}" class="dropdown-item">{{ $category['name'] }}</a></li> 
@endif
