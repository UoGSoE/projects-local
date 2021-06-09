@component('mail::message')
# Import of the dmoran spreadsheet is complete

@if (count($errors))
## The following rows had problems
@foreach ($errors as $error)
* {{ $error }}
@endforeach
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
