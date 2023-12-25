<div class="flex items-center justify-center">
    <!-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci -->

    <img src="{{$getRecord()?$getRecord()->thumbnail:$getState()??asset('images/thumbnail_placeholder.jpg')}}" alt="product thumbnail" class='rounded-lg'
        width="{{$width}}"
    >

</div>
