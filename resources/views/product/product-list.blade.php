@extends('main-layout')

@section('content')
    <table>
        <thead>
            <th>id</th>
            <th>name</th>
            <th>price</th>
            <th>created_at</th>
        </thead>
        <tbody>
            @if(count($products) > 0)
                @php
                    $i = ($products->currentPage() - 1) * $products->perPage() + 1;
                @endphp
                @foreach($products as $product)
                    <tr>
                        <td>{{$i++}}</td>
                        <td>{{$product->name}}</td>
                        <td>{{$product->reg_price}}</td>
                        <td>{{$product->created_at}}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">No Product</td>
                </tr>
            @endif
        </tbody>
    </table>
    {{$products->links()}}
@endsection