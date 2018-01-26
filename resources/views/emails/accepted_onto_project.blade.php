@component('mail::message')
# School of Engineering Project

Hi,

You've been accepted onto the project *{{ $project->title }}* run by {{ $project->owner->full_name }}.  Please
email them at {{ $project->owner->email }} to proceed.

Thanks,<br>

School of Engineering, Teaching Office

@endcomponent
