@component('mail::message')

Dear {{ $notifiable->forenames }},

The student import for {{ $course->code }} {{ $course->title }} has completed.

Kind regards,

Glasgow Projects Database
@endcomponent
