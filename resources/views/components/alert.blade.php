@if (session()->exists('error'))
    <div class="alert alert-danger mb-0">
        {{session('error')}}
    </div>
@endif

@if (session()->exists('success'))
<div class="alert alert-success mb-0">
    {{session('success')}}
</div>
@endif


