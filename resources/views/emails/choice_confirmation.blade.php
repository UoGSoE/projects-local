@component('mail::message')
# Project Choice Confirmation

Hi,

This is a confirmation email for the project choices you have made :

@component('mail::table')
| Project | Choice |
| ------- | ------ |
@foreach ($student->projects()->withPivot('choice')->orderBy('choice')->get() as $project)
| {{ $project->title }} | {{ $project->pivot->choice }} |
@endforeach
@endcomponent

Thanks,<br>
School of Engineering, Teaching Office
@endcomponent
