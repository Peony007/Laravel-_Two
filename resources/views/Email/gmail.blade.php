@component('mail::message')
# Introduction

Thanks for registering my homepage.

@component('mail::button', ['url' =>'http://localhost:8000/api/verify/?token='.$token])
Verify
@endcomponent
Thanks,<br>
{{ config('app.name') }}
@component('mail::panel')
This is the panel content.
@endcomponent
@endcomponent