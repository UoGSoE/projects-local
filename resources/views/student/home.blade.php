@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Available Projects
</h3>
<p class="subtitle">
    Some blurb about choosing things
</p>
@if (Auth::user()->isntOnACourse())
    You do not seem to be registered on any project courses.  Please email the Engineering Teaching Office.
@else
    @foreach ($projects as $project)
        <div class="box">
            <h4 class="title is-4">
                <button class="button" :class="{ 'is-info': isChosen({{ $project->id }}) }" @click.prevent="expandProject({{ $project->id }})" title="Show full description">
                    <span v-if="isExpanded({{ $project->id }})" class="icon">
                        -
                    </span>
                    <span v-else class="icon">
                        +
                    </span>
                </button>
                {{ $project->title }}
            </h4>
            <p class="subtitle has-text-grey-light">
                Run by {{ $project->owner->full_name }}
            </p>
            <div v-if="isExpanded({{ $project->id }})">
                <h5 class="title is-5 has-text-grey">Description</h5>
                <p>
                    {!! nl2br(e($project->description)) !!}
                </p>
                @if ($project->pre_req)
                    <br />
                    <h5 class="title is-5 has-text-grey">Prerequisite Skills</h5>
                    <p>
                        {!! nl2br(e($project->pre_req)) !!}
                    </p>
                @endif
                <hr />
                <div class="level">
                    <div class="level-left has-text-weight-semibold has-text-grey">
                        <div class="level-item">
                            Make this project my
                        </div>
                        <div class="level-item">
                            <div class="buttons has-addons">
                                <span class="button" :class="{ 'is-info': (choices.first == {{ $project->id }}) }" @click="choose('first', {{ $project->id }})">
                                    1st
                                </span>
                                <span class="button" :class="{ 'is-info': (choices.second == {{ $project->id }}) }" @click="choose('second', {{ $project->id }})">2nd</span>
                                <span class="button" :class="{ 'is-info': (choices.third == {{ $project->id }}) }" @click="choose('third', {{ $project->id }})">3rd</span>
                            </div>
                        </div>
                        <div class="level-item">
                            preference
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection
