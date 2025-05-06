@component('mail::message')

    {{ $emailBody  }}

@component('mail::button', ['url' => $businessLink])
Check out {{ $businessName }} at Rpx!
@endcomponent

Thanks, {{ $firstName }}!
@endcomponent
