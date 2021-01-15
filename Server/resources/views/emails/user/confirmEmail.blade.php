@component('mail::message')
# Hey {{ $name }},

Welcome to Munched, the calorie tracking app for nerds. Before you start crushing your goals you need to confirm your email address.

@component('mail::button', ['url' => $url])
Confirm Email
@endcomponent

Thanks,<br>
The Munched Team
@endcomponent
