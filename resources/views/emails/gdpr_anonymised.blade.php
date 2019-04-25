@component('mail::message')
# Glasgow Projects GDPR

The following users have been anonymised in the system as they no longer show up in the campus staff directory.

@component('mail::table')
| Original Username | Anonymised |
| ----------------- | ---------- |
@foreach ($users as $user)
| $user['originalName'] | $user['anonName'] |
@endforeach
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent