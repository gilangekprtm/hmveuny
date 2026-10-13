<div class="hidden" data-toast-messages>
    @if(session('success'))
    <div data-toast-type="success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div data-toast-type="error">{{ session('error') }}</div>
    @endif

    @if(session('status'))
    <div data-toast-type="info">{{ session('status') }}</div>
    @endif

    @foreach($errors->all() as $message)
    <div data-toast-type="error">{{ $message }}</div>
    @endforeach
</div>
